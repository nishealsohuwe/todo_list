<?php
require __DIR__ . '/../vendor/autoload.php';

use TodoList\Core\{Router, Database};
use TodoList\Controllers\{AuthController, TaskController};
use TodoList\Middleware\AuthMiddleware;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// CORS
// $allowedOrigins = [
//     'https://your-production-domain.com',
//     'https://admin.your-domain.com'
// ];

// $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
// if (in_array($origin, $allowedOrigins)) {
//     header("Access-Control-Allow-Origin: $origin");
//     header("Access-Control-Allow-Credentials: true");
// }

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$router = new Router();

// Auth Routes
$router->addRoute('POST', '/register', [AuthController::class, 'register']);
$router->addRoute('POST', '/login', [AuthController::class, 'login']);
$router->addRoute('POST', '/logout', [AuthController::class, 'logout'], [AuthMiddleware::class . '::handle']);

// Tasks Routes
$router->addRoute('GET', '/tasks', [TaskController::class, 'list'], [AuthMiddleware::class . '::handle']);
$router->addRoute('POST', '/tasks', [TaskController::class, 'create'], [AuthMiddleware::class . '::handle']);
$router->addRoute('GET', '/tasks/(\d+)', [TaskController::class, 'show'], [AuthMiddleware::class . '::handle']);
$router->addRoute('PUT', '/tasks/(\d+)', [TaskController::class, 'update'], [AuthMiddleware::class . '::handle']);
$router->addRoute('DELETE', '/tasks/(\d+)', [TaskController::class, 'delete'], [AuthMiddleware::class . '::handle']);

$router->dispatch();