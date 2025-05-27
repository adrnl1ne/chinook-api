<?php
spl_autoload_register(function ($class) {
    // Convert namespace to full file path
    $path = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    
    // Check if file exists before requiring it
    if (file_exists($path)) {
        require_once $path;
    }
});
?>