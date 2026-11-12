<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

/**
 * SSRF-safe webhook URL validator.
 *
 * Validates that a URL is safe to send outbound HTTP requests to by:
 *   - Enforcing https (or http only if gateway.webhook_allow_insecure is true)
 *   - Rejecting userinfo (user:pass@host), non-standard schemes, empty hosts
 *   - Enforcing port allowlist (443, or 80 when insecure allowed)
 *   - Rejecting raw IP literals in decimal/hex/octal notation
 *   - Resolving A/AAAA records and rejecting any private/reserved/loopback/
 *     link-local/CGNAT/multicast IP in the answer set
 *
 * Usage:
 *   $validated = SafeUrl::validate($url);  // throws ValidationException on failure
 *   // $validated = ['host' => '...', 'port' => 443, 'ips' => ['1.2.3.4']]
 *
 * Injectable resolver for testing:
 *   app()->bind(\App\Support\SafeUrl::class, fn () => new SafeUrl(
 *       resolver: fn (string $host) => ['93.184.216.34']
 *   ));
 */
class SafeUrl
{
    /**
     * @param  callable(string):string[]|null  $resolver  Inject a custom DNS resolver (for tests).
     *                                                    Receives hostname, returns array of IP strings.
     */
    public function __construct(private readonly mixed $resolver = null) {}

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Validate a webhook URL. Throws a validation-style exception on failure.
     *
     * @return array{host: string, port: int, ips: string[]}
     *
     * @throws ValidationException
     */
    public static function validate(string $url): array
    {
        return app(self::class)->check($url);
    }

    /**
     * Instance method so the injected resolver is used.
     *
     * @return array{host: string, port: int, ips: string[]}
     *
     * @throws ValidationException
     */
    public function check(string $url): array
    {
        $this->assertValidStructure($url);

        $parts = parse_url($url);
        if ($parts === false) {
            $this->fail('webhook_url', 'The webhook URL is malformed.');
        }

        $scheme = strtolower($parts['scheme'] ?? '');
        $host = $parts['host'] ?? '';
        $port = $parts['port'] ?? null;
        $user = $parts['user'] ?? null;
        $pass = $parts['pass'] ?? null;

        // Reject userinfo credentials.
        if ($user !== null || $pass !== null) {
            $this->fail('webhook_url', 'The webhook URL must not contain credentials (user:pass@).');
        }

        // Enforce allowed schemes.
        $allowInsecure = (bool) config('gateway.webhook_allow_insecure', false);
        if ($scheme === 'http' && ! $allowInsecure) {
            $this->fail('webhook_url', 'The webhook URL must use HTTPS.');
        }
        if (! in_array($scheme, ['https', 'http'], true)) {
            $this->fail('webhook_url', 'The webhook URL scheme must be https (or http if explicitly allowed).');
        }

        // Enforce host is present.
        if ($host === '') {
            $this->fail('webhook_url', 'The webhook URL must have a valid host.');
        }

        // Resolve effective port.
        if ($port === null) {
            $effectivePort = $scheme === 'https' ? 443 : 80;
        } else {
            $effectivePort = (int) $port;
        }

        // Enforce port allowlist.
        $allowedPorts = (array) config('gateway.webhook_allowed_ports', [443]);
        if ($allowInsecure && ! in_array(80, $allowedPorts, true)) {
            $allowedPorts[] = 80;
        }
        if (! in_array($effectivePort, $allowedPorts, true)) {
            $this->fail('webhook_url', "The webhook URL port {$effectivePort} is not allowed.");
        }

        // Reject raw IP literals in any encoding (decimal, hex, octal tricks).
        // parse_url already strips brackets from IPv6, but we check the raw host too.
        $this->assertNotRawIp($host);

        // Resolve hostname → IPs and block any private/reserved address.
        $ips = $this->resolveHost($host);
        if (empty($ips)) {
            $this->fail('webhook_url', "The webhook URL hostname '{$host}' could not be resolved.");
        }
        foreach ($ips as $ip) {
            $this->assertPublicIp($ip, $host);
        }

        return ['host' => $host, 'port' => $effectivePort, 'ips' => $ips];
    }

    // ── Structure / scheme checks ─────────────────────────────────────────────

