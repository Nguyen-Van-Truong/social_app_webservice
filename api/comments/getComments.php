<?php
include_once '../../lib/DatabaseConnection.php';

function getComments($postId, $sortOrder) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $orderClause = $sortOrder == "newest" ? "DESC" : "ASC";
    $stmt = $conn->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at $orderClause");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();

    $comments = array();
    while ($row = $result->fetch_assoc()) {
        array_push($comments, $row);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "comments" => $comments]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $postId = isset($_GET['postId']) ? (int) $_GET['postId'] : 0;
    $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'newest';

    getComments($postId, $sortOrder);
}
?>
