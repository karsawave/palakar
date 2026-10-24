<?php

/*
 * Database connections. All of them run on PDO with prepared statements.
 * 'driver' is one of: mysql (also MariaDB), pgsql, sqlite.
 *
 * Use one in your code with:
 *   new \Resources\Database();          // 'default'
 *   new \Resources\Database('pgsql');   // a named connection
 */

return [

    // Works out of the box: a SQLite file inside app/storage/.
    'default' => [
        'driver' => 'sqlite',
        'database' => APP.'storage/database.sqlite',
    ],

    /*
    'mysql' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'password' => '',
        'database' => 'palakar',
        'charset' => 'utf8mb4',
        'persistent' => false,
    ],

    'pgsql' => [
        'driver' => 'pgsql',
        'host' => 'localhost',
        'port' => 5432,
        'user' => 'postgres',
        'password' => '',
        'database' => 'palakar',
        'charset' => 'UTF8',
        'persistent' => false,
    ],

    // Any PDO DSN and PDO options can also be given directly:
    'custom' => [
        'driver' => 'pgsql',
        'dsn' => 'pgsql:host=db;port=5432;dbname=palakar;sslmode=require',
        'user' => 'palakar',
        'password' => 'secret',
        'options' => [PDO::ATTR_TIMEOUT => 5],
    ],
    */
];
