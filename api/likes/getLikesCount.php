<?php
include_once '../../lib/DatabaseConnection.php';

function getLikesCount($postId) {
    // Kiểm tra xem postId có trống không
    if (empty($postId)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "postId is required"]);
        return;
    }

    $db = new DatabaseConnection();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT COUNT(*) as likeCount FROM likes WHERE post_id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $db->close();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(["success" => true, "likeCount" => $row['likeCount']]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "An error occurred while retrieving like count"]);
    }
}

header('Content-Type: application/json; charset=utf-8');

// Lấy postId từ URL
if (isset($_GET['postId'])) {
    $postId = $_GET['postId'];
    getLikesCount($postId);
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "postId parameter is missing"]);
}
?>
