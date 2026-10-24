<?php

declare(strict_types=1);

/**
 * Panada PDO Database Driver, the only database driver since Panada 2.
 *
 * Supports MySQL/MariaDB, PostgreSQL and SQLite through PHP's PDO extension.
 * Every query is a prepared statement: values are bound, never concatenated.
 *
 * @author	Azhari Harahap <azhari@harahap.us>
 *
 * @since	Version 1.0
 */

namespace Drivers\Database;

use Drivers\Abstraction\Sql;
use PDO;
use PDOException;
use PDOStatement;
use Resources\Interfaces\Database as DatabaseInterface;
use Resources\RunException;

class PanadaPDO extends Sql implements DatabaseInterface
{
    private ?PDO $link = null;
    private ?PDOStatement $lastStatement = null;

    public function __construct(private array $config, private string $connection = 'default')
    {
        if (!extension_loaded('pdo')) {
            throw new RunException('PDO extension not installed.');
        }
    }

    /**
     * Build the PDO DSN from a connection in app/config/database.php.
     */
    public static function dsn(array $config): string
    {
        if (!empty($config['dsn'])) {
            return $config['dsn'];
        }

        $driver = $config['driver'] ?? '';
        $database = (string) ($config['database'] ?? '');

        return match ($driver) {
            'mysql' => empty($config['socket'])
                ? sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $config['host'] ?? 'localhost', $config['port'] ?? 3306, $database, $config['charset'] ?? 'utf8mb4')
                : sprintf('mysql:unix_socket=%s;dbname=%s;charset=%s', $config['socket'], $database, $config['charset'] ?? 'utf8mb4'),
            'pgsql' => sprintf("pgsql:host=%s;port=%d;dbname=%s;options='--client_encoding=%s'", $config['host'] ?? 'localhost', $config['port'] ?? 5432, $database, $config['charset'] ?? 'UTF8'),
            'sqlite' => 'sqlite:'.$database,
            default => throw new RunException('Unsupported database driver "'.$driver.'". Use mysql, pgsql or sqlite.'),
        };
    }

    /**
     * The PDO connection, opened on first use.
     */
    public function link(): PDO
    {
        if ($this->link !== null) {
            return $this->link;
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_PERSISTENT => (bool) ($this->config['persistent'] ?? false),
        ];

        if (($this->config['driver'] ?? '') === 'mysql') {
            // Real prepared statements on the MySQL server, not emulated ones.
            $options[PDO::ATTR_EMULATE_PREPARES] = false;
        }

        try {
            $this->link = new PDO(
                self::dsn($this->config),
                $this->config['user'] ?? null,
                $this->config['password'] ?? null,
                ($this->config['options'] ?? []) + $options
            );
        } catch (PDOException $e) {
            throw new RunException('Unable to connect to database "'.$this->connection.'": '.$e->getMessage(), 0, E_ERROR, null, null, $e);
        }

        return $this->link;
    }

    /**
     * Run any SQL with bound values. Use "?" or ":name" placeholders.
     *
     *   query('UPDATE posts SET views = views + 1 WHERE id = ?', [$id])
     */
    public function query(string $sql, array $bindings = []): PDOStatement
    {
        $bindings = $this->takeBindings($sql, $bindings);
        $this->lastQuery = $sql;
        $this->lastBindings = $bindings;

        try {
            $statement = $this->link()->prepare($sql);
            $position = 0;

            foreach ($bindings as $key => $value) {
                $parameter = is_int($key) ? ++$position : ':'.ltrim($key, ':');
                $statement->bindValue($parameter, ...$this->bindable($value, (string) $key));
            }

            $statement->execute();
        } catch (PDOException $e) {
            throw new RunException('Database error: '.$e->getMessage().' | SQL: '.$sql, 0, E_ERROR, null, null, $e);
        }

        return $this->lastStatement = $statement;
    }

    /**
     * Get multiple records.
     *
     *   results('SELECT * FROM users WHERE city = ?', ['Bandung'])
     *   results($sql, 'array')   // old style, still works
     */
    public function results(?string $query = null, array|string $bindings = [], ?string $returnType = null): array|PDOStatement
    {
        if (is_string($bindings)) {
            [$returnType, $bindings] = [$bindings, []];
        }

        $statement = $this->query($query ?? $this->command(), $bindings);
        $type = $this->consumeReturnType($returnType);

        if ($type === 'iterator') {
            return $statement;
        }

        return $statement->fetchAll(...$this->fetchMode($type));
    }

    /**
     * Get single record, or false when nothing matches.
     */
    public function row(?string $query = null, array|string $bindings = [], ?string $returnType = null): object|array|false
    {
        if (is_string($bindings)) {
            [$returnType, $bindings] = [$bindings, []];
        }

        $statement = $this->query($query ?? $this->command(), $bindings);
        $statement->setFetchMode(...$this->fetchMode($this->consumeReturnType($returnType)));
        $row = $statement->fetch();
        $statement->closeCursor();

        return $row;
    }

    /**
     * Run $callback inside a transaction. Any exception rolls it back.
     *
     *   $db->transaction(function ($db) { ...two inserts... });
     */
    public function transaction(callable $callback): mixed
    {
        $this->begin();

        try {
            $result = $callback($this);
            $this->commit();

            return $result;
        } catch (\Throwable $e) {
            if ($this->link()->inTransaction()) {
                $this->rollback();
            }

            throw $e;
        }
    }

    public function begin(): bool
    {
        return $this->link()->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->link()->commit();
    }

    public function rollback(): bool
    {
        return $this->link()->rollBack();
    }

    /**
     * @deprecated Pass values as bindings instead. Kept so old code still runs.
     */
    public function escape(string $string): string
    {
        return substr($this->link()->quote($string), 1, -1);
    }

    /**
     * Get the id from last insert. PostgreSQL may need the sequence name.
     */
    public function insertId(?string $name = null): string|false
    {
        return $this->link()->lastInsertId($name);
    }

    /**
     * Rows changed by the last INSERT, UPDATE or DELETE.
     */
    public function affectedRows(): int
    {
        return $this->lastStatement?->rowCount() ?? 0;
    }

    /**
     * Get this db version.
     */
    public function version(): string
    {
        return (string) $this->link()->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /**
     * Close db connection.
     */
    public function close(): void
    {
        $this->lastStatement = null;
        $this->link = null;
    }

    /**
     * Prepares a statement for execution and returns a statement object.
     */
    public function prepare(string $query): PDOStatement
    {
        return $this->link()->prepare($query);
    }

    /**
     * @return array{0: mixed, 1: int}
     */
    private function bindable(mixed $value, string $name): array
    {
        return match (true) {
            $value === null => [null, PDO::PARAM_NULL],
            is_bool($value) => [$value, PDO::PARAM_BOOL],
            is_int($value) => [$value, PDO::PARAM_INT],
            is_float($value) => [(string) $value, PDO::PARAM_STR],
            is_string($value), $value instanceof \Stringable => [(string) $value, PDO::PARAM_STR],
            $value instanceof \DateTimeInterface => [$value->format('Y-m-d H:i:s'), PDO::PARAM_STR],
            default => throw new RunException('Value bound to "'.$name.'" must be a scalar, got '.get_debug_type($value)),
        };
    }

    private function fetchMode(string $type): array
    {
        if ($type === 'array') {
            return [PDO::FETCH_ASSOC];
        }

        return $this->instantiateClass ? [PDO::FETCH_CLASS, $this->instantiateClass] : [PDO::FETCH_OBJ];
    }
}
