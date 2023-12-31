<?php
include_once '../../lib/DatabaseConnection.php';

function isMemberOfGroup($groupId, $userId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $sql = "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $groupId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $conn->close();

    return $result->num_rows > 0;
}

function getGroupMessages($groupId, $userId, $page = 0, $limit = 10) {
    // Kiểm tra xem người dùng có phải là thành viên của nhóm không
    if (!isMemberOfGroup($groupId, $userId)) {
        echo json_encode(["success" => false, "message" => "Người dùng không phải là thành viên của nhóm"]);
        return;
    }

    $db = new DatabaseConnection();
    $conn = $db->connect();

    try {
        // Xác định vị trí bắt đầu cho truy vấn phân trang
        $offset = $page * $limit;

        // Truy vấn để lấy tin nhắn
        $sql = "SELECT gm.message_id, gm.group_id, gm.sender_id, gm.message, m.file_url , me.file_url as image
        FROM group_messages gm JOIN users u ON gm.sender_id = u.user_id 
        LEFT JOIN medias m ON m.media_id = u.profile_image_id 
        LEFT JOIN group_message_medias gmm ON gm.message_id = gmm.group_message_id
        LEFT JOIN medias me ON me.media_id = gmm.media_id
        WHERE group_id = ? AND retracted = 0 ORDER BY gm.created_at ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = array();
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }

        echo json_encode(["success" => true, "messages" => $messages]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi máy chủ: " . $e->getMessage()]);
    } finally {
        $conn->close();
    }
}

header('Content-Type: application/json; charset=utf-8');

// Nhận dữ liệu từ POST request
$groupId = isset($_POST['groupId']) ? (int)$_POST['groupId'] : null;
$userId = isset($_POST['userId']) ? (int)$_POST['userId'] : null;
$page = isset($_POST['page']) ? (int)$_POST['page'] : 0;
$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;

if ($groupId && $userId) {
    getGroupMessages($groupId, $userId, $page, $limit);
} else {
    echo json_encode(["success" => false, "message" => "Thiếu thông tin nhóm hoặc người dùng"]);
}
?>
