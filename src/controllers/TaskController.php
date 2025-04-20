<?php
namespace TodoList\Controllers;

use PDO;
use TodoList\Core\Database;

class TaskController {
    const ALLOWED_STATUSES = ['в работе', 'завершено', 'дедлайн'];

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function list(): void {
        $userId = $GLOBALS['user_id'];
        
        // Получаем параметры пагинации
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = min(50, max(5, (int) ($_GET['per_page'] ?? 10)));
        $offset = ($page - 1) * $perPage;
    
        // Правильный SQL-запрос с пагинацией
        $sql = "SELECT * FROM tasks WHERE user_id = ? LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        
        // Явно указываем типы параметров
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        
        $stmt->execute();
    
        // Общее количество задач
        $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ?");
        $totalStmt->execute([$userId]);
        $totalTasks = $totalStmt->fetchColumn();
    
        echo json_encode([
            'tasks' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'pagination' => [
                'total' => (int) $totalTasks,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($totalTasks / $perPage)
            ]
        ]);
    }

    public function create(): void {
        $userId = $GLOBALS['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['title'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Title is required']);
            return;
        }

        if (isset($data['status']) && !in_array($data['status'], self::ALLOWED_STATUSES)) {
            http_response_code(422);
            echo json_encode(['error' => 'Status must be one of: ' . implode(', ', self::ALLOWED_STATUSES)]);
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO tasks (user_id, title, description, status, deadline)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $data['title'],
            $data['description'] ?? null,
            $data['status'] ?? 'в работе',
            $data['deadline'] ?? null
        ]);

        http_response_code(201);
        echo json_encode(['id' => $this->db->lastInsertId()]);
    }

    public function show(int $taskId): void {
        $userId = $GLOBALS['user_id'];
        
        $stmt = $this->db->prepare("
            SELECT id, title, description, status, deadline 
            FROM tasks 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$taskId, $userId]);
        $task = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$task) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
            return;
        }

        echo json_encode($task);
    }

    public function update(int $taskId): void {
        $userId = $GLOBALS['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        $stmt = $this->db->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$taskId, $userId]);
        
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
            return;
        }

        $allowedFields = ['title', 'description', 'status', 'deadline'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updateData)) {
            http_response_code(400);
            echo json_encode(['error' => 'No valid fields to update']);
            return;
        }
         
        if (isset($updateData['status']) && !in_array($updateData['status'], self::ALLOWED_STATUSES)) {
            http_response_code(422);
            echo json_encode(['error' => 'Status must be one of: ' . implode(', ', self::ALLOWED_STATUSES)]);
            return;
        }

        $setClause = implode(', ', array_map(fn($field) => "$field = :$field", array_keys($updateData)));
        $updateData['id'] = $taskId;
        $updateData['user_id'] = $userId;

        $stmt = $this->db->prepare("
            UPDATE tasks 
            SET $setClause 
            WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute($updateData);

        echo json_encode(['success' => true]);
    }

    public function delete(int $taskId): void {
        $userId = $GLOBALS['user_id'];

        $stmt = $this->db->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$taskId, $userId]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
            return;
        }

        echo json_encode(['success' => true]);
    }
}