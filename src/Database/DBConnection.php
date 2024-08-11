<?php

namespace OpenCartImporter\Database;

use PDO;
use PDOException;

class DBConnection {
    private PDO $connection;

    public function __construct(array $config) {
        try {
            $this->connection = new PDO($config['dsn'], $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection(): PDO {
        return $this->connection;
    }
}