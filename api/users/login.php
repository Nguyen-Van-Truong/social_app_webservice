<?php
include_once '../../lib/DatabaseConnection.php';
include_once '../../lib/PasswordManager.php';

function loginUser($email, $password) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Prepare the query
    $stmt = $conn->prepare("SELECT user_id, email, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);

    // Execute the query
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Use PasswordManager to check the password
            if (PasswordManager::verifyPassword($password, $user['password_hash'])) {
                // Login successful
                // Generate a token for the user
                $token = bin2hex(random_bytes(16)); // Example of random token generation

                // TODO: Save the token in the database if needed

                echo json_encode([
                    "success" => true,
                    "message" => "Đăng nhập thành công",
                    "user_id" => $user['user_id'],
                    "token" => $token,
                    "email" => $user['email']  // Include email in the response
                ]);
            } else {
                // Incorrect password
                echo json_encode(["success" => false, "message" => "Sai mật khẩu"]);
            }
        } else {
            // User does not exist
            echo json_encode(["success" => false, "message" => "Người dùng không tồn tại"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Lỗi khi thực hiện truy vấn"]);
    }

    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

// Get data from POST
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

loginUser($email, $password);
?>
