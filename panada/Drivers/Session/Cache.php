<?php

declare(strict_types=1);

/**
 * Panada Object Cache session handler: native PHP sessions, data kept in
 * the cache connection named by 'driverConnection' (app/config/cache.php).
 *
 * @author	Iskandar Soesman
 *
 * @since	Version 0.3
 */

namespace Drivers\Session;

use Resources;

class Cache extends Native
{
    public function __construct(array $config)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_save_handler(new CacheHandler(
                new Resources\Cache($config['driverConnection'] ?? 'default'),
                ($config['storageName'] ?? 'sessions') . '_',
                (int) ($config['expiration'] ?? 7200),
            ), true);
        }

        parent::__construct($config);
    }
}
