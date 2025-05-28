<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Simple health endpoint
if (isset($_GET['phpinfo'])) {
    echo json_encode(['php_version' => phpversion(), 'time' => date('Y-m-d H:i:s')]);
    exit;
}

require_once __DIR__ . '/src/Controllers/ArtistController.php';
require_once __DIR__ . '/src/Controllers/AlbumController.php';
require_once __DIR__ . '/src/Controllers/GenreController.php';
require_once __DIR__ . '/src/Controllers/TracksController.php';
require_once __DIR__ . '/src/Controllers/PlaylistController.php';
require_once __DIR__ . '/src/Controllers/MediaTypesController.php';
require_once __DIR__ . '/src/Utilities/Response.php';
require_once __DIR__ . '/src/router/Router.php';

use Controllers\ArtistController;
use Controllers\AlbumController;
use Controllers\GenreController;
use Controllers\TracksController;
use Controllers\PlaylistController;
use Controllers\MediaTypesController;
use Utilities\Response;

$router = new Router();

// Get the request URI and remove trailing slash
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim($uri, '/');

// Create a simple API status endpoint
$router->addRoute('GET', '%^status$%', function() {
    echo json_encode([
        'status' => 'online',
        'message' => 'Chinook API is working!',
        'server_time' => date('Y-m-d H:i:s'),
        'php_version' => phpversion()
    ]);
});

// Fix all the route patterns by removing leading slashes
$router->addRoute('GET', '%^artists$%', function() {
    $controller = new ArtistController();
    if (isset($_GET['s'])) {
        return $controller->getBySearch($_GET['s']);
    } else {
        return $controller->getAll();
    }
});

$router->addRoute('GET', '%^albums$%', function() {
    $controller = new AlbumController();
    if (isset($_GET['s'])) {
        return $controller->getAlbumBySearch($_GET['s']);
    } else {
        return $controller->getAll();
    }
});

$router->addRoute('GET', '%^tracks$%', function() {
    $controller = new TracksController();
    if (isset($_GET['s'])) {
        return $controller->getBySearch($_GET['s']);
    } else if (isset($_GET['composer'])) {
        return $controller->getTracksByComposer($_GET['composer']);
    } else {
        return $controller->getAll();
    }
});

$router->addRoute('GET', '%^genres$%', function() {
    $controller = new GenreController();
    return $controller->getAll();
});

$router->addRoute('GET', '%^mediatypes$%', function() {
    $controller = new MediaTypesController();
    return $controller->getAll();
});

$router->addRoute('GET', '%^playlists$%', function() {
    $controller = new PlaylistController();
    if (isset($_GET['s'])) {
        return $controller->getPlaylistBySearch($_GET['s']);
    } else {
        return $controller->getAll();
    }
});

// ID-based routes - fix all patterns
$router->addRoute('GET', '%^artists/(\d+)$%', function($id) {
    $controller = new ArtistController();
    return $controller->getById($id);
});

$router->addRoute('GET', '%^artists/(\d+)/albums$%', function($artistId) {
    $controller = new ArtistController();
    return $controller->getAlbumsByArtistId($artistId);
});

$router->addRoute('GET', '%^albums/(\d+)/tracks$%', function($albumId) {
    $controller = new AlbumController();
    return $controller->getTracksByAlbumId($albumId);
});

$router->addRoute('GET', '%^playlists/(\d+)$%', function($id) {
    $controller = new PlaylistController();
    return $controller->getById($id);
});

$router->addRoute('GET', '%^albums/(\d+)$%', function($id) {
    $controller = new AlbumController();
    return $controller->getById($id);
});

$router->addRoute('GET', '%^tracks/(\d+)$%', function($id) {
    $controller = new TracksController();
    return $controller->getTracksById($id);
});

// POST routes
$router->addRoute('POST', '%^artists$%', function() {
    $controller = new ArtistController();
    $data = json_decode(file_get_contents('php://input'), true);
    return $controller->createArtist($data);
});

$router->addRoute('POST', '%^playlists$%', function() {
    $controller = new PlaylistController();
    $data = json_decode(file_get_contents('php://input'), true);
    return $controller->createPlaylist($data);
});

$router->addRoute('POST', '%^albums$%', function() {
    $controller = new AlbumController();
    $data = json_decode(file_get_contents('php://input'), true);
    return $controller->createAlbum($data);
});

$router->addRoute('POST', '%^playlists/(\d+)/tracks$%', function($playlistId) {
    $controller = new PlaylistController();
    $data = json_decode(file_get_contents('php://input'), true);
    return $controller->addTrackToPlaylist($playlistId, $data);
});

$router->addRoute('POST', '%^tracks$%', function() {
    $controller = new TracksController();
    $data = json_decode(file_get_contents('php://input'), true);
    return $controller->createTrack($data);
});

// PUT routes
$router->addRoute('PUT', '%^artists/(\d+)$%', function($id) {
    $controller = new ArtistController();
    $data = json_decode(file_get_contents('php://input'), true);
    return $controller->updateArtist($id, $data);
});

$router->addRoute('PUT', '%^albums/(\d+)$%', function($id) {
    $controller = new AlbumController();
    $data = json_decode(file_get_contents('php://input'), true);
    return $controller->updateAlbum($id, $data);
});

$router->addRoute('PUT', '%^tracks/(\d+)$%', function($id) {
    $controller = new TracksController();
    $data = json_decode(file_get_contents('php://input'), true);
    return $controller->updateTrack($id, $data);
});

// DELETE routes
$router->addRoute('DELETE', '%^artists/(\d+)$%', function($id) {
    $controller = new ArtistController();
    return $controller->deleteArtist($id);
});

$router->addRoute('DELETE', '%^playlists/(\d+)/tracks/(\d+)$%', function($playlistId, $trackId) {
    $controller = new PlaylistController();
    return $controller->removeTrackFromPlaylist($playlistId, $trackId);
});

$router->addRoute('DELETE', '%^playlists/(\d+)$%', function($id) {
    $controller = new PlaylistController();
    return $controller->deletePlaylist($id);
});

$router->addRoute('DELETE', '%^tracks/(\d+)$%', function($id) {
    $controller = new TracksController();
    return $controller->deleteTrack($id);
});

$router->addRoute('DELETE', '%^albums/(\d+)$%', function($id) {
    $controller = new AlbumController();
    return $controller->deleteAlbum($id);
});

$router->dispatch($method, $uri);
?>