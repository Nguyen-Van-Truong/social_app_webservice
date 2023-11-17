<?php
include_once '../../lib/DatabaseConnection.php';

function postComment($postId, $userId, $comment) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $postId, $userId, $comment);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Comment posted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to post comment"]);
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $postId = isset($_POST['postId']) ? (int) $_POST['postId'] : 0;
    $userId = isset($_POST['userId']) ? (int) $_POST['userId'] : 0;
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';

    postComment($postId, $userId, $comment);
}
?>
