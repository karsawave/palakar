<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Drivers\Database\PanadaPDO;

/**
 * Runs only when PANADA_TEST_MYSQL is set, e.g.
 * PANADA_TEST_MYSQL="host=127.0.0.1;dbname=panada_test;user=panada;password=panada"
 * Works with MySQL and MariaDB; CI uses MariaDB.
 */
final class MysqlTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        if ((string) getenv('PANADA_TEST_MYSQL') === '') {
            $this->markTestSkipped('Set PANADA_TEST_MYSQL to run the MySQL/MariaDB tests.');
        }

        parent::setUp();
    }

    protected function connect(): PanadaPDO
    {
        parse_str(str_replace(';', '&', (string) getenv('PANADA_TEST_MYSQL')), $c);

        return new PanadaPDO([
            'driver' => 'mysql',
            'host' => $c['host'] ?? '127.0.0.1',
            'port' => (int) ($c['port'] ?? 3306),
            'database' => $c['dbname'] ?? 'panada_test',
            'user' => $c['user'] ?? 'root',
            'password' => $c['password'] ?? '',
        ]);
    }

    protected function createTableSql(): string
    {
        $this->db->query('DROP TABLE IF EXISTS posts');

        return 'CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, name TEXT, email TEXT, active BOOLEAN, score INT, deleted_at TEXT)';
    }
}
