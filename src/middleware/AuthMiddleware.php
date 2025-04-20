<?php
namespace TodoList\Middleware;

use TodoList\Core\JwtAuth;
use TodoList\Middleware\TokenBlacklist;

class AuthMiddleware {
    public static function handle(): void {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token not provided']);
            exit;
        }

        $token = $matches[1];
        $userId = JwtAuth::validateToken($token);

        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }

        if ((new TokenBlacklist())->isBlacklisted($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token revoked']);
            exit;
        }

        $GLOBALS['user_id'] = $userId;
    }
}