<?php

include_once '../../lib/DatabaseConnection.php';

function createPost($userId, $content, $visible) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $image = ''; // Khởi tạo biến để lưu trữ tên ảnh

    // Xử lý tải ảnh lên nếu có
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $file = $_FILES['image'];
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $userId . '_' . time() . '.' . $fileExtension; // Sử dụng userId và timestamp
        $fileTmpName = $file['tmp_name'];
        $fileDestination = '../../uploads/' . $fileName;
        move_uploaded_file($fileTmpName, $fileDestination);
        $image = 'uploads/' . $fileName;
    }

    // Chuẩn bị truy vấn
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, visible, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("issi", $userId, $content, $image, $visible);

    // Thực thi truy vấn
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Bài viết đã được đăng"]);
    } else {
        echo json_encode(["success" => false, "message" => "Không thể đăng bài viết"]);
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

// Nhận dữ liệu từ POST request
$userId = $_POST['userId'] ?? '';
$content = $_POST['content'] ?? '';
$visible = $_POST['visible'] ?? 1;

createPost($userId, $content, $visible);
?>
