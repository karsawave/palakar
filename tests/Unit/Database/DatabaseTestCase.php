<?php

declare(strict_types=1);

namespace Tests\Unit\Database;

use Drivers\Database\PanadaPDO;
use PHPUnit\Framework\TestCase;
use Resources\RunException;

/**
 * The same checks run on every database: SqliteTest always, PgsqlTest when
 * PANADA_TEST_PGSQL is set.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected PanadaPDO $db;

    abstract protected function connect(): PanadaPDO;

    abstract protected function createTableSql(): string;

    protected function setUp(): void
    {
        $this->db = $this->connect();
        $this->db->query('DROP TABLE IF EXISTS users');
        $this->db->query($this->createTableSql());
        $this->db->insert('users', ['name' => 'Budi', 'email' => 'budi@example.com', 'active' => true, 'score' => 10]);
        $this->db->insert('users', ['name' => 'Ani', 'email' => 'ani@example.com', 'active' => false, 'score' => 20]);
        $this->db->insert('users', ['name' => 'Citra', 'email' => 'citra@example.com', 'active' => true, 'score' => 30]);
    }

    public function testValuesAreBoundNotConcatenated(): void
    {
        $this->db->select('id')->from('users')->where('email', '=', "x' OR '1'='1")->getAll();

        $this->assertSame('SELECT id FROM users WHERE email = ?', $this->db->getLastQuery());
        $this->assertSame(["x' OR '1'='1"], $this->db->getLastBindings());
    }

    public function testClassicInjectionPayloadsFindNothing(): void
    {
        foreach (["' OR '1'='1", "' OR 1=1 --", "x'; DROP TABLE users; --", '" OR ""="'] as $payload) {
            $this->assertSame([], $this->db->getAll('users', ['email' => $payload]), $payload);
        }

        $this->assertSame(3, (int) $this->db->select('COUNT(*)')->from('users')->getVar());
    }

    public function testInjectionPayloadIsStoredLiterally(): void
    {
        $payload = "O'Reilly'); DROP TABLE users; --";
        $this->db->insert('users', ['name' => $payload, 'email' => 'o@example.com', 'active' => true, 'score' => 1]);

        $row = $this->db->getOne('users', ['email' => 'o@example.com']);

        $this->assertSame($payload, $row->name);
        $this->assertSame(4, (int) $this->db->select('COUNT(*)')->from('users')->getVar());
    }

    public function testInvalidIdentifiersAreRejected(): void
    {
        $attempts = [
            fn () => $this->db->from('users')->orderBy('name; DROP TABLE users')->getAll(),
            fn () => $this->db->from('users')->orderBy('id', 'DESC; DROP TABLE users')->getAll(),
            fn () => $this->db->from('users')->where('1=1 OR id', '=', 1)->getAll(),
            fn () => $this->db->from('users')->where('id', '= 1 OR 1 =', 1)->getAll(),
            fn () => $this->db->insert('users', ["name) VALUES ('x'); --" => 'x']),
            fn () => $this->db->update('users; DROP TABLE users', ['name' => 'x'], ['id' => 1]),
            fn () => $this->db->from('users')->where('id', '=', 1, 'OR 1=1 --')->getAll(),
        ];

        foreach ($attempts as $i => $attempt) {
            try {
                $attempt();
                $this->fail('Attempt #'.$i.' was not rejected');
            } catch (RunException $e) {
                $this->assertStringContainsString('Invalid', $e->getMessage());
            }
        }
    }

    public function testWhereOperators(): void
    {
        $this->assertCount(2, $this->db->from('users')->where('id', 'IN', [1, 3, 99])->getAll());
        $this->assertCount(1, $this->db->from('users')->where('id', 'NOT IN', [1, 3])->getAll());
        $this->assertCount(0, $this->db->from('users')->where('id', 'IN', [])->getAll());
        $this->assertCount(2, $this->db->from('users')->where('score', 'BETWEEN', [15, 30])->getAll());
        $this->assertCount(3, $this->db->from('users')->where('deleted_at', '=', null)->getAll());
        $this->assertCount(0, $this->db->from('users')->where('deleted_at', 'IS NOT', 'NULL')->getAll());
        $this->assertCount(2, $this->db->from('users')->where('active', '=', true)->getAll());
        $this->assertCount(1, $this->db->from('users')->where('name', 'LIKE', 'B%')->getAll());
        $this->assertCount(2, $this->db->from('users')->where('id', '=', 1, 'OR')->where('id', '=', 2)->getAll());
        $this->assertCount(1, $this->db->from('users')->whereRaw('(score > ? AND active = ?)', [15, true])->getAll());
    }

    public function testSelectChainOrderLimitAndFetchModes(): void
    {
        $rows = $this->db->select('name')->from('users')->orderBy('score', 'desc')->limit(2, 1)->fetchAs('array')->getAll();

        $this->assertSame([['name' => 'Ani'], ['name' => 'Budi']], $rows);
        $this->assertIsObject($this->db->getOne('users', ['id' => 1]));
        $this->assertInstanceOf(\PDOStatement::class, $this->db->fetchAs('iterator')->getAll('users'));
        $this->assertFalse($this->db->getOne('users', ['id' => 999]));
    }

    public function testGroupByHaving(): void
    {
        $rows = $this->db->select('active', 'COUNT(id) AS total')->from('users')
            ->groupBy('active')->having('COUNT(id)', '>', 1)->getAll();

        $this->assertCount(1, $rows);
        $this->assertSame(2, (int) $rows[0]->total);
    }

    public function testJoin(): void
    {
        $this->db->query('DROP TABLE IF EXISTS posts');
        $this->db->query('CREATE TABLE posts (id INTEGER PRIMARY KEY, user_id INTEGER, title TEXT)');
        $this->db->insert('posts', ['id' => 1, 'user_id' => 1, 'title' => 'Halo']);

        $rows = $this->db->select('u.name', 'p.title')->from('users u')
            ->join('posts p', 'left')->on('p.user_id', '=', 'u.id')
            ->where('p.title', '=', 'Halo')->getAll();

        $this->assertSame('Budi', $rows[0]->name);
    }

    public function testUpdateAndDelete(): void
    {
        $this->assertTrue($this->db->update('users', ['score' => 99], ['name' => 'Budi']));
        $this->assertSame(1, $this->db->affectedRows());
        $this->assertSame(99, (int) $this->db->getOne('users', ['name' => 'Budi'])->score);

        $this->db->where('score', '<', 25)->delete('users');
        $this->assertSame(2, (int) $this->db->select('COUNT(*)')->from('users')->getVar());
    }

    public function testUpdateOrDeleteWithoutWhereIsRefused(): void
    {
        foreach ([fn () => $this->db->delete('users'), fn () => $this->db->update('users', ['score' => 0])] as $call) {
            try {
                $call();
                $this->fail('Write without WHERE was not refused');
            } catch (RunException $e) {
                $this->assertStringContainsString('without a WHERE', $e->getMessage());
            }
        }

        $this->assertSame(3, (int) $this->db->select('COUNT(*)')->from('users')->getVar());
    }

    public function testTransactionRollsBackOnException(): void
    {
        try {
            $this->db->transaction(function ($db): void {
                $db->insert('users', ['name' => 'Ghost', 'email' => 'g@example.com', 'active' => true, 'score' => 0]);

                throw new \DomainException('payment failed');
            });
        } catch (\DomainException) {
        }

        $this->assertNull($this->db->getVar('SELECT id FROM users WHERE name = ?', ['Ghost']));

        $id = $this->db->transaction(function ($db) {
            $db->insert('users', ['name' => 'Dewi', 'email' => 'd@example.com', 'active' => true, 'score' => 5]);

            return $db->insertId();
        });

        $this->assertSame('Dewi', $this->db->getVar('SELECT name FROM users WHERE id = ?', [(int) $id]));
    }

    public function testRawQueriesWithNamedAndPositionalBindings(): void
    {
        $this->assertSame('Ani', $this->db->getVar('SELECT name FROM users WHERE email = :email', ['email' => 'ani@example.com']));
        $this->assertCount(2, $this->db->results('SELECT * FROM users WHERE score > ?', [15]));
    }

    public function testBackwardCompatibleSignatures(): void
    {
        // Panada 1 style: results($sql, 'array') and command() fed to results().
        $this->assertIsArray($this->db->results('SELECT * FROM users', 'array')[0]);

        $sql = $this->db->select('name')->from('users')->where('score', '>', 15)->command();
        $this->assertCount(2, $this->db->results($sql));
    }

    public function testErrorsAreThrownWithTheQuery(): void
    {
        $this->expectException(RunException::class);
        $this->expectExceptionMessage('SQL: SELECT nope FROM users');

        $this->db->query('SELECT nope FROM users');
    }
}
