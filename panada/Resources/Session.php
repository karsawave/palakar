<?php

// No strict_types here on purpose: this class only forwards your calls to the
// session driver, so it must not change the typing mode you are used to.

/**
 * Panada session Handler.
 *
 *   $session = new \Resources\Session();     // or $this->resource->session()
 *   $session->setValue('user_id', 15);
 *   $session->getValue('user_id');           // 15, or false
 *   $session->regenerateId();                // after login
 *   $session->destroy();                     // logout
 *
 * Drivers and cookie settings are in app/config/session.php.
 *
 * @link	http://panadaframework.com/
 *
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.1
 */

namespace Resources;

/**
 * @mixin \Drivers\Session\Native
 */
class Session
{
    private Interfaces\Session $driver;
    private array $config;

    public function __construct(string $connection = 'default')
    {
        $config = Config::session();

        if (!isset($config[$connection])) {
            throw new RunException('Session connection "'.$connection.'" is not defined in app/config/session.php');
        }

        $this->config = $config[$connection];
        $this->init();
    }

    /**
     * Override session config options for this instance.
     */
    public function setOption(array $option = []): void
    {
        $this->config = array_merge($this->config, $option);
        $this->init();
    }

    /**
     * Use magic method 'call' to pass user method into driver method.
     */
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

    /**
     * Instantiate the driver class: native, cookie, cache or database.
     */
    private function init(): void
    {
        $driver = 'Drivers\\Session\\'.ucwords(strtolower((string) ($this->config['driver'] ?? 'native')));

        if (!class_exists($driver)) {
            throw new RunException('Unknown session driver "'.$this->config['driver'].'"');
        }

        $this->driver = new $driver($this->config);
    }
}
