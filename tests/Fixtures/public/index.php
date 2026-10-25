<?php

// Front controller of the fixture app used by tests/Http.
define('APP', dirname(__DIR__) . '/app/');
define('INDEX_FILE', basename(__FILE__));
define('GEAR', dirname(__DIR__, 3) . '/panada/');

// PANADA_NO_COMPOSER=1 runs the app with Panada's own autoloader only.
define('VENDOR', getenv('PANADA_NO_COMPOSER') ? __DIR__ . '/no-vendor/' : dirname(__DIR__, 3) . '/vendor/');

require GEAR . 'Gear.php';

Gear::send(E_ALL);
