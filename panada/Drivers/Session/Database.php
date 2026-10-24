<?php

declare(strict_types=1);

/**
 * Panada Database session handler: native PHP sessions, data kept in a table.
 * See DatabaseHandler.php for the table structure.
 *
 * @author	Iskandar Soesman
 *
 * @since	Version 0.3
 */

namespace Drivers\Session;

use Resources;

class Database extends Native
{
    public function __construct(array $config)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_save_handler(new DatabaseHandler(
                new Resources\Database($config['driverConnection'] ?? 'default'),
                $config['storageName'] ?? 'sessions',
                (int) ($config['expiration'] ?? 7200)
            ), true);
        }

        parent::__construct($config);
    }
}
