<?php
include_once '../../lib/DatabaseConnection.php';

function getMember( $groupId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();


    $stmt = $conn->prepare("SELECT u.user_id, u.username, m.file_url, gm.role
    FROM users u
    JOIN group_members gm ON u.user_id = gm.user_id LEFT JOIN medias m ON m.media_id = u.profile_image_id
    WHERE gm.group_id = ?;");

    $stmt->bind_param("i",  $groupId);
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
    $groupId = isset($_GET['groupId']) ? (int) $_GET['groupId'] : 0;

    getMember($groupId);
}
?>
