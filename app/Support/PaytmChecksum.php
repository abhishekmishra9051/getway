<?php

namespace App\Support;

/**
 * Port of Paytm's official checksum library (paytm/Paytm_PHP_Checksum v2.0.2):
 * signature = AES-128-CBC( sha256(payload|salt) . salt ) with a fixed IV,
 * keyed by the merchant key.
 */
class PaytmChecksum
{
    private const IV = '@@@@&&&&####$$$$';

    private const SALT_CHARS = '9876543210ZYXWVUTSRQPONMLKJIHGFEDCBAabcdefghijklmnopqrstuvwxyz!@#$&_';

    public static function generate(string $payload, string $key): string
    {
        $salt = '';
        for ($i = 0; $i < 4; $i++) {
            $salt .= self::SALT_CHARS[random_int(0, strlen(self::SALT_CHARS) - 1)];
        }

        return openssl_encrypt(self::hash($payload, $salt), 'AES-128-CBC', html_entity_decode($key), 0, self::IV);
    }

    public static function verify(string $payload, string $key, string $signature): bool
    {
        $decrypted = openssl_decrypt($signature, 'AES-128-CBC', html_entity_decode($key), 0, self::IV);

        if ($decrypted === false || strlen($decrypted) < 4) {
            return false;
        }

        return hash_equals($decrypted, self::hash($payload, substr($decrypted, -4)));
    }

    private static function hash(string $payload, string $salt): string
    {
        return hash('sha256', $payload.'|'.$salt).$salt;
    }
}
