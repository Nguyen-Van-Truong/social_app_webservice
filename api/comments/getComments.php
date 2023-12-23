<?php
include_once '../../lib/DatabaseConnection.php';

function getComments($postId, $sortOrder) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $orderClause = $sortOrder == "newest" ? "DESC" : "ASC";

    // Join comments table with users and medias table to fetch user and media details
    $stmt = $conn->prepare("SELECT c.comment_id, c.post_id, c.user_id, u.username, c.comment, c.created_at, 
                            c.is_edited, c.edited_at, c.is_retracted, c.retracted_at, c.media_id, 
                            m.file_url, m.file_type, m.file_size 
                            FROM comments c
                            LEFT JOIN users u ON c.user_id = u.user_id
                            LEFT JOIN medias m ON c.media_id = m.media_id
                            WHERE c.post_id = ? 
                            ORDER BY c.created_at $orderClause");

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
