<?php
// ChatController.php
require_once 'db.php';

class ChatController {
    private $pdo;
    
    // 👇 THAY KEY CỦA BRO VÀO ĐÂY
    private $apiKey = 'AIzaSyDurVQmvTUPuYr2MPbw4ufcutetK-q2F2Y';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // --- HELPER: Kiểm tra gói cước ---
    private function getUserMembershipTier($userId) {
        if ($userId == 1) return 'Free';
        return 'Premium'; 
    }

    // --- 1. LẤY DANH SÁCH LỊCH SỬ CHAT ---
    public function getUserHistory($userId) {
        $sql = "SELECT s.SessionID, s.Title, s.CreatedAt, p.PersonaName, t.TopicName 
                FROM ChatSessions s
                LEFT JOIN Personas p ON s.PersonaID = p.PersonaID
                LEFT JOIN Topics t ON s.TopicID = t.TopicID
                WHERE s.UserID = ?
                ORDER BY s.CreatedAt DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // --- 2. LẤY NỘI DUNG CHI TIẾT 1 PHIÊN CHAT ---
    public function getSessionMessages($sessionId, $userId) {
        // Kiểm tra quyền sở hữu session
        $check = $this->pdo->prepare("SELECT UserID, PersonaID, TopicID FROM ChatSessions WHERE SessionID = ?");
        $check->execute([$sessionId]);
        $session = $check->fetch();

        if (!$session || $session['UserID'] != $userId) {
            return ['status' => 403, 'message' => 'Unauthorized'];
        }

        $sql = "SELECT Sender, Content, CreatedAt, AudioUrl FROM ChatMessages WHERE SessionID = ? ORDER BY CreatedAt ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId]);
        $messages = $stmt->fetchAll();

        return [
            'status' => 200, 
            'data' => [
                'session_info' => $session,
                'messages' => $messages
            ]
        ];
    }

    // --- 3. KHỞI TẠO CHAT THEO CHỦ ĐỀ (AI CHỦ ĐỘNG HỎI) ---
    public function initChatWithTopic($userId, $personaId, $topicId) {
        // Lấy thông tin Persona & Topic
        $pStmt = $this->pdo->prepare("SELECT IsPremium, SystemPrompt, PersonaName FROM Personas WHERE PersonaID = ?");
        $pStmt->execute([$personaId]);
        $persona = $pStmt->fetch();

        $tStmt = $this->pdo->prepare("SELECT TopicName FROM Topics WHERE TopicID = ?");
        $tStmt->execute([$topicId]);
        $topic = $tStmt->fetch();

        if (!$persona || !$topic) return ['status' => 404, 'message' => 'Data not found'];

        // Check Premium
        if ($persona['IsPremium'] && $this->getUserMembershipTier($userId) === 'Free') {
            return ['status' => 403, 'message' => 'Upgrade required', 'detail' => 'Premium persona.'];
        }

        // Tạo Session Mới
        $title = "Chat with " . $persona['PersonaName'] . " about " . $topic['TopicName'];
        $stmt = $this->pdo->prepare("INSERT INTO ChatSessions (UserID, PersonaID, TopicID, Title) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $personaId, $topicId, $title]);
        $sessionId = $this->pdo->lastInsertId();

        // Prompt để AI chủ động hỏi
        $prompt = "System: " . $persona['SystemPrompt'] . "\n" .
                  "Task: The user wants to talk about '" . $topic['TopicName'] . "'. " .
                  "Start the conversation by proactively asking a relevant, engaging open-ended question based on your persona. " .
                  "Do not wait for the user to say hi. Greeting in English.";
        
        $aiGreeting = $this->callGeminiAPI($prompt);

        // Lưu tin nhắn AI (Tin nhắn đầu tiên của session)
        $this->saveMessage($sessionId, 'AI', $aiGreeting);

        return [
            'status' => 200,
            'data' => [
                'session_id' => $sessionId,
                'response' => $aiGreeting
            ]
        ];
    }

    // --- 4. GỬI TIN NHẮN (CÓ TRÍ NHỚ) ---
    public function sendMessage($userId, $personaId, $topicId, $message, $sessionId = null, $imageBase64 = null) {
        // 1. Tạo session nếu chưa có
        if (!$sessionId) {
            $stmt = $this->pdo->prepare("INSERT INTO ChatSessions (UserID, PersonaID, TopicID, Title) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $personaId, $topicId, "New Conversation"]);
            $sessionId = $this->pdo->lastInsertId();
        }

        // 2. Xử lý lưu ảnh (Nếu có)
        $savedImagePath = null;
        if ($imageBase64) {
            $savedImagePath = $this->saveImageToDisk($imageBase64);
        }

        // 3. Lưu tin nhắn User vào DB
        $stmt = $this->pdo->prepare("INSERT INTO ChatMessages (SessionID, Sender, Content, ImagePath) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sessionId, 'User', $message, $savedImagePath]);

        // 4. Chuẩn bị Context (Memory)
        $stmt = $this->pdo->prepare("SELECT SystemPrompt FROM Personas WHERE PersonaID = ?");
        $stmt->execute([$personaId]);
        $persona = $stmt->fetch();

        $historyContext = $this->getConversationContext($userId, 30); 

        // 5. Chuẩn bị Prompt Text
        $textPrompt = "System Instruction: " . $persona['SystemPrompt'] . "\n\n" .
                      "--- Memory Stream ---\n" . $historyContext . "\n" . 
                      "---------------------\n" .
                      "User Input: " . $message . "\n" .
                      ( $imageBase64 ? "[User attached an image. Analyze it based on the text input.]" : "" ) . "\n" .
                      "Response (in English):";

        // 6. Gọi Gemini API (Có kèm ảnh nếu có)
        $aiResponseText = $this->callGeminiAPI($textPrompt, $imageBase64);

        // 7. Lưu tin nhắn AI
        $stmt = $this->pdo->prepare("INSERT INTO ChatMessages (SessionID, Sender, Content) VALUES (?, ?, ?)");
        $stmt->execute([$sessionId, 'AI', $aiResponseText]);

        return [
            'status' => 200,
            'data' => [
                'session_id' => $sessionId,
                'response' => $aiResponseText,
                'image_url' => $savedImagePath, // Trả về đường dẫn ảnh để hiển thị nếu cần
                'audioUrl' => null 
            ]
        ];
    }

    // --- HELPER: LƯU ẢNH VÀO SERVER ---
    private function saveImageToDisk($base64String) {
        // Tách header base64 (ví dụ: "data:image/png;base64,") ra khỏi data
        $parts = explode(',', $base64String);
        $data = base64_decode(end($parts));
        
        // Tạo tên file ngẫu nhiên
        $fileName = 'img_' . time() . '_' . rand(1000,9999) . '.jpg';
        $filePath = 'uploads/' . $fileName;
        
        file_put_contents($filePath, $data);
        return $filePath;
    }

    // --- HELPER: GỌI GEMINI (MULTIMODAL) ---
    private function callGeminiAPI($text, $imageBase64 = null) {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $this->apiKey;

        // Cấu trúc Part cho Text
        $parts = [
            ["text" => $text]
        ];

        // Nếu có ảnh, thêm Part cho Ảnh (Inline Data)
        if ($imageBase64) {
            // Lấy mime type (image/jpeg, image/png...)
            preg_match('/^data:(image\/\w+);base64,/', $imageBase64, $matches);
            $mimeType = $matches[1] ?? 'image/jpeg';
            $base64Clean = explode(',', $imageBase64)[1]; // Chỉ lấy phần data

            $parts[] = [
                "inline_data" => [
                    "mime_type" => $mimeType,
                    "data" => $base64Clean
                ]
            ];
        }

        $data = [
            "contents" => [
                [ "parts" => $parts ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) return "Connection Error: " . curl_error($ch);
        curl_close($ch);

        $jsonObj = json_decode($response, true);

        if (isset($jsonObj['error'])) return "API Error: " . $jsonObj['error']['message'];
        
        if (isset($jsonObj['candidates'][0]['content']['parts'][0]['text'])) {
            return $jsonObj['candidates'][0]['content']['parts'][0]['text'];
        } 
        return "I can't analyze this right now."; 
    }

    private function saveMessage($sessionId, $sender, $content) {
        $stmt = $this->pdo->prepare("INSERT INTO ChatMessages (SessionID, Sender, Content) VALUES (?, ?, ?)");
        $stmt->execute([$sessionId, $sender, $content]);
    }

    // --- 5. DELETE SESSION (Xóa đoạn chat) ---
    public function deleteSession($sessionId, $userId) {
        // Kiểm tra quyền sở hữu trước khi xóa
        $stmt = $this->pdo->prepare("DELETE FROM ChatSessions WHERE SessionID = ? AND UserID = ?");
        $stmt->execute([$sessionId, $userId]);
        
        if ($stmt->rowCount() > 0) {
            return ['status' => 200, 'message' => 'Chat deleted successfully'];
        } else {
            return ['status' => 404, 'message' => 'Chat not found or access denied'];
        }
    }

    // --- 6. UPDATE SESSION TITLE (Đổi tên đoạn chat) ---
    public function renameSession($sessionId, $userId, $newTitle) {
        if (empty(trim($newTitle))) {
            return ['status' => 400, 'message' => 'Title cannot be empty'];
        }

        $stmt = $this->pdo->prepare("UPDATE ChatSessions SET Title = ? WHERE SessionID = ? AND UserID = ?");
        $stmt->execute([$newTitle, $sessionId, $userId]);

        if ($stmt->rowCount() > 0) {
            return ['status' => 200, 'message' => 'Chat renamed successfully'];
        } else {
            return ['status' => 404, 'message' => 'Chat not found or no changes made'];
        }
    }

    // --- 7. HELPER: LẤY NGỮ CẢNH HỘI THOẠI (MEMORY) ---
    // --- 7. HELPER: LẤY NGỮ CẢNH TOÀN BỘ LỊCH SỬ (GLOBAL MEMORY) ---
    private function getConversationContext($userId, $limit = 30) {
        // Lấy X tin nhắn gần nhất CỦA USER (Bất kể session nào)
        $sql = "SELECT m.Sender, m.Content, s.SessionID 
                FROM ChatMessages m
                JOIN ChatSessions s ON m.SessionID = s.SessionID
                WHERE s.UserID = ? 
                ORDER BY m.CreatedAt DESC 
                LIMIT ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Đảo ngược để đúng thứ tự thời gian
        $rows = array_reverse($rows);

        $contextString = "";
        $currentSession = null;

        foreach ($rows as $msg) {
            // Thêm dấu ngăn cách nếu chuyển sang session khác (Optional, giúp AI phân biệt)
            if ($currentSession !== $msg['SessionID']) {
                $contextString .= "\n[--- Conversation Segment ---]\n";
                $currentSession = $msg['SessionID'];
            }

            $role = ($msg['Sender'] === 'User') ? 'User' : 'AI';
            $cleanContent = str_replace(["\r", "\n"], " ", $msg['Content']);
            $contextString .= "$role: $cleanContent\n";
        }

        return $contextString;
    }
}
?>