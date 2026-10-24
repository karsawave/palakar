<?php

namespace Controllers;

use Resources\Csrf;

class Auth extends \Resources\Controller
{
    public function login(string $name): string
    {
        $session = $this->resource->session();
        $session->regenerateId();
        Csrf::regenerate();
        $session->setValue('user', $name);

        return 'logged in';
    }

    public function whoami(): string
    {
        return (string) $this->resource->session()->getValue('user');
    }

    public function logout(): string
    {
        $this->resource->session()->destroy();

        return 'bye';
    }
}
