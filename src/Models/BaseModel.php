<?php
namespace Models;

use PDO;
use Utilities\Response;

class BaseModel {
    protected $db;

    public function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        try {
            $this->db = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
                $config['user'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\PDOException $e) {
            Response::error('Database connection failed: ' . $e->getMessage(), 500);
        }
    }
}
?>