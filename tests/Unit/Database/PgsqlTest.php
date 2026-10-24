<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Drivers\Database\PanadaPDO;

/**
 * Runs only when PANADA_TEST_PGSQL is set, e.g.
 * PANADA_TEST_PGSQL="host=127.0.0.1;dbname=panada_test;user=panada;password=panada"
 */
final class PgsqlTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        if ((string) getenv('PANADA_TEST_PGSQL') === '') {
            $this->markTestSkipped('Set PANADA_TEST_PGSQL to run the PostgreSQL tests.');
        }

        parent::setUp();
    }

    protected function connect(): PanadaPDO
    {
        parse_str(str_replace(';', '&', (string) getenv('PANADA_TEST_PGSQL')), $c);

        return new PanadaPDO([
            'driver' => 'pgsql',
            'host' => $c['host'] ?? '127.0.0.1',
            'port' => (int) ($c['port'] ?? 5432),
            'database' => $c['dbname'] ?? 'panada_test',
            'user' => $c['user'] ?? 'postgres',
            'password' => $c['password'] ?? '',
        ]);
    }

    protected function createTableSql(): string
    {
        $this->db->query('DROP TABLE IF EXISTS posts');

        return 'CREATE TABLE users (id SERIAL PRIMARY KEY, name TEXT, email TEXT, active BOOLEAN, score INTEGER, deleted_at TEXT)';
    }
}
