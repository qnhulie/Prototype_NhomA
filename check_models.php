<?php
// check_models.php
header('Content-Type: text/html; charset=utf-8');

// 👇 THAY API KEY CỦA BRO VÀO ĐÂY
$apiKey = 'AIzaSyCh821KTTIqujLxyYpMrfY11OBd3nf2VbM'; 

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die("Lỗi kết nối: " . $error);
}

$data = json_decode($response, true);

echo "<h2>Danh sách Model khả dụng cho Key của bạn:</h2>";

if (isset($data['models'])) {
    echo "<ul>";
    foreach ($data['models'] as $model) {
        // Chỉ lấy những model hỗ trợ chat (generateContent)
        if (in_array("generateContent", $model['supportedGenerationMethods'])) {
            // Lấy phần tên sau chữ "models/"
            $cleanName = str_replace("models/", "", $model['name']);
            echo "<li><strong style='color:blue; font-size:1.2em'>$cleanName</strong> <br> (Version: {$model['version']})</li>";
        }
    }
    echo "</ul>";
    echo "<p>👉 Hãy copy một trong các tên in đậm màu xanh ở trên và thay vào file ChatController.php</p>";
} else {
    echo "<h3 style='color:red'>Lỗi API:</h3>";
    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
}
?>