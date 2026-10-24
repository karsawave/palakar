<?php

declare(strict_types=1);

/**
 * Panada Active Record API.
 *
 * @link	http://panadaframework.com/
 *
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.3
 */

namespace Resources;

/**
 * One table as a class. Columns become properties of the object, so dynamic
 * properties are allowed here on purpose.
 *
 *   namespace Models;
 *
 *   class Users extends \Resources\ActiveRecord
 *   {
 *       protected $table = 'users';      // default: the class name in lower case
 *   }
 *
 *   $user = new \Models\Users();
 *   $user->name = 'Budi';
 *   $user->save();                       // INSERT, then $user->id is set
 *   $user->name = 'Budi Santoso';
 *   $user->save();                       // UPDATE users SET ... WHERE id = ?
 *
 *   $user->condition('score', '>', 10)->order('score', 'DESC')->limit(5)->get();
 *   $user->getByEmail($email);
 *
 * Every value goes through the query builder, so it is always bound.
 *
 * $table, $connection, $primaryKey and the public methods have no declared
 * types: models redeclare them without types, which PHP refuses when the
 * parent declares one.
 */
#[\AllowDynamicProperties]
class ActiveRecord
{
    // Define the constants for db relations.
    const
        BELONGS_TO = 1,
        HAS_ONE = 2,
        HAS_MANY = 3,
        MANY_MANY = 4;

    protected Database $db;
    protected $table;
    protected $connection = 'default';

    /** Class the rows are fetched into, so relations also work on them. */
    protected $setInstantiateClass = false;

    public $primaryKey = 'id';

    /** Values passed to save() or the constructor, instead of the properties. */
    private array $fields = [];

    // Parts of the next query. They are emptied as soon as it runs.
    private array $condition = [];
    private array $from = [];
    private string|array $select = '*';
    private ?string $orderBy = null;
    private ?string $order = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private array $groupBy = [];

    private string $modulPrefix = '';

    /**
     *   new Users()                       'default' connection
     *   new Users('pg')                   another connection
     *   new Users(['name' => 'Budi'])     INSERT right away (UPDATE when the primary key is given)
     */
    public function __construct()
    {
        $args = func_get_args();
        $data = [];

        if (!empty($args[0])) {
            if (is_array($args[0])) {
                $data = $args[0];
            } else {
                $this->connection = $args[0];
            }

            if (isset($args[1])) {
                $this->connection = $args[1];
            }
        }

        $class = explode('\\', static::class);
        $this->table ??= strtolower(end($class));
        $this->db = new Database($this->connection);

        if ($class[0] === 'Modules') {
            $this->modulPrefix = $class[0].'\\'.$class[1].'\\';
        }

        if ($this->setInstantiateClass === false && $this->relations()) {
            $this->setInstantiateClass = static::class;
        }

        if ($data !== []) {
            if (isset($data[$this->primaryKey])) {
                $this->{$this->primaryKey} = $data[$this->primaryKey];
            }

            $this->save($data);
        }
    }

    /**
     * INSERT a new record, or UPDATE it when the primary key is known.
     *
     * @return mixed the new id after an INSERT, true after an UPDATE
     */
    public function save($data = false)
    {
        if (is_array($data) && $data !== []) {
            $this->fields = $data;
        }

        $fields = $this->getFields();
        $pk = $this->primaryKey;

        if (isset($this->$pk)) {
            return $this->db->update($this->table, $fields, [$pk => $this->$pk]);
        }

        $this->db->insert($this->table, $fields);

        // Remember the id, so the next save() updates this same record.
        return $this->$pk = $this->db->insertId();
    }

    /**
     * get()          every row matching condition(), order(), limit() ...
     * get(5)         one row by primary key, also copied into this object
     * get(1, 2, 3)   rows whose primary key is in the list
     *
     * @return mixed array of rows, or one row / false for a single id
     */
    public function get()
    {
        $ids = func_get_args();
        $db = $this->buildQuery();

        if (count($ids) === 1) {
            $row = $db->where($this->primaryKey, '=', $ids[0])->getOne();

            if ($row) {
                $this->fill($row);
            }

            return $row;
        }

        if ($ids !== []) {
            $db->where($this->primaryKey, 'IN', $ids);
        }

        return $db->getAll();
    }

    /**
     * Delete by condition(), by an id or array of columns, or the loaded record.
     */
    public function delete($args = null)
    {
        return $this->db->delete($this->table, $this->writeWhere($args));
    }

    /**
     * Update by condition(), by an id or array of columns, or the loaded
     * record, with the values of the properties (or of save()'s fields).
     */
    public function update($args = null)
    {
        $where = $this->writeWhere($args);

        return $this->db->update($this->table, $this->getFields(), $where);
    }

    /**
     * condition('score', '>', 10)->condition('city', '=', 'Bandung')
     *
     * @param string|false $separator AND / OR placed after this condition
     */
    public function condition(string $column, string $operator, mixed $value, string|false $separator = false)
    {
        $this->condition[] = [$column, $operator, $value, $separator];

        return $this;
    }

