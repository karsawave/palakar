<?php

declare(strict_types=1);

namespace Resources;

/**
 * Holds the status code, headers and body until Gear sends them.
 */
class Response
{
    public static int $statusCode = 200;

    public static array $headers = [
        'Content-Type' => 'text/html; charset=utf-8',
        'X-Content-Type-Options' => 'nosniff',
        // Against clickjacking and leaking full URLs to other sites. Apps that
        // must be framed elsewhere unset() this entry.
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ];

    public static string $body = '';

    public function send(): string
    {
        self::sendHeader();

        return self::$body;
    }

    public function sendBody(): string
    {
        return self::$body;
    }

    public static function sendHeader(): void
    {
        if (headers_sent()) {
            return;
        }

        // After a fatal error PHP already set "HTTP/1.0 500"; setting it again
        // is a warning since PHP 8.5, which broke the error page.
        if (http_response_code() !== self::$statusCode) {
            http_response_code(self::$statusCode);
        }

        foreach (self::$headers as $name => $value) {
            header($name . ': ' . $value, true);
        }
    }

    public function sendHeaders(): void
    {
        self::sendHeader();
    }

    public function setBody(string $body): void
    {
        self::$body = $body;
    }

    /**
     * Turn whatever a controller produced into the response body.
     * Arrays and objects without __toString are sent as JSON.
     */
    public static function setContent(string $echoed, mixed $returned): void
    {
        if (is_array($returned) || (is_object($returned) && !$returned instanceof \Stringable)) {
            self::$headers['Content-Type'] = 'application/json; charset=utf-8';
            $returned = json_encode($returned, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }

        self::$body = $echoed . (is_scalar($returned) || $returned instanceof \Stringable ? (string) $returned : '');
    }

    public static function setHeader(string $key, string $value, int $statusCode = 200): void
    {
        self::$statusCode = $statusCode;
        self::$headers[$key] = $value;
    }

    public function setStatusCode(int $statusCode): void
    {
        self::$statusCode = $statusCode;
    }
}
