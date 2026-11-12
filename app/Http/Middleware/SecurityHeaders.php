<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies security-relevant HTTP response headers to every web response.
 *
 * ── CSP enforcement ─────────────────────────────────────────────────────────
 * To switch to report-only mode, set GATEWAY_CSP_ENFORCE=false.
 *
 * ── Allowed sources ─────────────────────────────────────────────────────────
 * script-src  : 'self' (Vite bundle) + per-request nonce (pay-redirect only)
 * style-src   : 'self' + Google Fonts (stylesheet)
 * font-src    : 'self' + Google Fonts (woff2 files)
 * img-src     : 'self' + data: (inline SVG favicons, QR code data URIs)
 * connect-src : 'self'
 * form-action : 'self' + Paytm PG hosts (both sandbox and production)
 *               + PhonePe checkout host (api-preprod / api.phonepe.com)
 * frame-ancestors: 'none' (belt-and-suspenders with X-Frame-Options: DENY)
 *
 * ── What AuthenticateSession does alongside logoutOtherDevices ───────────────
 * Auth::logoutOtherDevices() actively deletes / invalidates other sessions at
 * the moment of the password change. AuthenticateSession provides a passive
 * safety net: on every subsequent authenticated request it compares the session's
 * stored password hash against the DB; if they differ the session is rejected.
 * The two mechanisms are complementary — logoutOtherDevices for eager cleanup,
 * AuthenticateSession for defence-in-depth on any session that slips through.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Per-request nonce for the Paytm PG form auto-submit script.
        $nonce = base64_encode(random_bytes(16));
        view()->share('csp_nonce', $nonce);

        $response = $next($request);

        // ── HSTS ─────────────────────────────────────────────────────────────
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // ── Content-Security-Policy ───────────────────────────────────────────
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'",
            // Google Fonts stylesheet. No inline styles remain after Vite migration.
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            // data: covers the SVG favicon and any inline SVG data URIs in views.
            "img-src 'self' data:",
            "connect-src 'self'",
            // form-action: self + all PG checkout hostnames from config.
            "form-action 'self' ".implode(' ', $this->pgFormActionHosts()),
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);

        $headerName = config('gateway.csp_enforce', true)
            ? 'Content-Security-Policy'
            : 'Content-Security-Policy-Report-Only';

        $response->headers->set($headerName, $csp);

        return $response;
    }

    /**
     * Derive the set of hostnames that can be the target of a form POST.
     * These come from gateway config so a single config change covers both CSP
     * and the rest of the application.
     *
     * @return string[]
     */
    private function pgFormActionHosts(): array
    {
        $hosts = [];

        // Paytm PG (sandbox + production base URLs)
        foreach (['sandbox', 'production'] as $env) {
            $url = config("gateway.paytm_pg.{$env}");
            if ($url && ($host = $this->originFromUrl($url))) {
                $hosts[] = $host;
            }
        }

        // PhonePe PG (base_url for sandbox + production)
        foreach (['sandbox', 'production'] as $env) {
            $url = config("gateway.phonepe_pg.{$env}.base_url");
            if ($url && ($host = $this->originFromUrl($url))) {
                $hosts[] = $host;
            }
        }

        return array_unique($hosts);
    }

    /** Extract scheme://host from a URL, returning null on failure. */
    private function originFromUrl(string $url): ?string
    {
        $parts = parse_url(rtrim($url, '/'));
        if (! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }
}
