<?php
include_once '../../lib/DatabaseConnection.php';
include_once '../../lib/PasswordManager.php';

function changePassword($email, $oldPassword, $newPassword) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Check the current password
    $currentPasswordStmt = $conn->prepare("SELECT password_hash FROM users WHERE email = ?");
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

    if (!password_verify($oldPassword, $currentPasswordHash)) {
        echo json_encode(["success" => false, "message" => "Incorrect old password."]);
        return;
    }

    if (password_verify($newPassword, $currentPasswordHash)) {
        echo json_encode(["success" => false, "message" => "New password must be different from old password."]);
        return;
    }

    // Hash the new password
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    $stmt->bind_param("ss", $newPasswordHash, $email);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Password changed successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to change password."]);
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

$email = $_POST['email'] ?? '';
$oldPassword = $_POST['oldPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

changePassword($email, $oldPassword, $newPassword);
?>
