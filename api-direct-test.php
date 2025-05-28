<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Get URL parameters
$type = $_GET['type'] ?? 'artists';
$id = $_GET['id'] ?? null;

// Load database config
$config = require_once __DIR__ . '/config/database.php';

try {
    // Create database connection
    $db = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['user'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Choose what data to return based on parameters
    switch($type) {
        case 'artists':
            if ($id) {
                $stmt = $db->prepare('SELECT * FROM Artist WHERE ArtistId = ?');
                $stmt->execute([$id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $db->query('SELECT * FROM Artist LIMIT 20');
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;
            
        case 'albums':
            if ($id) {
                $stmt = $db->prepare('SELECT * FROM Album WHERE AlbumId = ?');
                $stmt->execute([$id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $db->query('SELECT * FROM Album LIMIT 20');
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;
            
        case 'tracks':
            if ($id) {
                $stmt = $db->prepare('SELECT * FROM Track WHERE TrackId = ?');
                $stmt->execute([$id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $stmt = $db->query('SELECT * FROM Track LIMIT 20');
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;
            
        default:
            $data = ['error' => 'Invalid type specified'];
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>