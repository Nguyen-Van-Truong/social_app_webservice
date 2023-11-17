<?php
include_once '../../lib/DatabaseConnection.php';

function sendMessage($senderId, $receiverId, $message) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Chuẩn bị truy vấn
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $senderId, $receiverId, $message);

    // Thực thi truy vấn
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Tin nhắn đã được gửi"]);
    } else {
        echo json_encode(["success" => false, "message" => "Không thể gửi tin nhắn"]);
        echo "Lỗi SQL: " . $stmt->error;
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

// Nhận dữ liệu từ POST request
$senderId = $_POST['senderId'] ?? '';
$receiverId = $_POST['receiverId'] ?? '';
$message = $_POST['message'] ?? '';

sendMessage($senderId, $receiverId, $message);
?>
