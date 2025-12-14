<?php
// ChatController.php
require_once 'db.php';

class ChatController {
    private $pdo;
    
        private $apiKey = 'AIzaSyCh821KTTIqujLxyYpMrfY11OBd3nf2VbM';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function getUserMembershipTier($userId) {
        if ($userId == 1) return 'Free';
        return 'Premium'; 
    }

    public function sendMessage($userId, $personaId, $topicId, $message) {
        // 1. AUTH GUARD
        $stmt = $this->pdo->prepare("SELECT IsPremium, SystemPrompt, PersonaName FROM Personas WHERE PersonaID = ?");
        $stmt->execute([$personaId]);
        $persona = $stmt->fetch();

        if (!$persona) return ['status' => 404, 'message' => 'Persona not found'];

        $userTier = $this->getUserMembershipTier($userId);
        if ($persona['IsPremium'] && $userTier === 'Free') {
            return [
                'status' => 403, 
                'message' => 'Upgrade required', 
                'detail' => 'This persona is reserved for Premium members.'
            ];
        }

        // 2. TẠO SESSION & LƯU MESSAGE USER
        $stmt = $this->pdo->prepare("INSERT INTO ChatSessions (UserID, PersonaID, TopicID) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $personaId, $topicId]);
        $sessionId = $this->pdo->lastInsertId();

        $this->saveMessage($sessionId, 'User', $message);

        // 3. GỌI REAL AI (GEMINI)
        $fullContextPrompt = "System Instruction: " . $persona['SystemPrompt'] . "\n" .
                             "User Input: " . $message . "\n" .
                             "Response (in English):";

        // Gọi hàm xử lý AI mới (có bắt lỗi chi tiết)
        $aiResponseText = $this->callGeminiAPI($fullContextPrompt);

        // 4. LƯU MESSAGE AI
        $this->saveMessage($sessionId, 'AI', $aiResponseText);

        return [
            'status' => 200,
            'data' => [
                'response' => $aiResponseText,
                'audioUrl' => null 
            ]
        ];
    }

    private function saveMessage($sessionId, $sender, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO ChatMessages (SessionID, Sender, Content) VALUES (?, ?, ?)");
        $stmt->execute([$sessionId, $sender, $content]);
    }

    // --- HÀM GỌI GOOGLE GEMINI API (ĐÃ NÂNG CẤP) ---
    private function callGeminiAPI($prompt) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        // Bỏ qua SSL (Chỉ dùng cho Localhost để tránh lỗi kết nối)
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        
        // 1. Kiểm tra lỗi kết nối mạng (CURL Error)
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            return "Connection Error: " . $error_msg;
        }
        
        curl_close($ch);

        $jsonObj = json_decode($response, true);

        // 2. Kiểm tra lỗi từ Google trả về (API Error)
        if (isset($jsonObj['error'])) {
            // Trả về thông báo lỗi cụ thể từ Google
            return "Google API Error: " . $jsonObj['error']['message'];
        }

        // 3. Lấy kết quả thành công
        if (isset($jsonObj['candidates'][0]['content']['parts'][0]['text'])) {
            return $jsonObj['candidates'][0]['content']['parts'][0]['text'];
        } else {
            // Trường hợp JSON trả về lạ, in ra để debug (hoặc xem log)
            return "AI Error: Unexpected response format. Raw: " . substr($response, 0, 100) . "..."; 
        }
    }
}
?>