<?php

declare(strict_types=1);

/**
 * Panada SQL query builder.
 *
 * Golden rule of this class: every VALUE travels to the database as a bound
 * parameter of a prepared statement ("?"), never glued into the SQL string.
 * That is what makes SQL injection impossible here.
 *
 * Table and column NAMES cannot be bound, so they are checked against a strict
 * pattern (letters, digits, underscore, "table.column") and rejected otherwise.
 *
 * Only select() and whereRaw() accept raw SQL. Never put user input in them.
 *
 * @author	Iskandar Soesman.
 *
 * @since	Version 1.1.0
 */

namespace Drivers\Abstraction;

use Resources\RunException;

abstract class Sql
{
    private const OPERATORS = [
        '=', '!=', '<>', '<', '>', '<=', '>=',
        'LIKE', 'NOT LIKE', 'ILIKE', 'NOT ILIKE',
        'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'IS', 'IS NOT',
    ];

    private const JOIN_TYPES = ['', 'INNER', 'LEFT', 'RIGHT', 'FULL', 'CROSS', 'LEFT OUTER', 'RIGHT OUTER', 'FULL OUTER'];

    /** name, table.name, table.* or * */
    private const IDENTIFIER = '/^(?:\*|[A-Za-z_][A-Za-z0-9_]*(?:\.(?:[A-Za-z_][A-Za-z0-9_]*|\*))?)$/';

    /** COUNT(*), SUM(price), COUNT(DISTINCT user_id) ... */
    private const AGGREGATE = '/^(?:COUNT|SUM|AVG|MIN|MAX)\(\s*(?:DISTINCT\s+)?(?:\*|[A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)?)\s*\)$/i';

    protected array $columns = ['*'];
    protected bool $distinct = false;
    protected array $tables = [];
    protected array $joins = [];
    protected array $criteria = [];
    protected array $groupBy = [];
    protected array $having = [];
    protected array $orderBy = [];
    protected ?int $limit = null;
    protected ?int $offset = null;

    /** Return data type option: object, array or iterator (the PDOStatement). */
    protected string $returnType = 'object';

    /** Fetch rows into this class instead of stdClass (used by ActiveRecord). */
    public string|false $instantiateClass = false;

    protected string $lastQuery = '';
    protected array $lastBindings = [];

    private ?string $compiledSql = null;
    private array $compiledBindings = [];

    /**
     * Run a statement with its bound values. Implemented by the driver.
     */
    abstract public function query(string $sql, array $bindings = []): \PDOStatement;

    abstract public function results(?string $query = null, array|string $bindings = [], ?string $returnType = null): array|\PDOStatement;

    abstract public function row(?string $query = null, array|string $bindings = [], ?string $returnType = null): object|array|false;

    /**
     * API for "SELECT ... " statement. Raw SQL: columns, aliases and functions.
     *
     *   select('id', 'name')   select(['id', 'name'])   select('COUNT(*) AS total')
     */
    public function select(string|array ...$columns): static
    {
        if (isset($columns[0]) && is_array($columns[0])) {
            $columns = $columns[0];
        }

        $this->columns = $columns === [] ? ['*'] : array_values($columns);

        return $this;
    }

    /**
     * API for "... DISTINCT " statement.
     */
    public function distinct(): static
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * API for "... FROM ... " statement. Accepts aliases: from('users u').
     */
    public function from(string|array ...$tables): static
    {
        if (isset($tables[0]) && is_array($tables[0])) {
            $tables = $tables[0];
        }

        $this->tables = array_map(fn(string $table) => $this->table($table), $tables);

        return $this;
    }

    /**
     * API for "... JOIN ..." statement. Call on() right after it.
     *
     * @param string|null $type LEFT, RIGHT, INNER, ...
     */
    public function join(string $table, ?string $type = null): static
    {
        $type = strtoupper(trim((string) $type));

        if (!in_array($type, self::JOIN_TYPES, true)) {
            throw new RunException('Invalid join type: ' . $type);
        }

        $this->joins[] = ['type' => $type, 'table' => $this->table($table), 'on' => []];

        return $this;
    }

    /**
     * API for "... JOIN ... ON ..." statement. Compares two columns.
     *
     *   ->join('posts p', 'LEFT')->on('p.user_id', '=', 'u.id')
     */
    public function on(string $column, string $operator, string $value, string|false $separator = false): static
    {
        if ($this->joins === []) {
            throw new RunException('Call join() before on()');
        }

        $operator = $this->operator($operator);
        $this->joins[array_key_last($this->joins)]['on'][] = [
            $this->column($column) . ' ' . $operator . ' ' . $this->column($value),
            $this->separator($separator),
            [],
        ];

        return $this;
    }

