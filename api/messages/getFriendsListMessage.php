<?php
include_once '../../lib/DatabaseConnection.php';

function getFriendsListMessage($userId, $sortOrder) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $orderClause = $sortOrder == "recent" ? "DESC" : "ASC";
    $stmt = $conn->prepare("SELECT U.user_id, U.username, M.file_url FROM friendships F JOIN users U ON F.user_id1 = U.user_id OR F.user_id2 = U.user_id LEFT JOIN medias M ON U.profile_image_id = M.media_id WHERE F.status = 'accepted' AND (F.user_id1 = ? OR F.user_id2 = ?) AND U.user_id != ? ORDER BY F.created_at $orderClause");

    $stmt->bind_param("iii", $userId, $userId, $userId);
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
    $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'recent';

    getFriendsListMessage($userId, $sortOrder);
}
?>
