<?php

namespace Controllers;

use Resources;

class Home extends Resources\Controller
{
    public function index()
    {
        return $this->output('layouts/main', [
            'title' => 'Hello world!',
            'content' => $this->output('home'),
        ]);
    }
}
