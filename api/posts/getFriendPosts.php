<?php

include_once '../../lib/DatabaseConnection.php';

function getFriendPosts($userId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Truy vấn cơ sở dữ liệu để lấy danh sách bạn bè của người dùng
    $sql = "SELECT user_id1, user_id2 FROM friendships WHERE user_id1 = ? OR user_id2 = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $friendIds = array();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['user_id1'] == $userId) {
            $friendIds[] = $row['user_id2'];
        } else {
            $friendIds[] = $row['user_id1'];
        }
    }

    // Thêm cả user_id của người dùng hiện tại vào danh sách bạn bè
    $friendIds[] = $userId;
    
    // Truy vấn cơ sở dữ liệu để lấy danh sách bài viết của bạn bè
    $sql = "SELECT * FROM posts WHERE user_id IN (" . implode(",", $friendIds) . ") ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if ($result) {
        $posts = array();

        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }

        echo json_encode(["success" => true, "posts" => $posts]);
    } else {
        echo json_encode(["success" => false, "message" => "Không thể lấy danh sách bài viết của bạn bè"]);
    }

    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

// Nhận dữ liệu từ POST request
$userId = $_POST['userId'] ?? '';

getFriendPosts($userId);
?>
