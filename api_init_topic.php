<?php
// api_init_topic.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');
require_once 'ChatController.php';

$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['user_id']) || empty($input['persona_id']) || empty($input['topic_id'])) {
    echo json_encode(['status' => 400, 'message' => 'Missing fields']);
    exit;
}

try {
    $controller = new ChatController($pdo);
    $result = $controller->initChatWithTopic(
        $input['user_id'], 
        $input['persona_id'], 
        $input['topic_id']
    );

    http_response_code($result['status']);
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}
?>