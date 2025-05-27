<?php
namespace Controllers;
require_once __DIR__ . '/../Models/BaseModel.php';
require_once __DIR__ . '/../Utilities/Formatter.php';
use Utilities\Formatter;
use Models\BaseModel;
use Utilities\Response;

class AlbumController extends BaseModel {

    # Retrieves all albums with their associated artist names
    public function getAll() {
        try {
            $stmt = $this->db->query('SELECT Album.*, Artist.Name AS ArtistName FROM Album '
                .'JOIN Artist ON Album.ArtistId = Artist.ArtistId');
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $albums = [];
            foreach ($result as $album) {
                $albums[] = Formatter::formatAlbum($album);
            }
            Response::send($albums);
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    # Retrieves a single album by its ID, including the artist name
    public function getById($id) {
        try {
            $stmt = $this->db->prepare('SELECT Album.*, Artist.Name AS ArtistName FROM Album '
                .'JOIN Artist ON Album.ArtistId = Artist.ArtistId WHERE Album.AlbumId = :id');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                $album = Formatter::formatAlbum($result);
                Response::send($album);
            } else {
                Response::error('Album not found', 404);
            }
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    # Retrieves all tracks in an ablum by its ID, including media type and genre
    public function getTracksByAlbumId($albumId) {
        try {
            $stmt = $this->db->prepare('SELECT Track.*, MediaType.Name AS MediaTypeName, Genre.Name AS GenreName '
                .'FROM Track JOIN MediaType ON Track.MediaTypeId = MediaType.MediaTypeId JOIN Genre ON '
                .'Track.GenreId = Genre.GenreId WHERE Track.AlbumId = :albumId');
            $stmt->bindParam(':albumId', $albumId, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($result) {
                $tracks = [];
                foreach ($result as $track) {
                    $tracks[] = Formatter::formatTrack($track);
                }
                Response::send($tracks);
            } else {
                Response::error('No tracks found for this album', 404);
            }
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    # Retrieves albums based on a search term in the title, including artist names
    public function getAlbumBySearch($search) {
        $search = htmlspecialchars($search);
        try {
            $stmt = $this->db->prepare('SELECT Album.*, Artist.Name AS ArtistName FROM Album JOIN Artist ON Album.ArtistId = Artist.ArtistId WHERE Album.Title LIKE :search');
            $searchParam = '%' . $search . '%';
            $stmt->bindParam(':search', $searchParam, \PDO::PARAM_STR);
            $stmt->execute();
            $albums = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($albums) {
                Response::send($albums);
            } else {
                Response::error('No albums found for this search', 404);
            }
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    # Creates an album with title and artist ID
    public function createAlbum($data) {
        if (empty($data['Title']) || empty($data['ArtistId'])) {
            Response::error('Title and ArtistId are required', 400);
            return;
        }
        $data['Title'] = htmlspecialchars($data['Title']);
        if (!is_int($data['ArtistId'])) {
            Response::error('ArtistId must be a number', 400);
            return;
        }
        try {
            $stmt = $this->db->prepare('INSERT INTO Album (Title, ArtistId) VALUES (:title, :artistId)');
            $stmt->bindParam(':title', $data['Title'], \PDO::PARAM_STR);
            $stmt->bindParam(':artistId', $data['ArtistId'], \PDO::PARAM_INT);
            if ($stmt->execute()) {
                Response::send(['message' => 'Album created successfully']);
            } else {
                Response::error('Failed to create album', 500);
            }
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    # Deletes an album by its ID
    public function deleteAlbum($id) {
        try {
            $stmt = $this->db->prepare('DELETE FROM Album WHERE AlbumId = :id');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                Response::send(['message' => 'Album deleted successfully']);
            } else {
                Response::error('Failed to delete album', 500);
            }
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    public function updateAlbum($id, $data) {
        if (!empty($data['Title'])) {
            $data['Title'] = htmlspecialchars($data['Title']);
        }
        if (!empty($data['ArtistId']) && !is_int($data['ArtistId'])) {
            Response::error('ArtistId must be a number', 400);
            return;
        }
        if (empty($data['Title']) && empty($data['ArtistId'])) {
            Response::error('At least one field (Title or ArtistId) must be provided for update', 400);
            return;
        }
        try {
            $stmt = $this->db->prepare('UPDATE Album SET Title = IFNULL(:title, Title), ArtistId = IFNULL(:artistId, ArtistId) WHERE AlbumId = :id');
            $stmt->bindParam(':title', $data['Title'], \PDO::PARAM_STR);
            $stmt->bindParam(':artistId', $data['ArtistId'], \PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                Response::send(['message' => 'Album updated successfully']);
            } else {
                Response::error('Failed to update album', 500);
            }
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    
            
}