<?php

namespace Controllers;
require_once __DIR__ . '/../Models/BaseModel.php';
require_once __DIR__ . '/../Utilities/Formatter.php';
use Models\BaseModel;
use Utilities\Response;
use Utilities\Formatter;

class TracksController extends BaseModel {

    # Retrieves all tracks
    public function getAll() {
        try {
            $stmt = $this->db->query('SELECT * FROM Track');
            $tracks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            \Utilities\Response::send($tracks);
        } catch (\PDOException $e) {
            \Utilities\Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    # Retrieves track whose name includes the search text, including their media type and genre
    public function getBySearch($search) {
        $search = htmlspecialchars($search);
        try {
            $stmt = $this->db->prepare('SELECT Track.*, MediaType.Name AS MediaTypeName, Genre.Name AS '
                .'GenreName FROM Track JOIN MediaType ON Track.MediaTypeId = MediaType.MediaTypeId JOIN '
                .'Genre ON Track.GenreId = Genre.GenreId WHERE Track.Name LIKE :search');
            $searchParam = '%' . $search . '%';
            $stmt->bindParam(':search', $searchParam, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($result as $track) {
                $tracks[] = Formatter::formatTrack($track);
            }
            \Utilities\Response::send($tracks);
        } catch (\PDOException $e) {
            \Utilities\Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    # Retrieves a single track by its ID including its media type and genre
    public function getTracksById($id) {
        #get track by id including their media type and genre
        try {
            $stmt = $this->db->prepare('SELECT Track.*, MediaType.Name AS MediaTypeName, Genre.Name AS GenreName'
                .' FROM Track JOIN MediaType ON Track.MediaTypeId = MediaType.MediaTypeId JOIN Genre ON Track.GenreId '
                .'= Genre.GenreId WHERE Track.TrackId = :id');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                $track = Formatter::formatTrack($result);
                \Utilities\Response::send($track);
            } else {
                \Utilities\Response::error('Track not found', 404);
            }
        } catch (\PDOException $e) {
            \Utilities\Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    # Retrieves tracks by a specific composer
    public function getTracksByComposer($composer) {
        $composer = htmlspecialchars($composer);
        try {
            $stmt = $this->db->prepare('SELECT * FROM Track WHERE Composer = :composer');
            $stmt->bindParam(':composer', $composer, \PDO::PARAM_STR);
            $stmt->execute();
            $tracks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if ($tracks) {
                \Utilities\Response::send($tracks);
            } else {
                \Utilities\Response::error('No tracks found for this composer', 404);
            }
        } catch (\PDOException $e) {
            \Utilities\Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    # Creates a new track with album, media type, genre, composer, milliseconds, bytes, and unit price
    public function createTrack($data) {
        if (empty($data['Name']) || empty($data['Composer']) || !is_int($data['Milliseconds'])
            || !is_int($data['Bytes']) || !is_numeric($data['UnitPrice']) || !is_int($data['AlbumId'])
            || !is_int($data['MediaTypeId']) || !is_int($data['GenreId'])) {
            \Utilities\Response::error('Missing required fields or incorrect type', 400);
            return;
        }
        $data['Name'] = htmlspecialchars($data['Name']);
        $data['Composer'] = htmlspecialchars($data['Composer']);
        try {
            $stmt = $this->db->prepare('INSERT INTO Track (Name, AlbumId, MediaTypeId, GenreId, Composer, Milliseconds, Bytes, UnitPrice)'
                .'VALUES (:name, :albumId, :mediaTypeId, :genreId, :composer, :milliseconds, :bytes, :unitPrice)');
            $stmt->bindParam(':name', $data['Name']);
            $stmt->bindParam(':albumId', $data['AlbumId']);
            $stmt->bindParam(':mediaTypeId', $data['MediaTypeId']);
            $stmt->bindParam(':genreId', $data['GenreId']);
            $stmt->bindParam(':composer', $data['Composer']);
            $stmt->bindParam(':milliseconds', $data['Milliseconds'], \PDO::PARAM_INT);
            $stmt->bindParam(':bytes', $data['Bytes'], \PDO::PARAM_INT);
            $stmt->bindParam(':unitPrice', $data['UnitPrice']);
            if ($stmt->execute()) {
                \Utilities\Response::send(['message' => 'Track created successfully'], 200);
            } else {
                \Utilities\Response::error('Failed to create track', 500);
            }
        } catch (\PDOException $e) {
            \Utilities\Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    # Updates an existing track by its ID with new data
    public function updateTrack($id, $data) {
        if (!empty($data['Name'])) {
            $data['Name'] = htmlspecialchars($data['Name']);
        }
        if (!empty($data['Composer'])) {
            $data['Composer'] = htmlspecialchars($data['Composer']);
        }
        if (!empty($data['AlbumId']) && !is_int($data['AlbumId'])) {
            Response::error('AlbumId must be a number', 400);
            return;
        }
        if (!empty($data['MediaTypeId']) && !is_int($data['MediaTypeId'])) {
            Response::error('MediaTypeId must be a number', 400);
            return;
        }
        if (!empty($data['GenreId']) && !is_int($data['GenreId'])) {
            Response::error('GenreId must be a number', 400);
            return;
        }
        if (!empty($data['Milliseconds']) && !is_int($data['Milliseconds'])) {
            Response::error('Milliseconds must be a number', 400);
            return;
        }
        if (!empty($data['Bytes']) && !is_int($data['Bytes'])) {
            Response::error('Bytes must be a number', 400);
            return;
        }
        if (!empty($data['UnitPrice']) && !is_numeric($data['UnitPrice'])) {
            Response::error('UnitPrice must be a number', 400);
            return;
        }
        if (empty($data['Name']) && empty($data['AlbumId']) && empty($data['MediaTypeId'])
            && empty($data['GenreId']) && empty($data['Composer']) && empty($data['Milliseconds'])
            && empty($data['Bytes']) && empty($data['UnitPrice'])) {
            \Utilities\Response::error('At least one field must be provided for update', 400);
            return;
        }
        try {
            $stmt = $this->db->prepare('UPDATE Track SET Name = IFNULL(:name, Name), AlbumId = IFNULL(:albumId, AlbumId), MediaTypeId = IFNULL(:mediaTypeId, MediaTypeId),'
                .' GenreId = IFNULL(:genreId, GenreId), Composer = IFNULL(:composer, Composer), Milliseconds = IFNULL(:milliseconds, Milliseconds), Bytes = IFNULL(:bytes, Bytes), UnitPrice '
                .'= IFNULL(:unitPrice, UnitPrice) WHERE TrackId = :id');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->bindParam(':name', $data['Name']);
            $stmt->bindParam(':albumId', $data['AlbumId']);
            $stmt->bindParam(':mediaTypeId', $data['MediaTypeId']);
            $stmt->bindParam(':genreId', $data['GenreId']);
            $stmt->bindParam(':composer', $data['Composer']);
            $stmt->bindParam(':milliseconds', $data['Milliseconds'], \PDO::PARAM_INT);
            $stmt->bindParam(':bytes', $data['Bytes'], \PDO::PARAM_INT);
            $stmt->bindParam(':unitPrice', $data['UnitPrice']);
            if ($stmt->execute()) {
                \Utilities\Response::send(['message' => 'Track updated successfully'], 200);
            } else {
                \Utilities\Response::error('Failed to update track', 500);
            }
        } catch (\PDOException $e) {
            \Utilities\Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    # Deletes a track by its ID if it does not belong to any playlist
    public function deleteTrack($id) {
        try {
            $stmt = $this->db->prepare('DELETE FROM Track WHERE TrackId = :id');
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    \Utilities\Response::send(['message' => 'Track deleted successfully']);
                } else {
                    \Utilities\Response::error('Track not found', 404);
                }
            } else {
                \Utilities\Response::error('Failed to delete track', 500);
            }
        } catch (\PDOException $e) {
            \Utilities\Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }
}