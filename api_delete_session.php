<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, DELETE, OPTIONS");
header('Content-Type: application/json; charset=utf-8');
require_once 'ChatController.php';

// Xử lý preflight request cho CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['user_id']) || empty($input['session_id'])) {
    echo json_encode(['status' => 400, 'message' => 'Missing fields']);
    exit;
}

try {
    $controller = new ChatController($pdo);
    $result = $controller->deleteSession($input['session_id'], $input['user_id']);
    http_response_code($result['status']);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}
?>