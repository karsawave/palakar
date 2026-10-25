<?php

declare(strict_types=1);

namespace Resources;

/**
 * CSRF protection: one secret token per session that every form sends back.
 * A malicious site can make a visitor's browser submit a form to your app,
 * but it cannot read this token, so the forged request is rejected.
 *
 * In a form:        <form method="post"> <?= csrf_field() ?> ... </form>
 * In JavaScript:    headers: {'X-CSRF-Token': '<?= csrf_token() ?>'}
 *
 * Gear checks it automatically for every request that is not GET, HEAD or
 * OPTIONS. Settings are in 'csrf' in app/config/security.php.
 */
final class Csrf
{
    public const FIELD = '_token';
    public const HEADER = 'X-CSRF-Token';

    private const SESSION_KEY = '_csrf_token';

    private static ?Session $session = null;

    /**
     * The token of the current session (created on first use).
     */
    public static function token(): string
    {
        $token = self::session()->getValue(self::SESSION_KEY);

        return is_string($token) && strlen($token) === 64 ? $token : self::regenerate();
    }

    /**
     * New token. Call it after login, together with session regenerateId().
     */
    public static function regenerate(): string
    {
        $token = bin2hex(random_bytes(32));
        self::session()->setValue(self::SESSION_KEY, $token);

        return $token;
    }

    /**
     * Hidden input for forms.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="' . self::FIELD . '" value="' . self::token() . '">';
    }

    public static function isValid(?string $token): bool
    {
        $expected = self::session()->getValue(self::SESSION_KEY);

        return is_string($expected) && $expected !== '' && is_string($token) && hash_equals($expected, $token);
    }

    /**
     * The token sent with this request: the form field, or the header for AJAX.
     */
    public static function fromRequest(): ?string
    {
        $token = $_POST[self::FIELD] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        return is_string($token) ? $token : null;
    }

    /**
     * Called by Gear before your controller runs.
     *
     * @param string $path URI path without slashes at the ends, e.g. "api/orders"
     */
    public static function verifyRequest(string $path): void
    {
        $config = Config::security()['csrf'] ?? [];

        if (!($config['enabled'] ?? true) || in_array(Request::method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return;
        }

        foreach ($config['except'] ?? [] as $pattern) {
            if (fnmatch(trim($pattern, '/'), $path)) {
                return;
            }
        }

        if (!self::isValid(self::fromRequest())) {
            throw new HttpException('The form has expired or the CSRF token is missing.', 403);
        }
    }

    private static function session(): Session
    {
        return self::$session ??= new Session();
    }
}
