<?php
include_once '../../lib/DatabaseConnection.php';

function cancelOrRejectFriendRequest($userId, $otherUserId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Check if there is a pending friend request in either direction
    $checkStmt = $conn->prepare("SELECT * FROM friendships WHERE 
        (user_id1 = ? AND user_id2 = ? AND status = 'requested') OR 
        (user_id2 = ? AND user_id1 = ? AND status = 'requested')");
    $checkStmt->bind_param("iiii", $userId, $otherUserId, $userId, $otherUserId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "No pending friend request found"]);
        return;
    }

    // If there is a pending request, delete it
    $stmt = $conn->prepare("DELETE FROM friendships WHERE 
        (user_id1 = ? AND user_id2 = ? AND status = 'requested') OR 
        (user_id2 = ? AND user_id1 = ? AND status = 'requested')");
    $stmt->bind_param("iiii", $userId, $otherUserId, $userId, $otherUserId);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Friend request cancelled/rejected successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to cancel/reject friend request"]);
    }

    $stmt->close();
    $checkStmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = isset($_POST['userId']) ? (int) $_POST['userId'] : 0;
    $otherUserId = isset($_POST['otherUserId']) ? (int) $_POST['otherUserId'] : 0;

    cancelOrRejectFriendRequest($userId, $otherUserId);
}
?>
