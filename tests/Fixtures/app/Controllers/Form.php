<?php

namespace Controllers;

class Form extends \Resources\Controller
{
    public function index(): string
    {
        return '<form method="post">' . csrf_field() . '</form>';
    }

    public function save(): string
    {
        return 'saved';
    }
}
