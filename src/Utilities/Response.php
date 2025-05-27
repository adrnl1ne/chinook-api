<?php
namespace Utilities;

if (!class_exists('Utilities\Response')) {
    class Response {
    public static function send($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public static function error($message, $statusCode) {
        self::send(['error' => $message], $statusCode);
    }
}
}

?>