    /**
     * API for "... WHERE ... " statement. The value is always bound.
     *
     *   where('email', '=', $email)
     *   where('status', 'IN', ['draft', 'published'])
     *   where('price', 'BETWEEN', [10, 50])
     *   where('deleted_at', '=', null)            // deleted_at IS NULL
     *   where('a', '=', 1, 'OR')->where('b', '=', 2)
     *
     * @param string|false $separator AND / OR placed after this condition
     */
    public function where(string $column, string $operator, mixed $value, string|false $separator = false): static
    {
        [$sql, $bindings] = $this->condition($column, $operator, $value);
        $this->criteria[] = [$sql, $this->separator($separator), $bindings];

        return $this;
    }

    /**
     * A hand written condition, for what where() cannot express.
     * Use "?" for values and pass them in $bindings.
     *
     *   whereRaw('(status = ? OR author_id = ?)', ['published', $userId])
     */
    public function whereRaw(string $sql, array $bindings = [], string|false $separator = false): static
    {
        $this->criteria[] = [$sql, $this->separator($separator), array_values($bindings)];

        return $this;
    }

    /**
     * API for "... GROUP BY ... " statement.
     */
    public function groupBy(string ...$columns): static
    {
        foreach ($columns as $column) {
            $this->groupBy[] = $this->column($column);
        }

        return $this;
    }

    /**
     * API for "... HAVING..." statement. having('COUNT(id)', '>', 5)
     */
    public function having(string $column, string $operator, mixed $value, string|false $separator = false): static
    {
        [$sql, $bindings] = $this->condition($column, $operator, $value);
        $this->having[] = [$sql, $this->separator($separator), $bindings];

        return $this;
    }

    /**
     * API for "... ORDER BY..." statement. Safe to feed with a sort parameter
     * from the URL: anything that is not a plain column name is rejected.
     *
     *   orderBy('created_at', 'DESC')      orderBy('name ASC, id DESC')
     */
    public function orderBy(string $column, ?string $order = null): static
    {
        foreach (explode(',', $column) as $part) {
            if (!preg_match('/^\s*(\S+)(?:\s+(ASC|DESC))?\s*$/i', $part, $match)) {
                throw new RunException('Invalid ORDER BY: ' . $part);
            }

            $direction = strtoupper($match[2] ?? '') ?: strtoupper(trim((string) $order));

            if (!in_array($direction, ['', 'ASC', 'DESC'], true)) {
                throw new RunException('Invalid ORDER BY direction: ' . $direction);
            }

            $this->orderBy[] = trim($this->column($match[1]) . ' ' . $direction);
        }

        return $this;
    }

    /**
     * API for "... LIMIT ... OFFSET ..." statement.
     */
    public function limit(int $limit, ?int $offset = null): static
    {
        $this->limit = max(0, $limit);
        $this->offset = $offset === null ? null : max(0, $offset);

        return $this;
    }

    /**
     * Build the SELECT statement and return its SQL (with "?" placeholders).
     * The values are kept and used by the next query() with the same SQL.
     */
    public function command(): string
    {
        [$this->compiledSql, $this->compiledBindings] = $this->compileSelect();

        return $this->compiledSql;
    }

    /**
     * Flag to get return type. The options is: object, array and iterator.
     */
    public function fetchAs(string $returnType = 'object'): static
    {
        if (!in_array($returnType, ['object', 'array', 'iterator'], true)) {
            throw new RunException('fetchAs() accepts object, array or iterator');
        }

        $this->returnType = $returnType;

        return $this;
    }

    /**
     * Get many rows.
     *
     *   getAll('users', ['status' => 'active'], ['id', 'name'])
     *   select('id')->from('users')->where('id', '>', 10)->getAll()
     */
    public function getAll(string|false|null $table = null, array $where = [], array|string $fields = [], string|false|null $returnType = null): array|\PDOStatement
    {
        if ($returnType) {
            $this->fetchAs($returnType);
        }

        if ($table) {
            $this->select($fields ?: '*')->from($table);

            foreach ($where as $column => $value) {
                $this->where((string) $column, '=', $value, 'AND');
            }
        }

        return $this->results();
    }

    /**
     * Get one row (or false).
     */
    public function getOne(string|false|null $table = null, array $where = [], array|string $fields = [], string|false|null $returnType = null): object|array|false
    {
        if ($returnType) {
            $this->fetchAs($returnType);
        }

        if ($table) {
            $this->select($fields ?: '*')->from($table);

            foreach ($where as $column => $value) {
                $this->where((string) $column, '=', $value, 'AND');
            }
        }

        if ($this->limit === null && $this->tables !== []) {
            $this->limit(1);
        }

        return $this->row();
    }

