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
