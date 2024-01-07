<?php
include_once '../../lib/DatabaseConnection.php';

function setAppThemeColor($color) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Check if the theme color setting already exists
    $stmt = $conn->prepare("SELECT setting_value FROM app_settings WHERE setting_name = 'theme_color'");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update the existing theme color setting
        $stmt = $conn->prepare("UPDATE app_settings SET setting_value = ? WHERE setting_name = 'theme_color'");
        $stmt->bind_param("s", $color);
        $success = $stmt->execute();
    } else {
        // Insert a new theme color setting
        $stmt = $conn->prepare("INSERT INTO app_settings (setting_name, setting_value) VALUES ('theme_color', ?)");
        $stmt->bind_param("s", $color);
        $success = $stmt->execute();
    }

    if ($success) {
        echo json_encode(["success" => true, "message" => "Theme color updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update theme color"]);
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

$color = $_POST['color'] ?? '';

if ($color) {
    setAppThemeColor($color);
} else {
    echo json_encode(["success" => false, "message" => "No color provided"]);
}
?>
