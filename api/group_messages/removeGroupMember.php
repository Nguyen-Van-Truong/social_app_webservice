<?php
include_once '../../lib/DatabaseConnection.php';

function removeGroupMember($groupId, $userId) {
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
            $removeSql = "DELETE FROM group_members WHERE group_id = ? AND user_id = ?";
            $removeStmt = $conn->prepare($removeSql);
            $removeStmt->bind_param("ii", $groupId, $userId);
            $removeStmt->execute();
            $conn->commit();
        }
        echo json_encode(["success" => true, "message" => "successfully" . $conn->error]);
        $db->close();
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Lỗi máy chủ: " . $e->getMessage()]);
    } 
}

header('Content-Type: application/json; charset=utf-8');

// Nhận dữ liệu từ POST request
$groupId = isset($_POST['groupId']) ? (int)$_POST['groupId'] : null;
$userId = isset($_POST['userId']) ? (int)$_POST['userId'] : null;

if ($groupId && $userId) {
    removeGroupMember($groupId, $userId);
} else {
    echo json_encode(["success" => false, "message" => "Thiếu thông tin nhóm hoặc người dùng"]);
}
?>
