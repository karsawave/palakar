<?php

return [

    'defaultController' => 'Home',

    // true  = show detailed error pages (development).
    // false = show a generic error page and only write details to the log (production).
    'debug' => true,

    // Just put null value if you have enabled the .htaccess file.
    'indexFile' => INDEX_FILE.'/',

    // Set to true only when the app runs behind a reverse proxy you control
    // (Traefik, Nginx, Cloudflare Tunnel). Panada will then trust the
    // X-Forwarded-Proto header to know the visitor is on HTTPS.
    'trustProxyHeaders' => false,

    'module' => [
        'path' => APP,
        'domainMapping' => [],
    ],

    'vendor' => [
        'path' => defined('VENDOR') ? VENDOR : dirname(APP).'/vendor/',
    ],

    'alias' => [
        /*
        'controller' => [
            'class' => 'Alias',
            'method' => 'index'
        ],
        */
        'method' => 'alias',
    ],
];
