<?php
include_once '../../lib/DatabaseConnection.php';

function sendFriendRequest($requestSender, $requestReceiver) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Check if the friend request already exists
    $checkStmt = $conn->prepare("SELECT * FROM friendships WHERE (user_id1 = ? AND user_id2 = ?) OR (user_id1 = ? AND user_id2 = ?)");
    $checkStmt->bind_param("iiii", $requestSender, $requestReceiver, $requestReceiver, $requestSender);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Friend request already sent or exists"]);
        return;
    }

    // Insert new friend request
    $stmt = $conn->prepare("INSERT INTO friendships (user_id1, user_id2, status) VALUES (?, ?, 'requested')");
    $stmt->bind_param("ii", $requestSender, $requestReceiver);
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
    $requestSender = isset($_POST['requestSender']) ? (int) $_POST['requestSender'] : 0;
    $requestReceiver = isset($_POST['requestReceiver']) ? (int) $_POST['requestReceiver'] : 0;

    sendFriendRequest($requestSender, $requestReceiver);
}
?>
