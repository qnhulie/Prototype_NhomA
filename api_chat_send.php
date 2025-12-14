<?php
// api_chat_send.php
// 1. Cho phép CORS (quan trọng nếu chạy HTML và PHP khác port)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json; charset=utf-8');

// 2. Tắt hiển thị lỗi ra màn hình (để tránh làm hỏng JSON)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 3. Hàm bắt lỗi Fatal (Nếu code sập, nó vẫn trả về JSON)
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && $error['type'] === E_ERROR) {
        http_response_code(500);
        echo json_encode(['status' => 500, 'message' => 'PHP Fatal Error: ' . $error['message']]);
    }
});

try {
    // Kiểm tra method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method Not Allowed', 405);
    }

    // Kiểm tra file tồn tại
    if (!file_exists('ChatController.php')) {
        throw new Exception('File ChatController.php not found on server.');
    }

    require_once 'ChatController.php';

    // Nhận dữ liệu
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    if (!$input) {
        throw new Exception('Invalid JSON Input');
    }

    // Validate
    if (empty($input['user_id']) || empty($input['message']) || empty($input['persona_id'])) {
        throw new Exception('Missing required fields (user_id, message, or persona_id)');
    }

    // Khởi tạo Controller
    if (!isset($pdo)) {
        throw new Exception('Database connection failed. Check db.php');
    }
    
    $controller = new ChatController($pdo);
    
    // Gọi hàm xử lý
    $result = $controller->sendMessage(
        $input['user_id'], 
        $input['persona_id'], 
        $input['topic_id'] ?? 1, 
        $input['message']
    );

    // Trả kết quả
    http_response_code($result['status']);
    echo json_encode($result);

} catch (Exception $e) {
    // Bắt mọi Exception và trả về JSON
    $code = $e->getCode() ?: 500;
    http_response_code($code > 599 ? 500 : $code); // Code HTTP phải hợp lệ
    echo json_encode([
        'status' => $code, 
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
}
?>