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
    // A file: every ActiveRecord model opens its own connection.
    'ar' => [
        'driver' => 'sqlite',
        'database' => sys_get_temp_dir().'/panada-activerecord-test.sqlite',
    ],
];
