<?php

declare(strict_types=1);

/**
 * Exception for runtime errors in Panada and in your application.
 *
 *   throw new \Resources\RunException('Payment gateway is not configured');
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

class RunException extends \ErrorException
{
    public function __construct(string $message = '', int $code = 0, int $severity = E_ERROR, ?string $filename = null, ?int $line = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $severity, $filename, $line, $previous);
    }

    public function __toString(): string
    {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }

    /**
     * Kept for backward compatibility: build the error response for an exception.
     */
    public static function main(\Throwable $exception): Response
    {
        return ErrorHandler::render($exception);
    }

    /**
     * Kept for backward compatibility: build an error response from a message.
     */
    public static function outputError(string $message = '', string|false|null $file = null, int|false|null $line = null): Response
    {
        return ErrorHandler::render(new self($message, 0, E_ERROR, $file ?: null, $line ?: null));
    }

    /**
     * Generates class/method backtrace, e.g. "Controllers\Home->index line 12".
     */
    public static function getErrorCaller(int $offset = 1): string
    {
        $caller = [];
        $trace = array_reverse(array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), $offset));

        foreach ($trace as $call) {
            if (!isset($call['class']) || $call['class'] === __CLASS__) {
                continue;
            }

            $caller[] = $call['class'].'->'.$call['function'].(isset($call['line']) ? ' line '.$call['line'] : '');
        }

        return implode(', ', $caller);
    }
}
