<?php
include_once '../../lib/DatabaseConnection.php';
include_once '../../lib/PasswordManager.php';

function loginUser($email, $password) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Chuẩn bị truy vấn
    $stmt = $conn->prepare("SELECT user_id, email, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);

    // Thực thi truy vấn
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Sử dụng PasswordManager để kiểm tra mật khẩu
            if (PasswordManager::verifyPassword($password, $user['password_hash'])) {
                // Đăng nhập thành công
                echo json_encode(["success" => true, "message" => "Đăng nhập thành công", "user_id" => $user['user_id']]);
            } else {
                // Sai mật khẩu
                echo json_encode(["success" => false, "message" => "Sai mật khẩu"]);
            }
        } else {
            // Người dùng không tồn tại
            echo json_encode(["success" => false, "message" => "Người dùng không tồn tại"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Lỗi khi thực hiện truy vấn"]);
    }

    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

// Nhận dữ liệu từ POST
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

loginUser($email, $password);
?>
