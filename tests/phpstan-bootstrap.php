<?php

// PHPStan only needs the constants public/index.php defines. Registering
// Gear's autoloader here would execute files (e.g. "extends \Redis") instead
// of letting PHPStan parse them.
define('APP', __DIR__.'/Fixtures/app/');
define('GEAR', dirname(__DIR__).'/panada/');
define('INDEX_FILE', 'index.php');
define('VENDOR', dirname(__DIR__).'/vendor/');
