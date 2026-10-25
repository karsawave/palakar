<?php

declare(strict_types=1);

/**
 * Panada Redis Driver (the "redis" PHP extension).
 *
 * Values are serialize()d (the same bytes phpredis' PHP serializer writes,
 * so old entries stay readable), except integers: Redis stores them as
 * they are, so incrementBy() works on a value set by setValue().
 *
 * @author	Iskandar Soesman
 *
 * @since	Version 1.0
 */

namespace Drivers\Cache;

use Resources\Interfaces;
use Resources\RunException;

class Redis extends \Redis implements Interfaces\Cache
{
    use KeyNamespace;

    public function __construct(array $config)
    {
        parent::__construct();

        $server = $config['server'][0] ?? [];
        $host = $server['host'] ?? '127.0.0.1';
        $port = (int) ($server['port'] ?? 6379);

        try {
            if (!empty($server['persistent'])) {
                $this->pconnect($host, $port, (float) ($server['timeout'] ?? 0));
            } else {
                $this->connect($host, $port, (float) ($server['timeout'] ?? 0));
            }

            if (!empty($config['password'])) {
                $this->auth($config['password']);
            }
        } catch (\RedisException $e) {
            throw new RunException('Unable to connect to Redis at ' . $host . ':' . $port . ': ' . $e->getMessage(), 0, E_ERROR, null, null, $e);
        }
    }

    public function setValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        $key = $this->keyToNamespace($key, $namespace);

        return (bool) ($expire > 0 ? $this->setex($key, $expire, $this->pack($value)) : $this->set($key, $this->pack($value)));
    }

    public function addValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        // SET with NX: only when the key is missing.
        $options = $expire > 0 ? ['nx', 'ex' => $expire] : ['nx'];

        return (bool) $this->set($this->keyToNamespace($key, $namespace), $this->pack($value), $options);
    }

    public function updateValue(string $key, mixed $value, int $expire = 0, string|false $namespace = false): bool
    {
        return $this->setValue($key, $value, $expire, $namespace);
    }

    public function getValue(string $key, string|false $namespace = false): mixed
    {
        $value = $this->get($this->keyToNamespace($key, $namespace));

        if (!is_string($value)) {
            return false;
        }

        return preg_match('/^-?\d+$/', $value) ? (int) $value : unserialize($value);
    }

    public function deleteValue(string $key, string|false $namespace = false): bool
    {
        return (bool) $this->del($this->keyToNamespace($key, $namespace));
    }

    /**
     * Empties the whole Redis database of this connection.
     */
    public function flushValues(): bool
    {
        return (bool) $this->flushDB();
    }

    public function incrementBy(string $key, int $offset = 1): int|false
    {
        $value = $this->incrBy($key, $offset);

        return is_int($value) ? $value : false;
    }

    public function decrementBy(string $key, int $offset = 1): int|false
    {
        $value = $this->decrBy($key, $offset);

        return is_int($value) ? $value : false;
    }

    private function pack(mixed $value): string
    {
        return is_int($value) ? (string) $value : serialize($value);
    }
}
