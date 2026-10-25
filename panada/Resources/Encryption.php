<?php

declare(strict_types=1);

namespace Resources;

/**
 * Panada encryption class: authenticated encryption with libsodium
 * (XSalsa20-Poly1305). A changed or forged value fails to decrypt.
 *
 *   $crypt = new \Resources\Encryption();     // key from security.php
 *   $token = $crypt->encrypt('secret data');
 *   $plain = $crypt->decrypt($token);         // false when tampered with
 *
 * Note: values encrypted by Panada 1 cannot be decrypted (that was only
 * an obfuscation, not real encryption).
 *
 * @link	http://panadaframework.com/
 *
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.1
 */
final class Encryption
{
    private string $key;

    public function __construct(?string $key = null)
    {
        if (!extension_loaded('sodium')) {
            throw new RunException('The sodium PHP extension is required for encryption.');
        }

        $this->key = sodium_crypto_generichash(self::secret($key), '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    }

    /**
     * Validate a secret key, or read it from app/config/security.php.
     */
    public static function secret(?string $key = null): string
    {
        $key = (string) ($key ?: (Config::security()['secretKey'] ?? ''));

        if (strlen($key) < 32 || $key === '_put_your_random_string_here_') {
            throw new RunException('Set a random secretKey of at least 32 characters in app/config/security.php');
        }

        return $key;
    }

    public function encrypt(string $string): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        return sodium_bin2base64($nonce . sodium_crypto_secretbox($string, $nonce, $this->key), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
    }

    public function decrypt(string $string): string|false
    {
        try {
            $raw = sodium_base642bin($string, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        } catch (\SodiumException) {
            return false;
        }

        if (strlen($raw) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            return false;
        }

        return sodium_crypto_secretbox_open(
            substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES),
            $this->key,
        );
    }
}
