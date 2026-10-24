<?php

declare(strict_types=1);

/**
 * Handler for configuration.
 *
 * Config::main() reads app/config/main.php, Config::myowncfg() reads
 * app/config/myowncfg.php, and so on. Each file is read only once.
 *
 * @author  Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @link    http://panadaframework.com/
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 *
 * @since   version 1.0.0
 */

namespace Resources;

class Config
{
    private static array $config = [];

    private static function load(string $name): array
    {
        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $name) || !is_file($file = APP.'config/'.$name.'.php')) {
            throw new RunException('Config file app/config/'.$name.'.php does not exist');
        }

        return self::$config[$name] = require $file;
    }

    public static function main(): array
    {
        return self::load('main');
    }

    public static function session(): array
    {
        return self::load('session');
    }

    public static function cache(): array
    {
        return self::load('cache');
    }

    public static function database(): array
    {
        return self::load('database');
    }

    public static function security(): array
    {
        return self::load('security');
    }

    /**
     * Handler for user defined config, e.g. Config::payment().
     */
    public static function __callStatic(string $name, array $arguments = []): array
    {
        return self::load($name);
    }
}