    /**
     * Value of the first column of the first row, or null.
     *
     *   select('COUNT(*)')->from('users')->getVar()
     */
    public function getVar(?string $query = null, array $bindings = []): mixed
    {
        $row = $this->query($query ?? $this->command(), $bindings)->fetch(\PDO::FETCH_NUM);

        return $row === false ? null : $row[0];
    }

    /**
     * INSERT INTO table (a, b) VALUES (?, ?)
     */
    public function insert(string $table, array $data = []): bool
    {
        if ($data === []) {
            throw new RunException('insert() needs at least one column');
        }

        $columns = array_map(fn($column) => $this->column((string) $column, false), array_keys($data));

        $this->query(
            'INSERT INTO ' . $this->table($table, false) . ' (' . implode(', ', $columns) . ') VALUES (' . $this->placeholders(count($data)) . ')',
            array_values($data),
        );

        return true;
    }

    /**
     * UPDATE table SET a = ? WHERE ...
     *
     *   update('users', ['name' => $name], ['id' => $id])
     *   where('id', '=', $id)->update('users', ['name' => $name])
     */
    public function update(string $table, array $data, ?array $where = null): bool
    {
        if ($data === []) {
            throw new RunException('update() needs at least one column');
        }

        $set = [];

        foreach (array_keys($data) as $column) {
            $set[] = $this->column((string) $column, false) . ' = ?';
        }

        [$whereSql, $whereBindings] = $this->writeCondition('update', $where);

        $this->query(
            'UPDATE ' . $this->table($table, false) . ' SET ' . implode(', ', $set) . ' WHERE ' . $whereSql,
            [...array_values($data), ...$whereBindings],
        );

        return true;
    }

    /**
     * DELETE FROM table WHERE ...
     */
    public function delete(string $table, ?array $where = null): bool
    {
        [$whereSql, $whereBindings] = $this->writeCondition('delete', $where);

        $this->query('DELETE FROM ' . $this->table($table, false) . ' WHERE ' . $whereSql, $whereBindings);

        return true;
    }

    /**
     * Get last query (with "?" placeholders).
     */
    public function getLastQuery(): string
    {
        return $this->lastQuery;
    }

    /**
     * Values bound to the last query.
     */
    public function getLastBindings(): array
    {
        return $this->lastBindings;
    }

    /**
     * @deprecated Errors are always thrown as RunException since PHP 8 support.
     */
    public function setThrowError(bool $set = false): void {}

    /**
     * Turns the SELECT parts into [sql, bindings] and empties the builder.
     */
    protected function compileSelect(): array
    {
        $sql = 'SELECT ' . ($this->distinct ? 'DISTINCT ' : '') . implode(', ', $this->columns);
        $bindings = [];

        if ($this->tables !== []) {
            $sql .= ' FROM ' . implode(', ', $this->tables);
        }

        foreach ($this->joins as $join) {
            $sql .= ' ' . ltrim($join['type'] . ' JOIN ') . $join['table'];

            if ($join['on'] !== []) {
                $sql .= ' ON (' . $this->conditions($join['on'])[0] . ')';
            }
        }

        if ($this->criteria !== []) {
            [$where, $bindings] = $this->conditions($this->criteria);
            $sql .= ' WHERE ' . $where;
        }

        if ($this->groupBy !== []) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }

        if ($this->having !== []) {
            [$having, $havingBindings] = $this->conditions($this->having);
            $sql .= ' HAVING ' . $having;
            $bindings = [...$bindings, ...$havingBindings];
        }

