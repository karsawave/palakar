<?php

declare(strict_types=1);

/**
 * Panada Local Memory Cacher: values live until the end of this request.
 * Handy when the same object is needed twice in one request, and as a
 * stand-in for a cache server in development. Expiry is ignored.
 *
 * @author	Iskandar Soesman
 *
 * @since	Version 0.3
 */

namespace Drivers\Cache;

use Resources\Interfaces;

class Dummy implements Interfaces\Cache
{
    use KeyNamespace;

    /** Shared by every Dummy of this request. */
    private static array $holder = [];

    public function setValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        self::$holder[$this->keyToNamespace($key, $namespace)] = $value;

        return true;
    }

    public function addValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        $key = $this->keyToNamespace($key, $namespace);

        if (array_key_exists($key, self::$holder)) {
            return false;
        }

        self::$holder[$key] = $value;

        return true;
    }

    public function updateValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        return $this->setValue($key, $value, $expire, $namespace);
    }

    public function getValue(string $key, string|false $namespace = false): mixed
    {
        $key = $this->keyToNamespace($key, $namespace);

        return array_key_exists($key, self::$holder) ? self::$holder[$key] : false;
    }

    public function deleteValue(string $key, string|false $namespace = false): bool
    {
        unset(self::$holder[$this->keyToNamespace($key, $namespace)]);

        return true;
    }

    public function flushValues(): bool
    {
        self::$holder = [];

        return true;
    }

    public function incrementBy(string $key, int $offset = 1): int
    {
        $value = (int) $this->getValue($key) + $offset;
        $this->setValue($key, $value);

        return $value;
    }

    public function decrementBy(string $key, int $offset = 1): int
    {
        return $this->incrementBy($key, -$offset);
    }
}
