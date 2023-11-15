<?php
include_once '../../lib/DatabaseConnection.php';
include_once '../../lib/PasswordManager.php';

function registerUser($username, $email, $password, $gender, $bio) {
    // Kiểm tra dữ liệu đầu vào
    if (empty($username) || empty($email) || empty($password) || empty($gender)) {
        echo json_encode(["success" => false, "message" => "Vui lòng điền vào tất cả các trường bắt buộc."]);
        return;
    }

    // Kiểm tra định dạng email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Định dạng email không hợp lệ."]);
        return;
    }

    // Kiểm tra giới tính
    $allowedGenders = ['male', 'female', '1', '2'];
    if (!in_array($gender, $allowedGenders)) {
        echo json_encode(["success" => false, "message" => "Giới tính không hợp lệ. Chỉ chấp nhận 'male', 'female', '1', hoặc '2'."]);
        return;
    }
    // Chuyển đổi giới tính thành male hoặc female nếu là số
    $gender = $gender === '1' ? 'male' : ($gender === '2' ? 'female' : $gender);

    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Kiểm tra xem email đã tồn tại chưa
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Email đã được sử dụng."]);
        $stmt->close();
        $db->close();
        return;
    }

    // Mã hóa mật khẩu
    $passwordHash = PasswordManager::hashPassword($password);

    // Chuẩn bị truy vấn đăng ký
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, gender, bio) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $passwordHash, $gender, $bio);

    // Thực thi truy vấn
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Đăng ký thành công"]);
    } else {
        // Lỗi có thể do vi phạm ràng buộc duy nhất hoặc các vấn đề khác
        echo json_encode(["success" => false, "message" => "Lỗi khi đăng ký. Có thể do lỗi server hoặc dữ liệu không hợp lệ."]);
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

// Nhận dữ liệu từ POST
$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$gender = $_POST['gender'] ?? '';
$bio = $_POST['bio'] ?? ''; // Tiểu sử là tùy chọn, có thể để trống

registerUser($username, $email, $password, $gender, $bio);
?>
