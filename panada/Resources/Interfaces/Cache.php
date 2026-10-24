<?php

declare(strict_types=1);

/**
 * Interface for Cache Drivers.
 *
 * No return types here on purpose: drivers written for Panada 1 declare
 * none, and PHP refuses an implementation without the interface's return
 * type. The drivers in panada/Drivers/Cache declare them.
 *
 * @author Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @link http://panadaframework.com/
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 *
 * @since version 1.0.0
 */

namespace Resources\Interfaces;

interface Cache
{
    /** Store a value; $expire in seconds, 0 = no expiry. */
    public function setValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false);

    /** Store only when the key does not exist yet. */
    public function addValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false);

    public function updateValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false);

    /** The value, or false when missing or expired. */
    public function getValue(string $key, string|false $namespace = false);

    public function deleteValue(string $key, string|false $namespace = false);

    public function flushValues();

    public function incrementBy(string $key, int $offset = 1);

    public function decrementBy(string $key, int $offset = 1);
}