    /**
     * Compare two columns (values are always bound, so condition() cannot).
     */
    public function conditionColumns(string $left, string $right)
    {
        foreach ([$left, $right] as $column) {
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $column)) {
                throw new RunException('Invalid column name: '.$column);
            }
        }

        $this->condition[] = [$left.' = '.$right];

        return $this;
    }

    /**
     * Extra tables for the next query, next to this model's table.
     */
    public function from(string|array $tables)
    {
        $this->from = [...$this->from, ...(array) $tables];

        return $this;
    }

    /**
     * @param string|null $order ASC | DESC
     */
    public function order(string $column, ?string $order = null)
    {
        $this->orderBy = $column;
        $this->order = $order;

        return $this;
    }

    public function select(string ...$columns)
    {
        $this->select = $columns === [] ? '*' : $columns;

        return $this;
    }

    public function limit(int $limit, ?int $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;

        return $this;
    }

    public function group(string ...$columns)
    {
        $this->groupBy = $columns;

        return $this;
    }

    /**
     * first(), last() and getByColumn($value).
     *
     * getBy...() returns false, one row (also copied into this object) or an
     * array of rows.
     */
    public function __call(string $name, array $arguments)
    {
        if ($name === 'first' || $name === 'last') {
            return $this->buildQuery()->orderBy($this->primaryKey, $name === 'first' ? 'ASC' : 'DESC')->limit(1)->getOne();
        }

        if (!str_starts_with($name, 'getBy') || $name === 'getBy') {
            throw new RunException('Call to undefined method '.static::class.'::'.$name.'()');
        }

        if ($arguments === []) {
            throw new RunException($name.'() in Active Record expects 1 parameter, none given.');
        }

        if (!$results = $this->buildQuery()->where(substr($name, 5), '=', $arguments[0])->getAll()) {
            return false;
        }

        if (count($results) === 1) {
            $this->fill($results[0]);

            return $results[0];
        }

        return $results;
    }

    /**
     * Override it in your model to declare relations, e.g.
     *
     *   'posts'   => [self::HAS_MANY, 'post', 'user_id'],
     *   'profile' => [self::HAS_ONE, 'profile', 'user_id'],
     *   'user'    => [self::BELONGS_TO, 'user', 'user_id'],
     *   'tags'    => [self::MANY_MANY, 'tag', ['post_tag', 'tag_id', 'post_id']],
     *
     * The second value is the model (Models\Post), the third the key(s).
     */
    public function relations()
    {
        return false;
    }

    /**
     * Lazy relations: $user->posts->get(), $post->user->name ...
     * Unknown properties are false, like in Panada 1.
     */
    public function __get($name)
    {
        $relation = ($this->relations() ?: [])[$name] ?? null;

        if ($relation === null) {
            return false;
        }

        $class = $this->modulPrefix.'Models\\'.ucwords($relation[1]);
        $related = new $class();
        $pk = $this->primaryKey;

        return match ($relation[0]) {
            self::BELONGS_TO => $related->limit(1)->{'getBy'.$related->primaryKey}($this->{$relation[2]}),
            self::HAS_ONE => $related->limit(1)->{'getBy'.$relation[2]}($this->$pk),
            self::HAS_MANY => $related->condition($relation[2], '=', $this->$pk, 'AND'),
            self::MANY_MANY => $related->select($related->table.'.*')
                ->from($relation[2][0])
                ->condition($relation[2][0].'.'.$relation[2][2], '=', $this->$pk, 'AND')
                ->conditionColumns($relation[2][0].'.'.$relation[2][1], $related->table.'.'.$related->primaryKey),
            default => throw new RunException('Unknown relation type for '.static::class.'::$'.$name),
        };
    }

    /**
     * Hand the parts of the next query to the query builder, then forget them
     * so they never leak into the following query.
     */
    private function buildQuery(): Database
    {
        $this->db->select($this->select)->from([$this->table, ...$this->from]);
        $this->db->instantiateClass = $this->setInstantiateClass;
        $this->applyConditions();

        if ($this->groupBy !== []) {
            $this->db->groupBy(...$this->groupBy);
        }

        if ($this->orderBy !== null) {
            $this->db->orderBy($this->orderBy, $this->order);
        }

        if ($this->limit !== null) {
            $this->db->limit($this->limit, $this->offset);
        }

        $this->select = '*';
        $this->from = $this->groupBy = [];
        $this->orderBy = $this->order = null;
        $this->limit = $this->offset = null;

        return $this->db;
    }

    private function applyConditions(): void
    {
        foreach ($this->condition as $condition) {
            if (count($condition) === 1) {
                $this->db->whereRaw($condition[0]);
                continue;
            }

            $this->db->where(...$condition);
        }

        $this->condition = [];
    }

    /**
     * WHERE of update()/delete(): condition() first, then $args (an id or an
     * array of columns), then the primary key of the loaded record. With none
     * of them the query builder refuses to touch the whole table.
     */
    private function writeWhere($args): ?array
    {
        if ($this->condition !== []) {
            $this->applyConditions();

            return null;
        }

        // Not "??": without __isset() PHP would ask __get(), which says false.
        $pk = $this->primaryKey;
        $args ??= isset($this->$pk) ? $this->$pk : null;

        return match (true) {
            $args === null => null,
            is_array($args) => $args,
            default => [$pk => $args],
        };
    }

    /**
     * Values to write: the array given to save(), or the column properties.
     */
    private function getFields(): array
    {
        $fields = $this->fields ?: self::columns($this);
        $this->fields = [];

        return $fields;
    }

    private function fill(object $row): void
    {
        foreach (self::columns($row) as $column => $value) {
            $this->$column = $value;
        }
    }

    /**
     * The column values of a record: its properties, minus the ones this
     * class declares for itself.
     */
    private static function columns(object $record): array
    {
        return array_diff_key(get_object_vars($record), get_class_vars(self::class));
    }
}
