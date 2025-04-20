<?php
namespace TodoList\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuth {
    public static function generateToken(int $user_id): string {
        $payload = [
            'iss' => 'todo-list-api', // Кто выдал токен
            'sub' => $user_id,         // ID пользователя
            'iat' => time(),          // Время создания
            'exp' => time() + 3600     // Срок действия (1 час)
        ];
        return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    }

    public static function validateToken(string $token): ?int {
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            return $decoded->sub; 
        } catch (\Exception $e) {
            return null; 
        }
    }
}