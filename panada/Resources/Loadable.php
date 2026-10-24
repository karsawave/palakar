<?php

declare(strict_types=1);

namespace Resources;

/**
 * Gives a class the famous Panada shortcuts:
 *
 *   $this->model->users()         -> new Models\Users()
 *   $this->library->payment()     -> new Libraries\Payment()
 *   $this->resource->session()    -> new Resources\Session()
 *
 * Resources\Controller and Resources\Model already use it. Any other class
 * (a library, for example) can add it with `use \Resources\Loadable;`.
 */
trait Loadable
{
    public function __get(string $name): PropertiesLoader
    {
        $namespaces = [
            'model' => 'Models',
            'models' => 'Models',
            'library' => 'Libraries',
            'libraries' => 'Libraries',
            'resource' => 'Resources',
            'resources' => 'Resources',
        ];

        $key = strtolower($name);

        if (!isset($namespaces[$key])) {
            throw new RunException('Undefined property '.static::class.'::$'.$name);
        }

        return new PropertiesLoader(explode('\\', static::class), $namespaces[$key]);
    }
}
