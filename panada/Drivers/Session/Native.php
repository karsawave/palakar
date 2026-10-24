<?php

declare(strict_types=1);

/**
 * Panada PHP native session handler.
 *
 * Session data stays on the server (PHP's own session files); the browser
 * only keeps the session id, in a cookie that is:
 *   - HttpOnly : JavaScript cannot read it (an XSS bug cannot steal it).
 *   - Secure   : only sent over HTTPS ('auto' = when the request is HTTPS).
 *   - SameSite : not sent with cross-site POSTs (Lax), extra CSRF defence.
 * Strict mode makes PHP refuse session ids it did not create (fixation).
 *
 * @author	Iskandar Soesman
 *
 * @since	Version 0.1
 */

namespace Drivers\Session;

use Resources\Interfaces;
use Resources\Request;
use Resources\RunException;

class Native implements Interfaces\Session
{
    public const DEFAULTS = [
        'expiration' => 7200,
        'name' => 'PAN_SID',
        'cookieExpire' => 0,
        'cookiePath' => '/',
        'cookieDomain' => '',
        'cookieSecure' => 'auto',
        'cookieHttpOnly' => true,
        'cookieSameSite' => 'Lax',
    ];

    public string $sessionName = 'PAN_SID';
    public string $sessionId = '';

    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config + self::DEFAULTS;
        $this->sessionName = $this->config['name'];

        if (session_status() === PHP_SESSION_ACTIVE) {
            $this->sessionId = (string) session_id();

            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.gc_maxlifetime', (string) $this->config['expiration']);

        session_name($this->sessionName);
        session_set_cookie_params(self::cookieOptions($this->config));
        session_start();

        $this->sessionId = (string) session_id();
    }

    /**
     * Cookie attributes built from the session config.
     *
     * @return array{lifetime: int, path: string, domain: string, secure: bool, httponly: bool, samesite: string}
     */
    public static function cookieOptions(array $config): array
    {
        $config += self::DEFAULTS;
        $sameSite = ucfirst(strtolower((string) $config['cookieSameSite']));

        if (!in_array($sameSite, ['Lax', 'Strict', 'None'], true)) {
            throw new RunException('cookieSameSite must be Lax, Strict or None');
        }

        $secure = $config['cookieSecure'] === 'auto' ? Request::isSecure() : (bool) $config['cookieSecure'];

        return [
            'lifetime' => (int) $config['cookieExpire'],
            'path' => (string) $config['cookiePath'],
            'domain' => (string) $config['cookieDomain'],
            // Browsers drop SameSite=None cookies that are not Secure.
            'secure' => $secure || $sameSite === 'None',
            'httponly' => (bool) $config['cookieHttpOnly'],
            'samesite' => $sameSite,
        ];
    }

    /**
     * New session id, same data. Call it right after a successful login.
     */
    public function regenerateId(): void
    {
        session_regenerate_id(true);
        $this->sessionId = (string) session_id();
    }

    /**
     * Save new session value(s).
     */
    public function setValue(string|array $name, mixed $value = ''): void
    {
        foreach (is_array($name) ? $name : [$name => $value] as $key => $val) {
            $_SESSION[$key] = $val;
        }
    }

    /**
     * Get the session value, or false when it does not exist.
     */
    public function getValue(string $name): mixed
    {
        return $_SESSION[$name] ?? false;
    }

    /**
     * Get all session value.
     */
    public function getAllValue(): array
    {
        return $_SESSION ?? [];
    }

    /**
     * Remove/unset the session value.
     */
    public function deleteValue(string $name): void
    {
        unset($_SESSION[$name]);
    }

    /**
     * Remove the session on the server and the cookie in the browser (logout).
     */
    public function destroy(bool $setExpireHeader = false): void
    {
        if ($setExpireHeader && !headers_sent()) {
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Pragma: no-cache');
        }

        $_SESSION = [];

        if (!headers_sent()) {
            $options = self::cookieOptions($this->config);
            unset($options['lifetime']);
            setcookie($this->sessionName, '', ['expires' => time() - 42000] + $options);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
