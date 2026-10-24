<?php

declare(strict_types=1);

/**
 * Public API of the database driver. Read this file to see everything
 * $this->resource->database() can do.
 *
 * @author Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @link http://panadaframework.com/
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 *
 * @since version 1.0.0
 */

namespace Resources\Interfaces;

interface Database
{
    // Query builder
    public function select(string|array ...$columns): static;
    public function distinct(): static;
    public function from(string|array ...$tables): static;
    public function join(string $table, ?string $type = null): static;
    public function on(string $column, string $operator, string $value, string|false $separator = false): static;
    public function where(string $column, string $operator, mixed $value, string|false $separator = false): static;
    public function whereRaw(string $sql, array $bindings = [], string|false $separator = false): static;
    public function groupBy(string ...$columns): static;
    public function having(string $column, string $operator, mixed $value, string|false $separator = false): static;
    public function orderBy(string $column, ?string $order = null): static;
    public function limit(int $limit, ?int $offset = null): static;
    public function fetchAs(string $returnType = 'object'): static;
    public function command(): string;

    // Reading
    public function query(string $sql, array $bindings = []): \PDOStatement;
    public function getAll(string|false|null $table = null, array $where = [], array|string $fields = [], string|false|null $returnType = null): array|\PDOStatement;
    public function getOne(string|false|null $table = null, array $where = [], array|string $fields = [], string|false|null $returnType = null): object|array|false;
    public function getVar(?string $query = null, array $bindings = []): mixed;
    public function results(?string $query = null, array|string $bindings = [], ?string $returnType = null): array|\PDOStatement;
    public function row(?string $query = null, array|string $bindings = [], ?string $returnType = null): object|array|false;

    // Writing
    public function insert(string $table, array $data = []): bool;
    public function insertId(?string $name = null): string|false;
    public function update(string $table, array $data, ?array $where = null): bool;
    public function delete(string $table, ?array $where = null): bool;
    public function affectedRows(): int;

    // Transactions & connection
    public function transaction(callable $callback): mixed;
    public function begin(): bool;
    public function commit(): bool;
    public function rollback(): bool;
    public function escape(string $string): string;
    public function version(): string;
    public function close(): void;
}
