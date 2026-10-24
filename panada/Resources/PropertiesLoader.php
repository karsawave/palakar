<?php

// No strict_types here on purpose: this class forwards calls coming from
// your code (or from URL segments, which are always strings), so it must not
// change the typing mode the caller is used to.

/**
 * Properties Loader: the object behind $this->model->name().
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

final class PropertiesLoader
{
    /**
     * Objects created during this request, so $this->model->users()
     * returns the same instance every time it is called with the same arguments.
     */
    private static array $instances = [];

    public function __construct(
        private array $childNamespace,
        private string $classNamespace,
    ) {
    }

    public function __call(string $name, array $arguments = []): object
    {
        $class = $this->classNamespace.'\\'.ucwords($name);

        // Inside a module, the module's own Models/Libraries win.
        if (($this->childNamespace[0] ?? '') === 'Modules' && $this->classNamespace !== 'Resources') {
            $moduleClass = 'Modules\\'.$this->childNamespace[1].'\\'.$class;

            if (class_exists($moduleClass)) {
                $class = $moduleClass;
            }
        }

        if (!class_exists($class)) {
            throw new RunException('Class '.$class.' not found');
        }

        $key = $class.'|'.json_encode($arguments);

        return self::$instances[$key] ??= new $class(...$arguments);
    }

    public function __get(string $name): object
    {
        return $this->__call($name);
    }
}
