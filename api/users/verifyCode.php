<?php
include_once '../../lib/DatabaseConnection.php';

function verifyCode($email, $code) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Retrieve the most recent code for the email, created within the last 5 minutes
    $stmt = $conn->prepare("SELECT code FROM password_resets WHERE email = ? AND created_at > (NOW() - INTERVAL 5 MINUTE) ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $retrievedCode = $row['code'];

        // Verify if the provided code matches the retrieved code
        if ($code == $retrievedCode) {
            echo json_encode(["success" => true, "message" => "Code verified successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid code."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid or expired code."]);
    }
    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

$email = $_POST['email'] ?? '';
$code = $_POST['code'] ?? '';

verifyCode($email, $code);
?>
