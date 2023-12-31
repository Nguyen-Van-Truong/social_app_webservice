<?php
include_once '../../lib/DatabaseConnection.php';

function getRoleInGroup( $groupId, $userId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();


    $stmt = $conn->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");

    $stmt->bind_param("ii",  $groupId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $friends = array();
    while ($row = $result->fetch_assoc()) {
        array_push($friends, $row);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "data" => $friends]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $groupId = isset($_GET['groupId']) ? (int) $_GET['groupId'] : 0;
    $userId = isset($_GET['userId']) ? (int) $_GET['userId'] : 0;
    getRoleInGroup($groupId, $userId);
}
?>
