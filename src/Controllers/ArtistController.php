<?php
namespace Controllers;

require_once __DIR__ . '/../Models/BaseModel.php';
use Models\BaseModel;
use Utilities\Response;

class ArtistController extends BaseModel {
    # Checks if an artist exists by ID
    # This method is used internally to verify if an artist exists before performing operations like get, update, or delete.
    private function artistExists($id) {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM Artist WHERE ArtistId = :id');
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
    # Retrieves all artists
    public function getAll() {
        try {
            $stmt = $this->db->query('SELECT * FROM Artist');
            $artists = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            Response::send($artists);
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }
    # Retrieves an artist by ID
    public function getById($id) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM Artist WHERE ArtistId = :id');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $artist = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($artist) {
                Response::send($artist);
            } else {
                Response::error('Artist not found', 404);
            }
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }
    # Retrives all artists whose names match the search term
    public function getBySearch($search) {
        $search = htmlspecialchars($search);
        try {
            $stmt = $this->db->prepare('SELECT * FROM Artist WHERE Name LIKE :search');
            $searchParam = '%' . $search . '%';
            $stmt->bindParam(':search', $searchParam, \PDO::PARAM_STR);
            $stmt->execute();
            $artists = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            Response::send($artists);
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }
    # Retrieves all albums by artist ID
    public function getAlbumsByArtistId($artistId) {
        try {
            if (!$this->artistExists($artistId)) {
                Response::error('Artist not found', 404);
                return;
            }
            $stmt = $this->db->prepare('SELECT * FROM Album WHERE ArtistId = :artistId');
            $stmt->bindParam(':artistId', $artistId, \PDO::PARAM_INT);
            $stmt->execute();
            $albums = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($albums) {
                Response::send($albums);
            } else {
                Response::error('No albums found for this artist', 404);
            }
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }
    # Creates a new artist
    public function createArtist($data) {
        if (empty($data['Name'])) {
            Response::error('Artist name is required', 400);
            return;
        }
        $data['Name'] = htmlspecialchars($data['Name']);
        if (strlen($data['Name']) > 120) {
            Response::error('Artist name must be less than 120 characters', 400);
            return;
        }
        try {
            $stmt = $this->db->prepare('INSERT INTO Artist (Name) VALUES (:name)');
            $stmt->bindParam(':name', $data['Name'], \PDO::PARAM_STR);
            if ($stmt->execute()) {
                Response::send(['message' => 'Artist with name: '.$data['Name'].' created successfully'], 200);
            } else {
                Response::error('Failed to create artist', 500);
            }
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }
    # Deletes an artist by ID
    public function deleteArtist($id) {
        try {
            if (!$this->artistExists($id)) {
                Response::error('Artist not found', 404);
                return;
            }
            $stmt = $this->db->prepare('DELETE FROM Artist WHERE ArtistId = :id');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                Response::send(['message' => 'Artist deleted successfully'], 200);
            } else {
                Response::error('Failed to delete artist', 500);
            }
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }
    # Updates an artist by ID
    public function updateArtist($id, $data) {
        if (empty($data['Name'])) {
            Response::error('Artist name is required', 400);
            return;
        }
        $data['Name'] = htmlspecialchars($data['Name']);
        if (strlen($data['Name']) > 120) {
            Response::error('Artist name must be less than 120 characters', 400);
            return;
        }
        try {
            if (!$this->artistExists($id)) {
                Response::error('Artist not found', 404);
                return;
            }
            $stmt = $this->db->prepare('UPDATE Artist SET Name = :name WHERE ArtistId = :id');
            $stmt->bindParam(':name', $data['Name'], \PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                Response::send(['message' => 'Artist updated successfully'], 200);
            } else {
                Response::error('Failed to update artist', 500);
            }
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }
}
?>