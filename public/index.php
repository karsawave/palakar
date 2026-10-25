<?php

// `composer serve` (php -S) sends every request here; let it serve existing
// files such as assets/css/main.css itself, except this front controller.
if (PHP_SAPI === 'cli-server') {
    $file = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

    if ($file !== false && $file !== __FILE__ && is_file($file)) {
        return false;
    }
}

// Sets the default timezone used by all date/time. Adjust to your location.
date_default_timezone_set('Asia/Jakarta');

// You can adjust these following constants if necessary.

// The APP constant is where your application folder located.
define('APP', dirname(__DIR__) . '/app/');

// The INDEX_FILE constant is this default file name.
define('INDEX_FILE', basename(__FILE__));

// The GEAR constant is where the core (panada/ folder) is located.
define('GEAR', dirname(__DIR__) . '/panada/');

// The VENDOR constant is where Composer installs packages (`composer install`).
// Palakar still runs when this folder does not exist.
define('VENDOR', dirname(__DIR__) . '/vendor/');

require_once GEAR . 'Gear.php';

// Debug pages are switched on/off with 'debug' in app/config/main.php.
// The parameter below only sets which PHP errors are reported, see
// https://www.php.net/manual/en/function.error-reporting.php
Gear::send(E_ALL);
