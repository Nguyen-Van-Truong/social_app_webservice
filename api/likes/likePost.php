<?php
include_once '../../lib/DatabaseConnection.php';

function likePost($userId, $postId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Kiểm tra xem người dùng đã like bài viết này chưa
    $checkStmt = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");
    $checkStmt->bind_param("ii", $userId, $postId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Bạn đã like bài viết này"]);
        return;
    }

    // Thực hiện like bài viết
    $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $userId, $postId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Like bài viết thành công"]);
    } else {
        echo json_encode(["success" => false, "message" => "Không thể like bài viết"]);
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

$userId = $_POST['userId'] ?? '';
$postId = $_POST['postId'] ?? '';

likePost($userId, $postId);
?>
