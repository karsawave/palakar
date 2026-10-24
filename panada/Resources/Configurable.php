<?php

declare(strict_types=1);

namespace Resources;

/**
 * setOption() for classes configured through their public properties
 * (Pagination, Upload, Image, Email):
 *
 *   $pagination->setOption('current', $page);
 *   $pagination->setOption(['limit' => 10, 'total' => $total]);
 *
 * Values often come from the URL, a config file or getenv() as strings, so
 * they are cast to the type of the property (int, float, bool).
 */
trait Configurable
{
    public function setOption(string|array $var, mixed $value = false): static
    {
        foreach (is_array($var) ? $var : [$var => $value] as $name => $value) {
            $name = (string) $name;
            $property = property_exists($this, $name) ? new \ReflectionProperty($this, $name) : null;

            // Only public options; a typo must not create a new property.
            if ($property === null || !$property->isPublic() || $property->isStatic()) {
                throw new RunException('Unknown option '.static::class.'::$'.$name);
            }

            $this->$name = match ((string) $property->getType()) {
                'int' => (int) $value,
                'float' => (float) $value,
                'bool' => (bool) $value,
                default => $value,
            };
        }

        return $this;
    }
}
