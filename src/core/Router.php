<?php
namespace TodoList\Core;

class Router {
    private $routes = [];

    public function addRoute(string $method, string $path, $handler, array $middleware = []): void {
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes[$method] ?? [] as $route => $config) {
            $pattern = preg_replace('/\/:(\w+)/', '/(\w+)', $route);
            $pattern = "@^" . $pattern . "$@D";

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); 

                foreach ($config['middleware'] as $middleware) {
                    call_user_func($middleware);
                }

                if (is_array($config['handler'])) {
                    $class = new $config['handler'][0]();
                    call_user_func_array([$class, $config['handler'][1]], $matches);
                } else {
                    call_user_func_array($config['handler'], $matches);
                }
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }
}