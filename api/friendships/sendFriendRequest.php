<?php
include_once '../../lib/DatabaseConnection.php';

function sendFriendRequest($user1, $user2) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Kiểm tra xem yêu cầu kết bạn đã tồn tại hay chưa
    $checkStmt = $conn->prepare("SELECT * FROM friendships WHERE (user_id1 = ? AND user_id2 = ?) OR (user_id1 = ? AND user_id2 = ?)");
    $checkStmt->bind_param("iiii", $user1, $user2, $user2, $user1);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Friend request already sent or exists"]);
        return;
    }

    $stmt = $conn->prepare("INSERT INTO friendships (user_id1, user_id2, status) VALUES (?, ?, 'requested')");
    $stmt->bind_param("ii", $user1, $user2);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Friend request sent successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to send friend request"]);
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user1 = isset($_POST['user1']) ? (int) $_POST['user1'] : 0;
    $user2 = isset($_POST['user2']) ? (int) $_POST['user2'] : 0;

    sendFriendRequest($user1, $user2);
}
?>
