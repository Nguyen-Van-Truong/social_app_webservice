<?php
include_once '../../lib/DatabaseConnection.php';

function acceptFriendRequest($userId, $friendId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Cập nhật trạng thái yêu cầu kết bạn
    $stmt = $conn->prepare("UPDATE friendships SET status = 'accepted' WHERE (user_id1 = ? AND user_id2 = ? AND status = 'requested') OR (user_id1 = ? AND user_id2 = ? AND status = 'requested')");
    $stmt->bind_param("iiii", $friendId, $userId, $userId, $friendId);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Friend request accepted"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to accept friend request or request does not exist"]);
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = isset($_POST['userId']) ? (int) $_POST['userId'] : 0;
    $friendId = isset($_POST['friendId']) ? (int) $_POST['friendId'] : 0;

    acceptFriendRequest($userId, $friendId);
}
?>
