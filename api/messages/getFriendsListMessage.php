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
        LM.latest_message_time,
        LM.latest_message
    FROM
        friendships F
    JOIN
        users U ON F.user_id1 = U.user_id OR F.user_id2 = U.user_id
    LEFT JOIN
        medias M ON U.profile_image_id = M.media_id
    LEFT JOIN
        (
            SELECT
                message_data.friend_id,
                message_data.latest_message_time,
                M2.message AS latest_message
            FROM
                (
                    SELECT
                        IF(sender_id = ?, receiver_id, sender_id) AS friend_id,
                        MAX(created_at) AS latest_message_time
                    FROM
                        messages
                    WHERE
                        sender_id = ? OR receiver_id = ?
                    GROUP BY
                        friend_id
                ) AS message_data
            JOIN messages M2 ON (M2.sender_id = message_data.friend_id OR M2.receiver_id = message_data.friend_id)
                            AND M2.created_at = message_data.latest_message_time
        ) LM ON U.user_id = LM.friend_id
    WHERE
        (F.user_id1 = ? OR F.user_id2 = ?)
        AND F.status = 'accepted'
        AND U.user_id != ?
    ORDER BY 
        LM.latest_message_time $orderClause");

    $stmt->bind_param("iiiiii", $userId, $userId, $userId, $userId, $userId, $userId);
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
