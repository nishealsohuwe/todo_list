<?php
namespace TodoList\Controllers;

use TodoList\Core\Database;
use TodoList\Core\JwtAuth;

/**
 * @OA\Tag(name="Auth", description="Аутентификация пользователей")
 */
class AuthController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Register new user",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response="201", description="User created")
     * )
     */
    public function register(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }
    
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'User already exists']);
            return;
        }
    
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->execute([$data['email'], $hashedPassword]);
    
        $userId = $this->db->lastInsertId();
        $token = JwtAuth::generateToken($userId);
    
        http_response_code(201);
        echo json_encode(['token' => $token]);
    }

    public function login(): void {
        $data = json_decode(file_get_contents('php://input'), true);
    
        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }
    
        $stmt = $this->db->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();
    
        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }
    
        $token = JwtAuth::generateToken($user['id']);
    
        echo json_encode(['token' => $token]);
    }

    public function logout(): void {
        $token = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'] ?? '');
        $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
        
        (new TokenBlacklist())->add($token, $decoded->exp);
        
        echo json_encode(['message' => 'Logged out']);
    }
}