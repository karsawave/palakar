<?php

namespace Controllers;

class Api extends \Resources\Controller
{
    public function store(): string
    {
        return 'stored without csrf';
    }
}
