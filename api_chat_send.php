<?php
// api_chat_send.php
// ... (Các phần header giữ nguyên như file cũ) ...
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');
require_once 'ChatController.php';

// ... (Phần validate giữ nguyên) ...

// Sửa đoạn gọi controller:
try {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);
    
    // Validate cơ bản
    if (empty($input['user_id']) || empty($input['message'])) {
        throw new Exception('Missing required fields');
    }

    $controller = new ChatController($pdo);
    
    $result = $controller->sendMessage(
        $input['user_id'], 
        $input['persona_id'], 
        $input['topic_id'] ?? 1, 
        $input['message'],
        $input['session_id'] ?? null // <--- QUAN TRỌNG: Nhận Session ID nếu có
    );

    http_response_code($result['status']);
    echo json_encode($result);

    // Gọi hàm xử lý
    $result = $controller->sendMessage(
        $input['user_id'], 
        $input['persona_id'], 
        $input['topic_id'] ?? 1, 
        $input['message'],
        $input['session_id'] ?? null,
        $input['image'] ?? null // <--- NHẬN THÊM TRƯỜNG IMAGE (BASE64)
    );

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 500, 'message' => $e->getMessage()]);
}


?>