<?php

namespace Controllers;

class Boom extends \Resources\Controller
{
    public function index(): string
    {
        throw new \RuntimeException('Kaboom <script>alert(1)</script>');
    }

    public function warning(): string
    {
        $list = [];

        return $list['missing'];
    }

    public function fatal(): string
    {
        ini_set('memory_limit', '16M');
        $big = str_repeat('x', 64 * 1024 * 1024);

        return $big;
    }

    public function forbidden(): string
    {
        throw new \Resources\HttpException('Members only', 403);
    }
}
