<?php
include_once '../../lib/DatabaseConnection.php';

function getGroupInfo($groupId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Enclose the table name 'groups' in backticks
    $stmt = $conn->prepare("SELECT * FROM `groups` g LEFT JOIN medias m ON g.profile_image_id = m.media_id WHERE g.group_id = ?");

    // Check if prepare was successful
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => $conn->error]);
        return;
    }

    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $result = $stmt->get_result();

    $groupInfo = array();
    while ($row = $result->fetch_assoc()) {
        array_push($groupInfo, $row);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "groups_info" => $groupInfo]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $groupId = isset($_GET['groupId']) ? (int) $_GET['groupId'] : 0;
    getGroupInfo($groupId);
}
?>
