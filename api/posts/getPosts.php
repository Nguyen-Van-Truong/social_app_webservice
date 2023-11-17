<?php

include_once '../../lib/DatabaseConnection.php';

function getPosts() {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Truy vấn cơ sở dữ liệu để lấy danh sách bài viết từ mới tới cũ
    $sql = "SELECT * FROM posts ORDER BY created_at DESC";
    $result = $conn->query($sql);

    if ($result) {
        $posts = array();

        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }

        echo json_encode(["success" => true, "posts" => $posts]);
    } else {
        echo json_encode(["success" => false, "message" => "Không thể lấy danh sách bài viết"]);
    }

    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

getPosts();
?>
