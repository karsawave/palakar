<?php

declare(strict_types=1);

namespace Drivers\Session;

use Resources\Cache;

/**
 * Stores PHP sessions in a cache server (Redis, Memcached, ...).
 * Expired entries are removed by the cache server itself.
 */
class CacheHandler implements \SessionHandlerInterface
{
    public function __construct(
        private Cache $cache,
        private string $prefix = 'sessions_',
        private int $lifetime = 7200,
    ) {}

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $data = $this->cache->getValue($this->prefix . $id);

        return is_string($data) ? $data : '';
    }

    public function write(string $id, string $data): bool
    {
        return (bool) $this->cache->setValue($this->prefix . $id, $data, $this->lifetime);
    }

    public function destroy(string $id): bool
    {
        $this->cache->deleteValue($this->prefix . $id);

        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }
}
