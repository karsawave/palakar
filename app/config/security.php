<?php

return [
    // A long random string, unique per application. Generate one with:
    //   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
    // Prefer the PALAKAR_SECRET_KEY environment variable, so the secret
    // never ends up in git.
    'secretKey' => getenv('PALAKAR_SECRET_KEY') ?: '_put_your_random_string_here_',

    // Every POST, PUT, PATCH and DELETE request must carry the token from
    // csrf_field() (or the X-CSRF-Token header), otherwise it gets a 403.
    'csrf' => [
        'enabled' => true,

        // URL paths that skip the check, e.g. a payment webhook or an API
        // authenticated by tokens instead of cookies. Wildcards allowed.
        'except' => [
            // 'api/*',
            // 'payment/callback',
        ],
    ],
];
