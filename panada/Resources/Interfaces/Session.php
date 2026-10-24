<?php

declare(strict_types=1);

/**
 * Interface for Session Drivers.
 *
 * @author Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @link http://panadaframework.com/
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 *
 * @since version 1.0.0
 */

namespace Resources\Interfaces;

interface Session
{
    public function setValue(string|array $name, mixed $value = ''): void;
    public function getValue(string $name): mixed;
    public function getAllValue(): array;
    public function deleteValue(string $name): void;
    public function regenerateId(): void;
    public function destroy(bool $setExpireHeader = false): void;
}
