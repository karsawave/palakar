<?php

declare(strict_types=1);

/**
 * Panada Memcached Driver (the "memcached" PHP extension).
 *
 * @author	Iskandar Soesman
 *
 * @since	Version 0.1
 */

namespace Drivers\Cache;

use Resources\Interfaces;

class Memcached extends \Memcached implements Interfaces\Cache
{
    use KeyNamespace;

    public function __construct(array $config)
    {
        parent::__construct();

        foreach ($config['server'] ?? [] as $server) {
            $this->addServer($server['host'] ?? '127.0.0.1', (int) ($server['port'] ?? 11211), (int) ($server['weight'] ?? 0));
        }
    }

    public function setValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        return $this->set($this->keyToNamespace($key, $namespace), $value, $expire);
    }

    public function addValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        return $this->add($this->keyToNamespace($key, $namespace), $value, $expire);
    }

    /** Only when the key exists. */
    public function updateValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        return $this->replace($this->keyToNamespace($key, $namespace), $value, $expire);
    }

    public function getValue(string $key, string|false $namespace = false): mixed
    {
        return $this->get($this->keyToNamespace($key, $namespace));
    }

    public function deleteValue(string $key, string|false $namespace = false): bool
    {
        return $this->delete($this->keyToNamespace($key, $namespace));
    }

    public function flushValues(): bool
    {
        return $this->flush();
    }

    public function incrementBy(string $key, int $offset = 1): int|false
    {
        return $this->increment($key, $offset);
    }

    public function decrementBy(string $key, int $offset = 1): int|false
    {
        return $this->decrement($key, $offset);
    }
}
