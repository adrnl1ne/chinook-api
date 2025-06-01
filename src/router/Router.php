<?php

use Utilities\Response;
class Router
{
    private $routes = [];

    public function addRoute($method, $path, $handler)
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    private function getActualMethod() {
        // First check for method override in POST body
        $data = json_decode(file_get_contents('php://input'), true);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['_method'])) {
            return strtoupper($data['_method']);
        }
        
        // Then check for method override in query string
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['_method'])) {
            return strtoupper($_GET['_method']);
        }
        
        // Finally check for header override
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            return strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        }
        
        return $_SERVER['REQUEST_METHOD'];
    }

    public function dispatch($method, $path)
    {
        // Get the actual method, considering possible overrides
        $actualMethod = $this->getActualMethod();
        
        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($actualMethod) && preg_match($route['path'], $path, $matches)) {
                return call_user_func($route['handler'], ...array_slice($matches, 1));
            }
        }
        Response::error('Invalid endpoint', 404);
        return null;
    }

}