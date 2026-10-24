<?php

declare(strict_types=1);

/**
 * Exception for HTTP errors. The code is the HTTP status (default 404).
 *
 *   throw new \Resources\HttpException('Post not found');        // 404
 *   throw new \Resources\HttpException('Members only', 403);     // 403
 *
 * The page is rendered from app/views/errors/{code}.php, or errors/404.php
 * when that file does not exist.
 *
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @link	http://panadaframework.com/
 *
 * @license	http://www.opensource.org/licenses/bsd-license.php
 *
 * @since	version 1.0.0
 */

namespace Resources;

class HttpException extends \Exception
{
    public function __construct(string $message = '', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }

    public static function outputError(\Throwable $exception): Response
    {
        $status = $exception->getCode();
        $status = ($status >= 400 && $status < 600) ? $status : 404;

        if (PHP_SAPI === 'cli') {
            fwrite(STDERR, 'HTTP '.$status.': '.$exception->getMessage()."\n");
        }

        error_log('HTTP '.$status.' '.($_SERVER['REQUEST_URI'] ?? '').': '.$exception->getMessage());

        $view = is_file(APP.'views/errors/'.$status.'.php') ? 'errors/'.$status : 'errors/404';

        $response = new Response();
        $response->setStatusCode($status);
        $response->setBody(Controller::outputError($view, [
            'message' => $exception->getMessage(),
            'status' => $status,
            'debug' => ErrorHandler::isDebug(),
        ]));

        return $response;
    }
}
