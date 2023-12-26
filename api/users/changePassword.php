<?php
include_once '../../lib/DatabaseConnection.php';

function changePassword($email, $newPassword) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Retrieve the current password hash
    $currentPasswordStmt = $conn->prepare("SELECT password_hash FROM users WHERE email = ?");
    if (!$currentPasswordStmt) {
        echo json_encode(["success" => false, "message" => "Database error."]);
        return;
    }
    $currentPasswordStmt->bind_param("s", $email);
    $currentPasswordStmt->execute();
    $result = $currentPasswordStmt->get_result();
    $currentPasswordStmt->close();

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "User not found."]);
        return;
    }

    $row = $result->fetch_assoc();
    $currentPasswordHash = $row['password_hash'];

    // Check if the new password is the same as the current password
    if (password_verify($newPassword, $currentPasswordHash)) {
        echo json_encode(["success" => false, "message" => "The new password must be different from the current password."]);
        return;
    }

    // Hash the new password
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "Database error."]);
        return;
    }
    $stmt->bind_param("ss", $newPasswordHash, $email);
    $result = $stmt->execute();

    if ($result) {
        echo json_encode(["success" => true, "message" => "Password changed successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to change password."]);
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

$email = $_POST['email'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

changePassword($email, $newPassword);
?>