    /**
     * Pre-flight: reject completely unparseable or empty strings quickly,
     * and catch some tricks parse_url accepts but shouldn't (e.g. //host).
     */
    private function assertValidStructure(string $url): void
    {
        if (trim($url) === '') {
            $this->fail('webhook_url', 'The webhook URL must not be empty.');
        }
        // Must have a real scheme, not just a protocol-relative URL.
        if (! preg_match('#^[a-zA-Z][a-zA-Z0-9+\-.]*://#', $url)) {
            $this->fail('webhook_url', 'The webhook URL must start with a scheme (https://).');
        }
    }

    // ── Raw-IP detection ──────────────────────────────────────────────────────

    /**
     * Reject hosts that are IP literals in any encoding (decimal, hex, octal, dotted).
     * This covers:
     *   - 127.0.0.1            (normal dotted)
     *   - 2130706433           (decimal integer)
     *   - 0x7f000001           (hex)
     *   - 0177.0.0.1           (octal-dotted)
     *   - [::1]                (IPv6 literal – parse_url strips [] and returns ::1)
     *   - ::ffff:127.0.0.1     (IPv4-mapped)
     */
    private function assertNotRawIp(string $host): void
    {
        // IPv6: parse_url strips the brackets, so host is the bare address.
        if (str_contains($host, ':')) {
            $this->fail('webhook_url', 'The webhook URL must use a hostname, not a raw IP address.');
        }

        // Standard dotted-decimal / dotted-hex / dotted-octal IPv4.
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->fail('webhook_url', 'The webhook URL must use a hostname, not a raw IP address.');
        }

        // Integer forms: decimal (2130706433), hex (0x7f000001), octal (0177).
        // A pure decimal / hex / octal string with no dots that converts to an IP.
        if (preg_match('/^(0x[0-9a-fA-F]+|0[0-7]+|[0-9]+)$/', $host)) {
            $this->fail('webhook_url', 'The webhook URL must use a hostname, not a raw IP address (integer form detected).');
        }

