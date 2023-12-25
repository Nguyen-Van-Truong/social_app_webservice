<?php
include_once '../../lib/DatabaseConnection.php';

function getFriendsListMessage($userId, $sortOrder) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $orderClause = $sortOrder == "recent" ? "DESC" : "ASC";
    $stmt = $conn->prepare("SELECT
    U.user_id,
    U.username,
    M.file_url,
    MAX(MS.created_at) AS latest_message_time,
    MS.message AS latest_message
FROM
    friendships F
JOIN
    users U ON F.user_id1 = U.user_id OR F.user_id2 = U.user_id
LEFT JOIN
    medias M ON U.profile_image_id = M.media_id
LEFT JOIN
    (
        SELECT
            sender_id,
            receiver_id,
            created_at,
            message
        FROM
            messages
        WHERE
            (sender_id = ? OR receiver_id = ?)
            AND (sender_id, receiver_id, created_at) IN (
                SELECT
                    sender_id,
                    receiver_id,
                    MAX(created_at) AS created_at
                FROM
                    messages
                WHERE
                    sender_id = ?
                    OR receiver_id = ?
                GROUP BY
                    sender_id, receiver_id
            )
    ) MS ON (MS.sender_id = U.user_id AND MS.receiver_id = ?)
             OR (MS.sender_id = ? AND MS.receiver_id = U.user_id)
WHERE
    (F.user_id1 = ? OR F.user_id2 = ?)
    AND F.status = 'accepted'
    AND U.user_id != ?
GROUP BY
    U.user_id, U.username, M.file_url
 ORDER BY F.created_at $orderClause");

    $stmt->bind_param("iiiiiiiii", $userId, $userId, $userId,$userId, $userId, $userId, $userId,$userId, $userId);
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
