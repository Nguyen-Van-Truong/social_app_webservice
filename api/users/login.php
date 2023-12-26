<?php
include_once '../../lib/DatabaseConnection.php';
include_once '../../lib/PasswordManager.php';

function loginUser($email, $password) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Prepare the query with a JOIN to include the profile image URL
    $stmt = $conn->prepare("SELECT u.user_id, u.email, u.username, u.password_hash, m.file_url as profile_image_url 
                             FROM users u 
                             LEFT JOIN medias m ON u.profile_image_id = m.media_id 
                             WHERE u.email = ?");
    $stmt->bind_param("s", $email);

    // Execute the query
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Use PasswordManager to check the password
            if (PasswordManager::verifyPassword($password, $user['password_hash'])) {
                // Login successful
                $token = bin2hex(random_bytes(16)); // Generate a random token

                // Respond with user information and profile image URL
                echo json_encode([
                    "success" => true,
                    "message" => "Đăng nhập thành công",
                    "user_id" => $user['user_id'],
                    "token" => $token,
                    "email" => $user['email'],
                    "username" => $user['username'],
                    "profile_image_url" => $user['profile_image_url'] // Add profile image URL
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Sai mật khẩu"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Người dùng không tồn tại"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Lỗi khi thực hiện truy vấn"]);
    }

    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

loginUser($email, $password);
?>
