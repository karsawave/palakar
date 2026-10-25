<?php

return [
    'defaultController' => 'Home',
    'debug' => getenv('PANADA_DEBUG') !== '0',
    'indexFile' => null,
    'trustProxyHeaders' => getenv('PANADA_TRUST_PROXY') === '1',
    'module' => ['path' => APP, 'domainMapping' => []],
    'vendor' => ['path' => VENDOR],
    'alias' => ['method' => 'alias'],
    'namespace' => ['Fixture' => APP . 'custom/'],
];
