<?php
include_once '../../lib/DatabaseConnection.php';

function getFriendsList($userId, $sortOrder) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $orderClause = $sortOrder == "recent" ? "DESC" : "ASC";
    $stmt = $conn->prepare("SELECT * FROM friendships WHERE (user_id1 = ? OR user_id2 = ?) AND status = 'accepted' ORDER BY created_at $orderClause");
    $stmt->bind_param("ii", $userId, $userId);
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

    getFriendsList($userId, $sortOrder);
}
?>
