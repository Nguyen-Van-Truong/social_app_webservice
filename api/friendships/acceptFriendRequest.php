<?php
include_once '../../lib/DatabaseConnection.php';

function acceptFriendRequest($currentUser, $requestSender) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Check if the friend request exists, is in 'requested' state, and currentUser is the recipient
    $checkStmt = $conn->prepare("SELECT * FROM friendships WHERE user_id1 = ? AND user_id2 = ? AND status = 'requested'");
    $checkStmt->bind_param("ii", $requestSender, $currentUser);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "No pending friend request found or unauthorized"]);
        return;
    }

    // Update the status of the friendship to 'accepted'
    $updateStmt = $conn->prepare("UPDATE friendships SET status = 'accepted' WHERE user_id1 = ? AND user_id2 = ? AND status = 'requested'");
    $updateStmt->bind_param("ii", $requestSender, $currentUser);
    if ($updateStmt->execute()) {
        echo json_encode(["success" => true, "message" => "Friend request accepted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to accept friend request"]);
    }

    $checkStmt->close();
    $updateStmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentUser = isset($_POST['currentUser']) ? (int) $_POST['currentUser'] : 0;
    $requestSender = isset($_POST['requestSender']) ? (int) $_POST['requestSender'] : 0;

    acceptFriendRequest($currentUser, $requestSender);
}
?>
