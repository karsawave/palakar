<?php

declare(strict_types=1);

// The unit tests run against a small fixture application.
define('APP', __DIR__.'/Fixtures/app/');
define('GEAR', dirname(__DIR__).'/panada/');
define('INDEX_FILE', 'index.php');
define('VENDOR', dirname(__DIR__).'/vendor/');

require VENDOR.'autoload.php';
require_once GEAR.'Gear.php';

Gear::registerAutoloader();
