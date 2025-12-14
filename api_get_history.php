<?php
// api_get_history.php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
require_once 'ChatController.php';

// Giả lập lấy User ID từ GET (Thực tế nên lấy từ Session/Token)
$userId = $_GET['user_id'] ?? 1; 

try {
    $controller = new ChatController($pdo);
    $history = $controller->getUserHistory($userId);
    echo json_encode(['status' => 200, 'data' => $history]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}
?>