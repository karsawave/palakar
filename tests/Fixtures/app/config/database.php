<?php

return [
    'default' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
    ],
    'legacy' => [
        'driver' => 'sqlite3',
        'database' => ':memory:',
    ],
    'http' => [
        'driver' => 'sqlite',
        'database' => getenv('PANADA_TEST_SQLITE') ?: ':memory:',
    ],
];
