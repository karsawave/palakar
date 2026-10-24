<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Drivers\Database\PanadaPDO;
use Resources\Database;

final class SqliteTest extends DatabaseTestCase
{
    protected function connect(): PanadaPDO
    {
        return new PanadaPDO(['driver' => 'sqlite', 'database' => ':memory:']);
    }

    protected function createTableSql(): string
    {
        return 'CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, active BOOLEAN, score INTEGER, deleted_at TEXT)';
    }

    public function testFacadeMapsOldDriverNamesAndKeepsChaining(): void
    {
        $db = new Database('legacy');   // 'driver' => 'sqlite3' in the fixture config
        $db->query('CREATE TABLE t (id INTEGER PRIMARY KEY, v TEXT)');
        $db->insert('t', ['v' => 'a']);

        $this->assertSame('a', $db->select('v')->from('t')->where('id', '=', 1)->getOne()->v);
    }

    public function testDsnBuilder(): void
    {
        $this->assertSame('mysql:host=db;port=3306;dbname=app;charset=utf8mb4', PanadaPDO::dsn(['driver' => 'mysql', 'host' => 'db', 'database' => 'app']));
        $this->assertSame("pgsql:host=localhost;port=5432;dbname=app;options='--client_encoding=UTF8'", PanadaPDO::dsn(['driver' => 'pgsql', 'database' => 'app']));
        $this->assertSame('sqlite::memory:', PanadaPDO::dsn(['driver' => 'sqlite', 'database' => ':memory:']));
    }
}
