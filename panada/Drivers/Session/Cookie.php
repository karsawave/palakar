<?php

declare(strict_types=1);

/**
 * Cookie based session: all values live in one cookie in the browser.
 *
 * The cookie is signed with HMAC-SHA256, so a visitor can read it (unless
 * 'isEncrypt' is true) but cannot change it. Values are stored as JSON, so
 * only strings, numbers, booleans and arrays are allowed, and the whole
 * cookie must stay under 4 KB. For anything bigger use the native driver.
 *
 * @author	Iskandar Soesman
 *
 * @since	Version 0.3
 */

namespace Drivers\Session;

use Resources\Encryption;
use Resources\Interfaces;
use Resources\RunException;

class Cookie implements Interfaces\Session
{
    public string $sessionName = 'PAN_SID';

    protected array $config;

    private array $values = [];
    private string $signingKey;
    private ?Encryption $encryption = null;

    public function __construct(array $config)
    {
        $this->config = $config + Native::DEFAULTS;
        $this->sessionName = $this->config['name'];

        $secret = Encryption::secret($this->config['secretKey'] ?? null);
        $this->signingKey = hash('sha256', 'panada-cookie-session|'.$secret, true);

        if (!empty($this->config['isEncrypt'])) {
            $this->encryption = new Encryption($secret);
        }

        $this->values = $this->decode($_COOKIE[$this->sessionName] ?? null);

        // Sliding expiration: every visit pushes the expiry forward.
        if ($this->values !== []) {
            $this->save();
        }
    }

    public function setValue(string|array $name, mixed $value = ''): void
    {
        foreach (is_array($name) ? $name : [$name => $value] as $key => $val) {
            $this->values[$key] = $val;
        }

        $this->save();
    }

    public function getValue(string $name): mixed
    {
        return $this->values[$name] ?? false;
    }

    public function getAllValue(): array
    {
        return $this->values;
    }

    public function deleteValue(string $name): void
    {
        unset($this->values[$name]);
        $this->save();
    }

    /**
     * There is no server side id; the cookie is simply written again.
     */
    public function regenerateId(): void
    {
        $this->save();
    }

    public function destroy(bool $setExpireHeader = false): void
    {
        $this->values = [];
        unset($_COOKIE[$this->sessionName]);
        $this->sendCookie('', time() - 42000);
    }

    /**
     * Turn the cookie back into values. Anything forged, expired or broken
     * gives an empty session.
     */
    private function decode(mixed $cookie): array
    {
        if (!is_string($cookie) || $cookie === '') {
            return [];
        }

        if ($this->encryption !== null && ($cookie = $this->encryption->decrypt($cookie)) === false) {
            return [];
        }

        [$payload, $signature] = explode('.', $cookie, 2) + [1 => ''];

        if (!hash_equals(hash_hmac('sha256', $payload, $this->signingKey), $signature)) {
            return [];
        }

        $data = json_decode((string) base64_decode(strtr($payload, '-_', '+/'), true), true);

        if (!is_array($data) || ($data['exp'] ?? 0) < time() || !is_array($data['values'] ?? null)) {
            return [];
        }

        return $data['values'];
    }

    private function save(): void
    {
        $payload = rtrim(strtr(base64_encode(json_encode(
            ['values' => $this->values, 'exp' => time() + (int) $this->config['expiration']],
            JSON_THROW_ON_ERROR
        )), '+/', '-_'), '=');

        $cookie = $payload.'.'.hash_hmac('sha256', $payload, $this->signingKey);

        if ($this->encryption !== null) {
            $cookie = $this->encryption->encrypt($cookie);
        }

        if (strlen($cookie) > 4000) {
            throw new RunException('Cookie session is larger than 4 KB. Store less data or use the native session driver.');
        }

        $_COOKIE[$this->sessionName] = $cookie;
        $lifetime = (int) $this->config['cookieExpire'];
        $this->sendCookie($cookie, $lifetime > 0 ? time() + $lifetime : 0);
    }

    private function sendCookie(string $value, int $expires): void
    {
        if (headers_sent()) {
            return;
        }

        $options = Native::cookieOptions($this->config);
        unset($options['lifetime']);

        setcookie($this->sessionName, $value, ['expires' => $expires] + $options);
    }
}
