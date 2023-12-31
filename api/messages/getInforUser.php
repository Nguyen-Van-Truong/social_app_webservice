<?php
include_once '../../lib/DatabaseConnection.php';

function getInforUser($userId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT u.username, m.file_url FROM users u LEFT JOIN medias m ON u.profile_image_id = m.media_id WHERE u.user_id = ?;");

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $friends = array();
    while ($row = $result->fetch_assoc()) {
        array_push($friends, $row);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "user_info" => $friends]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = isset($_GET['userId']) ? (int) $_GET['userId'] : 0;
    getInforUser($userId);
}
?>