        // Dotted-hex or dotted-octal components: 0x7f.0.0.1 or 0177.0.0.1
        $parts = explode('.', $host);
        if (count($parts) === 4) {
            $allNumeric = true;
            foreach ($parts as $part) {
                if (! preg_match('/^(0x[0-9a-fA-F]+|0[0-7]*|[0-9]+)$/', $part)) {
                    $allNumeric = false;
                    break;
                }
            }
            if ($allNumeric) {
                $this->fail('webhook_url', 'The webhook URL must use a hostname, not a raw IP address (encoded form detected).');
            }
        }
    }

    // ── DNS resolution ────────────────────────────────────────────────────────

    /**
     * Resolve A and AAAA records for $host. Returns an array of IP strings.
     *
     * @return string[]
     */
    private function resolveHost(string $host): array
    {
        if ($this->resolver !== null) {
            return ($this->resolver)($host);
        }

        $ips = [];

        // dns_get_record returns false on hard failure; suppress warnings.
        $a = @dns_get_record($host, DNS_A);
        if (is_array($a)) {
            foreach ($a as $record) {
                if (isset($record['ip'])) {
                    $ips[] = $record['ip'];
                }
            }
        }

        $aaaa = @dns_get_record($host, DNS_AAAA);
        if (is_array($aaaa)) {
            foreach ($aaaa as $record) {
                if (isset($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }

        return array_values(array_unique($ips));
    }

    // ── IP range checks ───────────────────────────────────────────────────────

    /**
     * Assert that $ip is a globally routable public address.
     *
     * filter_var with FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
     * blocks most private/reserved ranges, but misses:
     *   - CGNAT 100.64.0.0/10
     *   - IPv4-mapped ::ffff:x.x.x.x  (PHP filter_var treats these as valid IPv6)
     *   - 0.0.0.0/8 (unspecified / "this network")
     *   - 169.254.0.0/16 (link-local) — actually IS caught by FILTER_FLAG_NO_RES_RANGE
     *     in most PHP builds, but we double-check
     *
     * We explicitly check all of these after the filter_var pass.
     */
    private function assertPublicIp(string $ip, string $host): void
    {
        // Normalise IPv4-mapped IPv6 (::ffff:1.2.3.4 → 1.2.3.4) before checking.
        $normalized = $this->normalizeIpv4Mapped($ip);

        // Use filter_var for the bulk of the standard private/reserved ranges.
        $isPublic = filter_var(
            $normalized,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );

        if ($isPublic === false) {
            $this->fail('webhook_url', "The webhook URL hostname '{$host}' resolves to a private or reserved IP address.");
        }

        // Additional checks for ranges filter_var does not cover.
        if ($this->isCgnat($normalized)) {
            $this->fail('webhook_url', "The webhook URL hostname '{$host}' resolves to a CGNAT address (100.64.0.0/10).");
        }

        if ($this->isUnspecifiedV4($normalized)) {
            $this->fail('webhook_url', "The webhook URL hostname '{$host}' resolves to an unspecified address (0.0.0.0/8).");
        }

        // Extra belt-and-suspenders for link-local (169.254.0.0/16) in case
        // a PHP build doesn't block it via FILTER_FLAG_NO_RES_RANGE.
        if ($this->isLinkLocalV4($normalized)) {
            $this->fail('webhook_url', "The webhook URL hostname '{$host}' resolves to a link-local address.");
        }

        // IPv6-specific additional checks.
        if (filter_var($normalized, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            if ($this->isPrivateIpv6($normalized)) {
                $this->fail('webhook_url', "The webhook URL hostname '{$host}' resolves to a private IPv6 address.");
            }
        }
    }

    /**
     * Normalise ::ffff:1.2.3.4 and ::ffff:0102:0304 to dotted-decimal IPv4
     * so subsequent filter_var calls treat it as IPv4.
     */
    private function normalizeIpv4Mapped(string $ip): string
    {
        // PHP's inet_pton / inet_ntop handles this cleanly.
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $ip;
        }

        $packed = @inet_pton($ip);
        if ($packed === false || strlen($packed) !== 16) {
            return $ip;
        }

        // Check the IPv4-mapped prefix: ::ffff:0:0/96
        $prefix = substr($packed, 0, 12);
        $ipv4Mapped = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff";
        if ($prefix === $ipv4Mapped) {
            return inet_ntop(substr($packed, 12)); // returns dotted-decimal IPv4
        }

        return $ip;
    }

    /** CGNAT: 100.64.0.0/10 */
    private function isCgnat(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }
        $long = ip2long($ip);

        // 100.64.0.0 = 1681915904, 100.127.255.255 = 1686110207
        return $long >= 1681915904 && $long <= 1686110207;
    }

    /** Unspecified / "this network": 0.0.0.0/8 */
    private function isUnspecifiedV4(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        return (ip2long($ip) >> 24 & 0xFF) === 0;
    }

    /** Link-local: 169.254.0.0/16 */
    private function isLinkLocalV4(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }
        $long = ip2long($ip);

        // 169.254.0.0 = 2851995648, 169.254.255.255 = 2852061183
        return $long >= 2851995648 && $long <= 2852061183;
    }

    /**
     * IPv6-specific private/reserved ranges not caught by filter_var:
     *   ::1          loopback
     *   fc00::/7     unique local (fc00:: – fdff::)
     *   fe80::/10    link-local
     */
    private function isPrivateIpv6(string $ip): bool
    {
        $packed = @inet_pton($ip);
        if ($packed === false || strlen($packed) !== 16) {
            return false;
        }

        // ::1 loopback
        if ($packed === "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x01") {
            return true;
        }

        $firstByte = ord($packed[0]);
        $secondByte = ord($packed[1]);

        // fc00::/7 → first byte has top 7 bits = 1111110x → 0xfc or 0xfd
        if (($firstByte & 0xFE) === 0xFC) {
            return true;
        }

        // fe80::/10 → first 10 bits = 1111111010 → 0xfe, second byte & 0xc0 = 0x80
        if ($firstByte === 0xFE && ($secondByte & 0xC0) === 0x80) {
            return true;
        }

        // ff00::/8 multicast
        if ($firstByte === 0xFF) {
            return true;
        }

        // :: unspecified
        if ($packed === str_repeat("\x00", 16)) {
            return true;
        }

        return false;
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    /**
     * @return never
     *
     * @throws ValidationException
     */
    private function fail(string $field, string $message): void
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
