<?php
include_once '../../lib/DatabaseConnection.php';

function getFriendNotInGroup($userId, $groupId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();


    $stmt = $conn->prepare("SELECT u.user_id, u.username, m.file_url
    FROM users u
    JOIN friendships f ON (u.user_id = f.user_id1 OR u.user_id = f.user_id2) LEFT JOIN medias m ON m.media_id =  u.profile_image_id
    WHERE ((f.user_id1 = ? AND u.user_id != ?) OR (f.user_id2 = ? AND u.user_id != ?))
      AND f.status = 'accepted'
      AND u.user_id NOT IN (
        SELECT gm.user_id
        FROM group_members gm
        WHERE gm.group_id = ?
      );");

    $stmt->bind_param("iiiii", $userId, $userId,$userId,$userId, $groupId);
    $stmt->execute();
    $result = $stmt->get_result();

    $friends = array();
    while ($row = $result->fetch_assoc()) {
        array_push($friends, $row);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "friends" => $friends]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = isset($_GET['userId']) ? (int) $_GET['userId'] : 0;
    $groupId = isset($_GET['groupId']) ? (int) $_GET['groupId'] : 0;

    getFriendNotInGroup($userId, $groupId);
}
?>
