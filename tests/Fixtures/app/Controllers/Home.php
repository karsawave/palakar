<?php

namespace Controllers;

class Home extends \Resources\Controller
{
    public function index(): string
    {
        return 'fixture home';
    }

    public function echoed(): void
    {
        echo 'printed with echo';
    }

    public function data(): array
    {
        return ['name' => 'Panada', 'version' => 2];
    }

    public function show(int $id): string
    {
        return 'id=' . $id . ' ' . get_debug_type($id);
    }
}
