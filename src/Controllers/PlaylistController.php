<?php

namespace Controllers;
require_once __DIR__ . '/../Models/BaseModel.php';
use Models\BaseModel;
use Utilities\Response;

class PlaylistController extends BaseModel {

    public function getAll() {
        try {
            $stmt = $this->db->query('SELECT * FROM Playlist');
            $playlists = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            Response::send($playlists);
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    public function getById($id) {
        #get a playlist by id including its tracks
        if (!is_numeric($id)) {
            Response::error('Invalid playlist ID', 400);
            return;
        }
        try {
            $stmt = $this->db->prepare('SELECT P.PlaylistId, P.Name PlaylistName, T.TrackId, T.Name '
                .'TrackName FROM Playlist P LEFT JOIN PlaylistTrack ON P.PlaylistId = PlaylistTrack.PlaylistId'
                .' LEFT JOIN Track T ON T.TrackId = PlaylistTrack.TrackId WHERE P.PlaylistId = ?');
            $stmt->execute([$id]);
            $result = $stmt->fetchall(\PDO::FETCH_ASSOC | \PDO::FETCH_GROUP);
            if (!$result) {
                Response::error('Playlist not found', 404);
                return;
            }
            foreach ($result as $playlistId => $playlist) {
                $playlistName = $playlist[0]['PlaylistName'];
                foreach ($playlist as $key => $track) {
                    unset($playlist[$key]['PlaylistName']);
                }
                $playlists = [
                    'PlaylistId' => $playlistId,
                    'Name' => $playlistName,
                    'Tracks' => array_filter($playlist, fn($track) => $track['TrackId'] != null)
                ];
            }
            Response::send($playlists);
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    public function getPlaylistBySearch($search) {
        $search = htmlspecialchars($search);
        if (empty($search)) {
            Response::error('Search term cannot be empty', 400);
            return;
        }
        #get playlist including its tracks by search term
        try {
            $stmt = $this->db->prepare('SELECT * FROM Playlist WHERE Name LIKE ?');
            $stmt->execute(['%' . $search . '%']);
            $playlists = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (empty($playlists)) {
                Response::error('No playlists found', 404);
                return;
            }
            Response::send($playlists);
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }


    public function createPlaylist($data) {
        if (empty($data['Name'])) {
            Response::error('Playlist name is required', 400);
            return;
        }
        $data['Name'] = htmlspecialchars($data['Name']);
        try {
            $stmt = $this->db->prepare('INSERT INTO Playlist (Name) VALUES (?)');
            $stmt->execute([$data['Name']]);
            $playlistId = $this->db->lastInsertId();
            Response::send(['PlaylistId' => $playlistId], 201);
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    public function addTrackToPlaylist($playlistId, $data) {
        if (!is_numeric($playlistId) || !is_numeric($data['TrackId'])) {
            Response::error('Invalid playlist or track ID', 400);
            return;
        }
        try {
            $stmt = $this->db->prepare('INSERT INTO PlaylistTrack (PlaylistId, TrackId) VALUES (?, ?)');
            $stmt->execute([$playlistId, $data['TrackId']]);
            Response::send(['message' => 'Track added to playlist'], 201);
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    public function deletePlaylist($id) {
        if (!is_numeric($id)) {
            Response::error('Invalid playlist ID', 400);
            return;
        }
        try {
            $checkstmt = $this->db->prepare('SELECT * FROM PlaylistTrack WHERE PlaylistId = ?');
            $checkstmt->execute([$id]);
            if ($checkstmt->rowCount() != 0) {
                Response::error('Playlist not empty', 409);
                return;
            }
            $stmt = $this->db->prepare('DELETE FROM Playlist WHERE PlaylistId = ?');
            $stmt->execute([$id]);
            if ($stmt->rowCount() === 0) {
                Response::error('Playlist not found', 404);
                return;
            }
            Response::send(['message' => 'Playlist deleted successfully']);
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }

    public function removeTrackFromPlaylist($playlistId, $trackId) {
        if (!is_numeric($playlistId) || !is_numeric($trackId)) {
            Response::error('Invalid playlist or track ID', 400);
            return;
        }
        try {
            $stmt = $this->db->prepare('DELETE FROM PlaylistTrack WHERE PlaylistId = ? AND TrackId = ?');
            $stmt->execute([$playlistId, $trackId]);
            if ($stmt->rowCount() === 0) {
                Response::error('Track not found in playlist', 404);
                return;
            }
            Response::send(['message' => 'Track removed from playlist']);
        } catch (\PDOException $e) {
            Response::error('Database error: ' . $e->getMessage(), 500);
        }
    }
}