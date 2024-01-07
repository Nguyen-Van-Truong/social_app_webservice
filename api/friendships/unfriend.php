<?php
include_once '../../lib/DatabaseConnection.php';

function unfriend($requestingUserId, $targetUserId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Check if there is a friendship between the two users
    $checkStmt = $conn->prepare("SELECT * FROM friendships WHERE 
                                (user_id1 = ? AND user_id2 = ? AND status = 'accepted') 
                                OR 
                                (user_id1 = ? AND user_id2 = ? AND status = 'accepted')");
    $checkStmt->bind_param("iiii", $requestingUserId, $targetUserId, $targetUserId, $requestingUserId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Not friends"]);
        return;
    }

    // If they are friends, proceed to unfriend
    $stmt = $conn->prepare("DELETE FROM friendships WHERE 
                            (user_id1 = ? AND user_id2 = ?) 
                            OR 
                            (user_id1 = ? AND user_id2 = ?)");
    $stmt->bind_param("iiii", $requestingUserId, $targetUserId, $targetUserId, $requestingUserId);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Friendship ended successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to end friendship"]);
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $requestingUserId = isset($_POST['requestingUserId']) ? (int) $_POST['requestingUserId'] : 0;
    $targetUserId = isset($_POST['targetUserId']) ? (int) $_POST['targetUserId'] : 0;

    unfriend($requestingUserId, $targetUserId);
}
?>
