<?php
include_once '../../lib/DatabaseConnection.php';

function getFriendsMessages($userId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT
                                M.sender_id,
                                M.receiver_id,
                                M.message,
                                M.created_at
                            FROM
                                messages M
                            JOIN
                                friendships F ON (M.sender_id = F.user_id1 AND M.receiver_id = F.user_id2)
                                             OR (M.sender_id = F.user_id2 AND M.receiver_id = F.user_id1)
                            WHERE
                                (F.user_id1 = ? OR F.user_id2 = ?)
                                AND F.status = 'accepted'
                                AND (M.sender_id = ? OR M.receiver_id = ?)
                            ORDER BY
                                M.created_at DESC");

    if (!$stmt) {
        echo json_encode(["success" => false, "error" => $conn->error]);
        return;
    }

    $stmt->bind_param("iiii", $userId, $userId, $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = array();
    while ($row = $result->fetch_assoc()) {
        array_push($messages, $row);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "messages" => $messages]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : 0;
    if ($userId > 0) {
        getFriendsMessages($userId);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid user ID"]);
    }
}
?>
