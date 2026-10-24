<?php

declare(strict_types=1);

/**
 * Panada APCu Driver: memory shared by the PHP processes of one server.
 * On the command line it needs apc.enable_cli=1.
 *
 * @author	Iskandar Soesman
 *
 * @since	Version 0.2
 */

namespace Drivers\Cache;

use Resources\Interfaces;

class Apc implements Interfaces\Cache
{
    use KeyNamespace;

    public function setValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        return apcu_store($this->keyToNamespace($key, $namespace), $value, $expire);
    }

    public function addValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        return apcu_add($this->keyToNamespace($key, $namespace), $value, $expire);
    }

    public function updateValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        return $this->setValue($key, $value, $expire, $namespace);
    }

    public function getValue(string $key, string|false $namespace = false): mixed
    {
        return apcu_fetch($this->keyToNamespace($key, $namespace));
    }

    public function deleteValue(string $key, string|false $namespace = false): bool
    {
        return apcu_delete($this->keyToNamespace($key, $namespace));
    }

    public function flushValues(): bool
    {
        return apcu_clear_cache();
    }

    public function incrementBy(string $key, int $offset = 1): int|false
    {
        return apcu_inc($key, $offset);
    }

    public function decrementBy(string $key, int $offset = 1): int|false
    {
        return apcu_dec($key, $offset);
    }
}
