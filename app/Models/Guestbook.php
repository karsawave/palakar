<?php

namespace Models;

use Resources\Database;
use Resources\Model;

class Guestbook extends Model
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();

        // Demo only: create the table on the fly (SQLite syntax).
        $this->db->query('CREATE TABLE IF NOT EXISTS guestbook (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            message TEXT NOT NULL,
            created_at TEXT NOT NULL
        )');
    }

    public function add(array $entry): void
    {
        // Values are bound by PDO; quotes in a message cannot break the SQL.
        $this->db->insert('guestbook', [
            'name' => $entry['name'],
            'email' => $entry['email'],
            'message' => $entry['message'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function latest(int $limit = 20): array
    {
        return $this->db->select('name', 'message', 'created_at')
            ->from('guestbook')
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->getAll();
    }
}
