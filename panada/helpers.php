<?php

declare(strict_types=1);

/**
 * Small global helpers for views. Loaded by Gear (and by Composer's "files").
 */

if (!function_exists('e')) {
    /**
     * Escape a value before printing it in HTML. Use it for anything that
     * came from a user or the database:
     *
     *   <p>Hello, <?= e($name) ?></p>
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    /**
     * CSRF token of the current session, e.g. for an AJAX header.
     */
    function csrf_token(): string
    {
        return Resources\Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Hidden CSRF input. Put it inside every <form method="post">.
     */
    function csrf_field(): string
    {
        return Resources\Csrf::field();
    }
}
