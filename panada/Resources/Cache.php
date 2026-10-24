<?php

// No strict_types here on purpose: this class forwards calls coming from
// your code to the cache driver, so it must not change the typing mode the
// caller is used to (an $expire read from config may be a string).

/**
 * Panada cache API.
 *
 *   $cache = new \Resources\Cache();          // 'default' in app/config/cache.php
 *   $cache->setValue('rates', $rates, 3600);
 *   $rates = $cache->getValue('rates');       // false when missing or expired
 *
 * Drivers: dummy (memory of the current request), apc (APCu), memcached,
 * memcache and redis. Calls go to the driver, see
 * panada/Resources/Interfaces/Cache.php. The Redis and Memcached drivers
 * also accept their native methods, e.g. $cache->hSet(...).
 *
 * @link	http://panadaframework.com/
 *
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.3
 */

namespace Resources;

/**
 * @mixin Interfaces\Cache
 */
class Cache
{
    /** Drivers that need a PHP extension. */
    private const EXTENSIONS = [
        'apc' => 'apcu',
        'memcache' => 'memcache',
        'memcached' => 'memcached',
        'redis' => 'redis',
    ];

    private Interfaces\Cache $driver;

    public function __construct(string $connection = 'default', string|false $specifiedDriver = false)
    {
        $config = Config::cache()[$connection] ?? null;

        if (!is_array($config)) {
            throw new RunException('Cache connection "'.$connection.'" is not defined in app/config/cache.php');
        }

        $driver = strtolower((string) ($specifiedDriver ?: ($config['driver'] ?? '')));
        $class = 'Drivers\\Cache\\'.ucfirst($driver);

        if (isset(self::EXTENSIONS[$driver]) && !extension_loaded(self::EXTENSIONS[$driver])) {
            throw new RunException('Cache driver "'.$driver.'" needs the '.self::EXTENSIONS[$driver].' PHP extension.');
        }

        if (!preg_match('/^[a-z]+$/', $driver) || !is_subclass_of($class, Interfaces\Cache::class)) {
            throw new RunException('Unknown cache driver "'.$driver.'" in app/config/cache.php');
        }

        $this->driver = new $class($config);
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->driver->$name(...$arguments);
    }

    public function __get(string $name): mixed
    {
        return $this->driver->$name;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->driver->$name = $value;
    }
}
