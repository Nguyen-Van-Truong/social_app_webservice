<?php

include_once '../../lib/DatabaseConnection.php';

function getFriendPosts($userId, $page = 0, $limit = 10) {
    if (!is_numeric($userId) || $userId < 0) {
        echo json_encode(["success" => false, "message" => "User ID không hợp lệ"]);
        return;
    }

    $db = new DatabaseConnection();
    $conn = $db->connect();

    try {
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

        // Sử dụng prepared statement để truy vấn bảng posts
        $placeholders = implode(',', array_fill(0, count($friendIds), '?'));
        $offset = $page * $limit;

        // Sử dụng LIMIT và OFFSET trong truy vấn SQL
        $sql = "SELECT * FROM posts WHERE user_id IN ($placeholders) ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        // Kết hợp tất cả các đối số vào một mảng
        $params = array_merge($friendIds, [$limit, $offset]);

        // Sử dụng argument unpacking từ mảng đã kết hợp
        $stmt->bind_param(str_repeat('i', count($friendIds)) . 'ii', ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            $posts = array();

            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }

            echo json_encode(["success" => true, "posts" => $posts]);
        } else {
            echo json_encode(["success" => false, "message" => "Không thể lấy danh sách bài viết của bạn bè"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Có lỗi xảy ra: " . $e->getMessage()]);
    } finally {
        $db->close();
    }
}

header('Content-Type: application/json; charset=utf-8');

// Nhận dữ liệu từ POST request
$userId = $_POST['userId'] ?? '';
$page = $_POST['page'] ?? 0; // Giá trị mặc định là 0
$limit = $_POST['limit'] ?? 10; // Số lượng bài viết mỗi trang

getFriendPosts($userId, $page, $limit)
?>
