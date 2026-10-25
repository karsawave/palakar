<?php

declare(strict_types=1);

namespace Drivers\Session;

use Resources\Database;

/**
 * Stores PHP sessions in a database table through PDO. Create the table first:
 *
 *   CREATE TABLE sessions (
 *       session_id VARCHAR(128) PRIMARY KEY,
 *       session_data TEXT NOT NULL,
 *       session_expiration INTEGER NOT NULL
 *   );
 */
class DatabaseHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface
{
    public function __construct(
        private Database $db,
        private string $table = 'sessions',
        private int $lifetime = 7200,
    ) {}

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        $data = $this->db->select('session_data')->from($this->table)
            ->where('session_id', '=', $id)
            ->where('session_expiration', '>', time())
            ->getVar();

        return (string) $data;
    }

    public function write(string $id, string $data): bool
    {
        $row = ['session_data' => $data, 'session_expiration' => time() + $this->lifetime];

        if ($this->exists($id)) {
            return $this->db->update($this->table, $row, ['session_id' => $id]);
        }

        return $this->db->insert($this->table, ['session_id' => $id] + $row);
    }

    public function destroy(string $id): bool
    {
        return $this->db->delete($this->table, ['session_id' => $id]);
    }

    public function gc(int $max_lifetime): int|false
    {
        $this->db->where('session_expiration', '<', time())->delete($this->table);

        return $this->db->affectedRows();
    }

    /**
     * Strict mode: only accept ids that exist and are not expired.
     */
    public function validateId(string $id): bool
    {
        return $this->db->select('session_id')->from($this->table)
            ->where('session_id', '=', $id)
            ->where('session_expiration', '>', time())
            ->getVar() !== null;
    }

    public function updateTimestamp(string $id, string $data): bool
    {
        return $this->db->update($this->table, ['session_expiration' => time() + $this->lifetime], ['session_id' => $id]);
    }

    private function exists(string $id): bool
    {
        return $this->db->select('session_id')->from($this->table)->where('session_id', '=', $id)->getVar() !== null;
    }
}
