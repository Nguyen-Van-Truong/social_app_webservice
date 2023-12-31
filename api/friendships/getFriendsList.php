<?php
include_once '../../lib/DatabaseConnection.php';

function getFriendsList($userId, $sortOrder, $page = 0, $limit = 10) {
    if (!is_numeric($userId) || $userId < 0) {
        echo json_encode(["success" => false, "message" => "User ID không hợp lệ"]);
        return;
    }

    $db = new DatabaseConnection();
    $conn = $db->connect();

    $orderClause = $sortOrder == "recent" ? "DESC" : "ASC";
    $offset = $page * $limit;

    $sql = "SELECT * FROM friendships WHERE (user_id1 = ? OR user_id2 = ?) AND status = 'accepted' ORDER BY created_at $orderClause LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $userId, $userId, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $friends = array();
    while ($row = $result->fetch_assoc()) {
        $friendUserId = $row['user_id1'] == $userId ? $row['user_id2'] : $row['user_id1'];

        $stmt2 = $conn->prepare("SELECT u.*, m.file_url as profile_image_url FROM users u LEFT JOIN medias m ON u.profile_image_id = m.media_id WHERE u.user_id = ?");
        $stmt2->bind_param("i", $friendUserId);
        $stmt2->execute();
        $friendInfo = $stmt2->get_result()->fetch_assoc();

        if ($friendInfo) {
            $friendData = [
                'user_id' => $friendUserId,
                'username' => $friendInfo['username'],
                'profile_image_url' => $friendInfo['profile_image_url']
            ];
            array_push($friends, $friendData);
        }

        $stmt2->close();
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "friends" => $friends]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = isset($_GET['userId']) ? (int) $_GET['userId'] : 0;
    $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'recent';
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

    getFriendsList($userId, $sortOrder, $page, $limit);
}
?>
