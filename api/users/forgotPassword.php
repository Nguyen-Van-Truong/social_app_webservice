<?php

include_once '../../lib/DatabaseConnection.php';
include_once '../../api/users/MailSender.php';

function sendVerificationCode($email) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Kiểm tra xem email có tồn tại trong DB không
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if ($stmt === false) {
        die("Lỗi SQL: " . mysqli_error($conn));
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Email không tồn tại."]);
        return;
    }

    // Tạo mã xác nhận ngẫu nhiên
    $code = mt_rand(100000, 999999);

    // Lưu mã và thời gian hiện tại vào DB
    $resetStmt = $conn->prepare("INSERT INTO password_resets (email, code, created_at) VALUES (?, ?, NOW())");
    if ($resetStmt === false) {
        die("Lỗi SQL: " . mysqli_error($conn));
    }
    $resetStmt->bind_param("si", $email, $code);
    $resetStmt->execute();

    // Gửi mã xác nhận đến email người dùng
    $subject = "Mã Xác Nhận Từ SocialApp";
    $body = "Mã xác nhận của bạn là: $code";
    if (!MailSender::sendMail($email, $subject, $body)) {
        echo json_encode(["success" => false, "message" => "Không thể gửi email."]);
        return;
    }

    echo json_encode(["success" => true, "message" => "Mã xác nhận đã được gửi đến email."]);
}
header('Content-Type: application/json; charset=utf-8');

// Nhận email từ POST request
$email = $_POST['email'] ?? '';
sendVerificationCode($email);
?>
