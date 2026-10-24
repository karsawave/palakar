<?php

return [
    'default' => [
        'driver' => getenv('PANADA_SESSION_DRIVER') ?: 'native',
        'name' => 'PAN_SID',
        'expiration' => 7200,
        'cookieExpire' => 0,
        'cookiePath' => '/',
        'cookieDomain' => '',
        'cookieSecure' => 'auto',
        'cookieHttpOnly' => true,
        'cookieSameSite' => 'Lax',
        'driverConnection' => 'http',
        'storageName' => 'sessions',
        'isEncrypt' => getenv('PANADA_SESSION_ENCRYPT') === '1',
        'secretKey' => '',
    ],
];
