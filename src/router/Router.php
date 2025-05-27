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

    public function dispatch($method, $path)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === strtoupper($method) && preg_match($route['path'], $path, $matches)) {
                return call_user_func($route['handler'], ...array_slice($matches, 1)); // Pass matched parameters to the handler
            }
        }
        Response::error('Invalid endpoint', 404);
        return null; // No route found
    }
}