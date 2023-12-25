<?php
include_once '../../lib/DatabaseConnection.php';

function getGroupInfo($groupId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT * FROM groups g LEFT JOIN medias m ON g.profile_image_id = m.media_id WHERE group_id = ?");

    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $result = $stmt->get_result();

    $friends = array();
    while ($row = $result->fetch_assoc()) {
        array_push($friends, $row);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "groups_info" => $friends]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $groupId = isset($_GET['groupId']) ? (int) $_GET['groupId'] : 0;
    getGroupInfo($groupId);
}
?>
