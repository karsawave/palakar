<?php

// No strict_types here on purpose: this class forwards calls coming from
// your code (or from URL segments, which are always strings), so it must not
// change the typing mode the caller is used to.

/**
 * Panada Database API.
 *
 *   $db = new \Resources\Database();          // 'default' connection
 *   $db = $this->resource->database('pg');    // from a controller
 *
 *   $user = $db->getOne('users', ['email' => $email]);
 *
 * All calls are forwarded to Drivers\Database\PanadaPDO, see
 * panada/Resources/Interfaces/Database.php for the full list.
 *
 * @link	http://panadaframework.com/
 *
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.3
 */

namespace Resources;

use Drivers\Database\PanadaPDO;

/**
 * @mixin PanadaPDO
 */
class Database
{
    /** Old driver names from Panada 1 configs, mapped to PDO drivers. */
    private const DRIVER_ALIASES = [
        'mysqli' => 'mysql',
        'postgre' => 'pgsql',
        'postgres' => 'pgsql',
        'postgresql' => 'pgsql',
        'sqlite3' => 'sqlite',
    ];

    private PanadaPDO $driver;

    public function __construct(string $connection = 'default')
    {
        $config = Config::database();

        if (!isset($config[$connection])) {
            throw new RunException('Database connection "' . $connection . '" is not defined in app/config/database.php');
        }

        $config = $config[$connection];
        $driver = strtolower((string) ($config['driver'] ?? ''));
        $config['driver'] = self::DRIVER_ALIASES[$driver] ?? $driver;

        $this->driver = new PanadaPDO($config, $connection);
    }

    /**
     * Use magic method 'call' to pass user method into driver method.
     */
    public function __call(string $name, array $arguments): mixed
    {
        $result = $this->driver->$name(...$arguments);

        // Keep chaining on this object: $db->select()->from()->getAll().
        return $result === $this->driver ? $this : $result;
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
