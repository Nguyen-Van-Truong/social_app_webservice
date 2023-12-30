<?php
include_once '../../lib/DatabaseConnection.php';

function notificationPost($userId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT p.post_id, p.user_id, p.content, u.username, m.file_url   FROM posts p LEFT JOIN users u ON u.user_id  = p.user_id LEFT JOIN medias m ON m.media_id = u.profile_image_id
    WHERE p.user_id IN (SELECT
        CASE
            WHEN user_id1 = ? THEN user_id2
            ELSE user_id1
        END AS friend_id
    FROM
        friendships
    WHERE
        (user_id1 = ? OR user_id2 = ?)
        AND status = 'accepted')
            ORDER BY p.created_at DESC;");

    $stmt->bind_param("iii", $userId, $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $friends = array();
    while ($row = $result->fetch_assoc()) {
        array_push($friends, $row);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "noti_post_friend" => $friends]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = isset($_GET['userId']) ? (int) $_GET['userId'] : 0;
    notificationPost($userId);
}
?>
