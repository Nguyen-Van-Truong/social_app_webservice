<?php
include_once '../../lib/DatabaseConnection.php';

function getSuggestedFriends($userId, $sortOrder, $page = 0, $limit = 10) {
    if (!is_numeric($userId) || $userId < 0) {
        echo json_encode(["success" => false, "message" => "User ID không hợp lệ"]);
        return;
    }

    $db = new DatabaseConnection();
    $conn = $db->connect();

    $offset = $page * $limit;

    // Determine the order clause based on the sortOrder parameter
    $orderClause = "";
    if ($sortOrder == "most_mutual") {
        $orderClause = "ORDER BY mutual_friends DESC, u.username ASC";
    } elseif ($sortOrder == "least_mutual") {
        $orderClause = "ORDER BY mutual_friends ASC, u.username ASC";
    } else {
        $orderClause = "ORDER BY u.created_at ASC"; // Default order
    }

    $suggestedFriendsSql = "
        SELECT u.user_id, u.username, m.file_url as profile_image_url, COUNT(f.user_id1 + f.user_id2) as mutual_friends
        FROM users u 
        LEFT JOIN medias m ON u.profile_image_id = m.media_id
        LEFT JOIN friendships f ON (f.user_id1 = u.user_id OR f.user_id2 = u.user_id) AND f.status = 'accepted'
        WHERE u.user_id != ? AND NOT EXISTS (
            SELECT 1 FROM friendships
            WHERE (friendships.user_id1 = ? AND friendships.user_id2 = u.user_id) 
            OR (friendships.user_id2 = ? AND friendships.user_id1 = u.user_id)
        )
        GROUP BY u.user_id
        $orderClause
        LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($suggestedFriendsSql);
    $stmt->bind_param("iiiii", $userId, $userId, $userId, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $suggestedFriends = array();
    while ($row = $result->fetch_assoc()) {
        $suggestedFriendData = [
            'user_id' => $row['user_id'],
            'name' => $row['username'],
            'profile_image_url' => $row['profile_image_url'],
            'mutual_friends' => $row['mutual_friends']
        ];
        array_push($suggestedFriends, $suggestedFriendData);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "suggested_friends" => $suggestedFriends]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = isset($_GET['userId']) ? (int) $_GET['userId'] : 0;
    $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'most_mutual';
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 0;
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;

    getSuggestedFriends($userId, $sortOrder, $page, $limit);
}
?>
