<?php
include_once '../../lib/DatabaseConnection.php';

function addGroupMember($groupId, $userId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    try {
        // Kiểm tra xem người dùng đã là thành viên của nhóm chưa
        $checkExistSql = "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?";
        $checkExistStmt = $conn->prepare($checkExistSql);
        $checkExistStmt->bind_param("ii", $groupId, $userId);
        $checkExistStmt->execute();
        $existResult = $checkExistStmt->get_result();

        if ($existResult->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "Người dùng đã là thành viên của nhóm"]);
            return;
        }

        // Thêm người dùng vào nhóm
        $insertSql = "INSERT INTO group_members (group_id, user_id) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ii", $groupId, $userId);
        $insertStmt->execute();

        if ($insertStmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Người dùng đã được thêm vào nhóm"]);
        } else {
            echo json_encode(["success" => false, "message" => "Không thể thêm người dùng vào nhóm"]);
        }
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

if ($groupId && $userId) {
    addGroupMember($groupId, $userId);
} else {
    echo json_encode(["success" => false, "message" => "Thiếu thông tin nhóm hoặc người dùng"]);
}
?>