        if ($this->orderBy !== []) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit . ($this->offset !== null ? ' OFFSET ' . $this->offset : '');
        }

        $this->reset();

        return [$sql, $bindings];
    }

    /**
     * When query() receives the SQL just built by command(), reuse its values.
     */
    protected function takeBindings(string $sql, array $bindings): array
    {
        if ($bindings === [] && $sql === $this->compiledSql) {
            $bindings = $this->compiledBindings;
        }

        $this->compiledSql = null;
        $this->compiledBindings = [];

        return $bindings;
    }

    /**
     * Read the requested return type once, then go back to the default.
     */
    protected function consumeReturnType(?string $returnType): string
    {
        if ($returnType) {
            $this->fetchAs($returnType);
        }

        $type = $this->returnType;
        $this->returnType = 'object';

        return $type;
    }

    protected function reset(): void
    {
        $this->columns = ['*'];
        $this->distinct = false;
        $this->tables = $this->joins = $this->criteria = $this->groupBy = $this->having = $this->orderBy = [];
        $this->limit = $this->offset = null;
    }

    /**
     * @return array{0: string, 1: array}
     */
    private function condition(string $column, string $operator, mixed $value): array
    {
        $column = $this->column($column);
        $operator = $this->operator($operator);

        // Old Panada code wrote where('col', 'IS', 'NULL').
        if (($operator === 'IS' || $operator === 'IS NOT') && is_string($value) && strtoupper($value) === 'NULL') {
            $value = null;
        }

        if ($value === null) {
            return match ($operator) {
                '=', 'IS' => [$column . ' IS NULL', []],
                '!=', '<>', 'IS NOT' => [$column . ' IS NOT NULL', []],
                default => throw new RunException('NULL can only be used with =, !=, IS or IS NOT'),
            };
        }

        if ($operator === 'IN' || $operator === 'NOT IN') {
            $values = array_values(is_array($value) ? $value : [$value]);

            if ($values === []) {
                return [$operator === 'IN' ? '1 = 0' : '1 = 1', []];
            }

            return [$column . ' ' . $operator . ' (' . $this->placeholders(count($values)) . ')', $values];
        }

        if ($operator === 'BETWEEN' || $operator === 'NOT BETWEEN') {
            if (!is_array($value) || count($value) !== 2) {
                throw new RunException($operator . ' needs an array with two values');
            }

            return [$column . ' ' . $operator . ' ? AND ?', array_values($value)];
        }

        if (is_array($value)) {
            throw new RunException('Operator ' . $operator . ' expects one value, array given');
        }

        return [$column . ' ' . $operator . ' ?', [$value]];
    }

    /**
     * Join conditions with their AND / OR separators.
     *
     * @return array{0: string, 1: array}
     */
    private function conditions(array $conditions): array
    {
        $sql = '';
        $bindings = [];

        foreach (array_values($conditions) as $i => [$condition, $separator, $values]) {
            $sql .= $i === 0 ? $condition : ' ' . $conditions[$i - 1][1] . ' ' . $condition;
            array_push($bindings, ...$values);
        }

        return [$sql, $bindings];
    }

    /**
     * WHERE part for update() and delete(): conditions from where(), or the
     * $where array. Refuses to touch the whole table by accident.
     *
     * @return array{0: string, 1: array}
     */
    private function writeCondition(string $action, ?array $where): array
    {
        if ($this->criteria === [] && $where) {
            foreach ($where as $column => $value) {
                $this->where((string) $column, '=', $value, 'AND');
            }
        }

        if ($this->criteria === []) {
            throw new RunException($action . '() without a WHERE condition is refused. Use whereRaw(\'1 = 1\') if you really mean every row.');
        }

        $result = $this->conditions($this->criteria);
        $this->reset();

        return $result;
    }

    private function operator(string $operator): string
    {
        $operator = strtoupper(trim(preg_replace('/\s+/', ' ', $operator)));

        if (!in_array($operator, self::OPERATORS, true)) {
            throw new RunException('Invalid SQL operator: ' . $operator);
        }

        return $operator;
    }

    private function separator(string|false $separator): string
    {
        $separator = $separator === false ? 'AND' : strtoupper(trim($separator));

        if ($separator !== 'AND' && $separator !== 'OR') {
            throw new RunException('Invalid separator "' . $separator . '", use AND or OR');
        }

        return $separator;
    }

    /**
     * A column name: name, table.name, * or an aggregate like COUNT(id).
     */
    private function column(string $column, bool $allowAggregate = true): string
    {
        $column = trim($column);

        if (preg_match(self::IDENTIFIER, $column) || ($allowAggregate && preg_match(self::AGGREGATE, $column))) {
            return $column;
        }

        throw new RunException('Invalid column name: ' . $column);
    }

    /**
     * A table name with an optional alias: "users", "users u", "users AS u".
     */
    private function table(string $table, bool $allowAlias = true): string
    {
        $table = trim(preg_replace('/\s+/', ' ', $table));
        $pattern = $allowAlias
            ? '/^[A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)?(?: (?:AS )?[A-Za-z_][A-Za-z0-9_]*)?$/i'
            : '/^[A-Za-z_][A-Za-z0-9_]*(?:\.[A-Za-z_][A-Za-z0-9_]*)?$/';

        if (!preg_match($pattern, $table)) {
            throw new RunException('Invalid table name: ' . $table);
        }

        return $table;
    }

    private function placeholders(int $count): string
    {
        return implode(', ', array_fill(0, $count, '?'));
    }
}
