<?php

namespace Controllers;

require_once __DIR__ . '/../Models/BaseModel.php';
use Models\BaseModel;
use Utilities\Response;

class GenreController extends BaseModel {

    public function getAll() {
        try {
            $stmt = $this->db->query('SELECT * FROM Genre');
            $genres = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            Response::send($genres);
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }

    }
}

