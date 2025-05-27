<?php

namespace Controllers;
require_once __DIR__ . '/../Models/BaseModel.php';
use Models\BaseModel;
use Utilities\Response;

class MediaTypesController extends BaseModel {
    public function getAll() {
        try {
            $stmt = $this->db->query('SELECT * FROM MediaType');
            $mediaTypes = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            \Utilities\Response::send($mediaTypes);
        } catch (\PDOException $e) {
            \Utilities\Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }
}