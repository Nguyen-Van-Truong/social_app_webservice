<?php
// unlikePost.php
include_once '../../lib/DatabaseConnection.php';

function unlikePost($userId, $postId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Remove the like entry
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $userId, $postId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Unliked post successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Could not unlike the post"]);
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

$userId = $_POST['userId'] ?? '';
$postId = $_POST['postId'] ?? '';

unlikePost($userId, $postId);
?>
