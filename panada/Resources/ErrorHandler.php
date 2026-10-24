<?php

declare(strict_types=1);

namespace Resources;

/**
 * Catches every PHP error and uncaught exception, then shows one error page.
 *
 *  - 'debug' => true  : detailed page. When filp/whoops is installed
 *                       (`composer require --dev filp/whoops`) its interactive
 *                       page is used, otherwise Panada's own page with source
 *                       code and stack trace.
 *  - 'debug' => false : generic page; details only go to the PHP error log.
 *                       Whoops is never used here, even when installed.
 */
final class ErrorHandler
{
    private static bool $debug = false;

    /** @var \Whoops\Run|null Used as a page renderer only, never registered globally. */
    private static ?object $whoops = null;

    public static function register(bool $debug): void
    {
        self::$debug = $debug;
        self::$whoops = $debug && class_exists(\Whoops\Run::class) ? self::makeWhoops() : null;

        if (PHP_SAPI !== 'cli') {
            // We render errors ourselves, PHP must not print them a second time.
            ini_set('display_errors', '0');
        }

        set_error_handler([self::class, 'handleError']);
        set_exception_handler(static function (\Throwable $e): void {
            echo self::render($e)->send();
        });
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    public static function isDebug(): bool
    {
        return self::$debug;
    }

    /**
     * Turn PHP warnings/notices into exceptions so they are never ignored.
     *
     * Deprecation notices are only logged: a library that still uses an old
     * PHP feature should not take the whole site down after a PHP upgrade.
     */
    public static function handleError(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        if ($severity & (E_DEPRECATED | E_USER_DEPRECATED)) {
            error_log('Deprecated: '.$message.' in '.$file.':'.$line);

            return true;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Fatal errors (e.g. out of memory) skip the error handler; catch them here.
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            return;
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        echo self::render(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']))->send();
    }

    /**
     * Build the 500 response for an exception.
     */
    public static function render(\Throwable $e): Response
    {
        error_log(sprintf('%s: %s in %s:%d', $e::class, $e->getMessage(), $e->getFile(), $e->getLine()));

        $response = new Response();
        $response->setStatusCode(500);

        if (PHP_SAPI === 'cli') {
            Response::$headers['Content-Type'] = 'text/plain; charset=utf-8';
            $response->setBody($e::class.': '.$e->getMessage()."\n".$e->getFile().':'.$e->getLine()."\n\n".$e->getTraceAsString()."\n");

            return $response;
        }

        Response::$headers['Content-Type'] = 'text/html; charset=utf-8';

        try {
            if (self::$whoops !== null) {
                if (self::wantsJson()) {
                    Response::$headers['Content-Type'] = 'application/json; charset=utf-8';
                }

                $response->setBody((string) self::$whoops->handleException($e));

                return $response;
            }

            $response->setBody(Controller::outputError('errors/500', self::viewData($e)));
        } catch (\Throwable) {
            // The error view itself is broken: fall back to plain text.
            $response->setBody('<h1>500 Internal Server Error</h1>'.(self::$debug ? '<pre>'.e((string) $e).'</pre>' : ''));
        }

        return $response;
    }

    /**
     * Whoops configured to return the page as a string, so Panada's Response
     * still sends it (no exit(), no direct echo).
     */
    private static function makeWhoops(): \Whoops\Run
    {
        $whoops = new \Whoops\Run();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);

        if (self::wantsJson()) {
            $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());

            return $whoops;
        }

        $page = new \Whoops\Handler\PrettyPageHandler();
        $page->setPageTitle('Panada error');
        $page->addDataTable('Panada', [
            'PHP version' => PHP_VERSION,
            'Application path' => defined('APP') ? APP : '',
        ]);
        $whoops->pushHandler($page);

        return $whoops;
    }

    private static function wantsJson(): bool
    {
        return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    private static function viewData(\Throwable $e): array
    {
        if (!self::$debug) {
            return ['debug' => false, 'message' => 'Something went wrong. The error has been logged.'];
        }

        return [
            'debug' => true,
            'class' => $e::class,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => self::sourceLines($e->getFile(), $e->getLine()),
            'trace' => $e->getTraceAsString(),
        ];
    }

    /**
     * Five lines above and below the line that failed.
     *
     * @return array<int, array{number: int, text: string, current: bool}>
     */
    private static function sourceLines(string $file, int $line): array
    {
        if (!is_file($file) || !is_readable($file)) {
            return [];
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES) ?: [];
        $start = max(1, $line - 5);
        $end = min(count($lines), $line + 5);
        $result = [];

        for ($i = $start; $i <= $end; $i++) {
            $result[] = ['number' => $i, 'text' => $lines[$i - 1], 'current' => $i === $line];
        }

        return $result;
    }
}
