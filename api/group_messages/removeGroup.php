<?php
include_once '../../lib/DatabaseConnection.php';

function removeGroup($groupId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    try {
        $checkExistSql = "SELECT * FROM groups WHERE group_id =?";
        $checkExistStmt = $conn->prepare($checkExistSql);
        $checkExistStmt->bind_param("i", $groupId);
        $checkExistStmt->execute();
        $existResult = $checkExistStmt->get_result();

        if ($existResult->num_rows > 0) {
            $removeSql = "DELETE FROM groups WHERE group_id = ?";
            $removeStmt = $conn->prepare($removeSql);
            $removeStmt->bind_param("i", $groupId);
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

if ($groupId) {
    removeGroup($groupId);
} else {
    echo json_encode(["success" => false, "message" => "Thiếu thông tin nhóm hoặc người dùng"]);
}
?>
