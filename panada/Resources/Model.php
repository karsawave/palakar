<?php

declare(strict_types=1);

namespace Resources;

/**
 * Optional parent class for your models.
 *
 *   namespace Models;
 *
 *   class Users extends \Resources\Model
 *   {
 *       public function all(): array
 *       {
 *           return $this->resource->database()->getAll('users');
 *       }
 *   }
 *
 * It only adds $this->model, $this->library and $this->resource.
 */
abstract class Model
{
    use Loadable;
}
