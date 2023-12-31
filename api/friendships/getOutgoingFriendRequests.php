<?php
include_once '../../lib/DatabaseConnection.php';

function getOutgoingFriendRequests($userId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $sql = "SELECT u.user_id, u.username, u.email, m.file_url as profile_image_url
            FROM friendships f
            JOIN users u ON u.user_id = f.user_id2
            LEFT JOIN medias m ON u.profile_image_id = m.media_id
            WHERE f.user_id1 = ? AND f.status = 'requested'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $requests = array();
    while ($row = $result->fetch_assoc()) {
        array_push($requests, $row);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "outgoing_requests" => $requests]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = isset($_GET['userId']) ? (int) $_GET['userId'] : 0;
    getOutgoingFriendRequests($userId);
}
?>
