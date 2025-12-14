<?php
// api_get_messages.php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
require_once 'ChatController.php';

$userId = $_GET['user_id'] ?? 1;
$sessionId = $_GET['session_id'] ?? null;

if (!$sessionId) {
    echo json_encode(['status' => 400, 'message' => 'Missing session_id']);
    exit;
}

try {
    $controller = new ChatController($pdo);
    $result = $controller->getSessionMessages($sessionId, $userId);
    
    http_response_code($result['status']);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}
?>