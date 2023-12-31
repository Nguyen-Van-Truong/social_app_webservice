<?php
include_once '../../lib/DatabaseConnection.php';

function getAllAppSettings() {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT * FROM app_settings");
    $stmt->execute();
    $result = $stmt->get_result();

    $settings = array();
    while($row = $result->fetch_assoc()) {
        $settings[] = $row;
    }

    echo json_encode(["success" => true, "settings" => $settings]);

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

getAllAppSettings();
?>
