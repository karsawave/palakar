<?php

return [

    'default' => [
        // 'native' (PHP session files), 'database', 'cache' or 'cookie'.
        'driver' => 'native',
        'name' => 'PAN_SID',

        // Seconds without activity before the session data expires.
        'expiration' => 7200,

        // Cookie lifetime in seconds. 0 = until the browser is closed.
        'cookieExpire' => 0,
        'cookiePath' => '/',
        'cookieDomain' => '',

        // true, false, or 'auto' = Secure flag only when the request is HTTPS.
        // Behind a reverse proxy set 'trustProxyHeaders' => true in main.php.
        'cookieSecure' => 'auto',

        // JavaScript cannot read the cookie. Keep this true.
        'cookieHttpOnly' => true,

        // 'Lax' (recommended), 'Strict', or 'None' (cross-site; forces Secure).
        'cookieSameSite' => 'Lax',

        // For the 'database' and 'cache' drivers.
        'driverConnection' => 'default',
        'storageName' => 'sessions',

        // For the 'cookie' driver. Empty secretKey = the one in security.php.
        'isEncrypt' => false,
        'secretKey' => '',
    ],
];
