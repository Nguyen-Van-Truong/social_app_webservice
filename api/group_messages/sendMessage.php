<?php
include_once '../../lib/DatabaseConnection.php';

function sendMessageToGroup($groupId, $senderId, $message) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    try {
        // Kiểm tra xem người gửi có phải là thành viên của nhóm không
        $memberCheckSql = "SELECT * FROM group_members WHERE group_id = ? AND user_id = ?";
        $memberCheckStmt = $conn->prepare($memberCheckSql);
        $memberCheckStmt->bind_param("ii", $groupId, $senderId);
        $memberCheckStmt->execute();
        $memberResult = $memberCheckStmt->get_result();

        if ($memberResult->num_rows == 0) {
            echo json_encode(["success" => false, "message" => "Người dùng không phải là thành viên của nhóm"]);
            return;
        }

        // Chèn tin nhắn vào cơ sở dữ liệu
        $insertMessageSql = "INSERT INTO group_messages (group_id, sender_id, message) VALUES (?, ?, ?)";
        $insertMessageStmt = $conn->prepare($insertMessageSql);
        $insertMessageStmt->bind_param("iis", $groupId, $senderId, $message);
        $insertMessageStmt->execute();

        if ($insertMessageStmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Tin nhắn đã được gửi"]);
        } else {
            echo json_encode(["success" => false, "message" => "Không thể gửi tin nhắn"]);
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
$senderId = isset($_POST['senderId']) ? (int)$_POST['senderId'] : null;
$message = isset($_POST['message']) ? $_POST['message'] : '';

// Kiểm tra dữ liệu đầu vào
if ($groupId && $senderId && $message) {
    sendMessageToGroup($groupId, $senderId, $message);
} else {
    echo json_encode(["success" => false, "message" => "Dữ liệu đầu vào không hợp lệ"]);
}
?>
