<?php
include_once '../../lib/DatabaseConnection.php';

function getFileType($extension) {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($extension, $imageExtensions)) {
        return 'image';
    } else {
        return 'other'; // Default to 'other' if the type is not an image
    }
}

function updateUserProfile($userId, $username, $bio) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Start transaction
    $conn->begin_transaction();

    // Update user information, excluding email
    $stmtUser = $conn->prepare("UPDATE users SET username = ?, bio = ? WHERE user_id = ?");
    $stmtUser->bind_param("ssi", $username, $bio, $userId);
    if (!$stmtUser->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to update user profile"]);
        $conn->rollback();
        return;
    }
    $stmtUser->close();

    // Check if an image file is uploaded
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $fileExtension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $fileName = $userId . '_profile_' . time() . '.' . $fileExtension;
        $fileTmpName = $_FILES['profile_image']['tmp_name'];
        $fileDestination = '../../uploads/profiles/' . $fileName;
        $fileSize = $_FILES['profile_image']['size'];
        $fileType = getFileType($fileExtension);

        move_uploaded_file($fileTmpName, $fileDestination);
        $fileUrl = 'uploads/profiles/' . $fileName;

        // Insert into medias table
        $stmtMedia = $conn->prepare("INSERT INTO medias (file_url, file_type, file_size, uploaded_at) VALUES (?, ?, ?, NOW())");
        $stmtMedia->bind_param("ssi", $fileUrl, $fileType, $fileSize);
        if (!$stmtMedia->execute()) {
            echo json_encode(["success" => false, "message" => "Failed to upload profile image"]);
            $conn->rollback();
            return;
        }

        $mediaId = $conn->insert_id;
        $stmtMedia->close();

        // Update user's profile image
        $stmtUpdateImage = $conn->prepare("UPDATE users SET profile_image_id = ? WHERE user_id = ?");
        $stmtUpdateImage->bind_param("ii", $mediaId, $userId);
        if (!$stmtUpdateImage->execute()) {
            echo json_encode(["success" => false, "message" => "Failed to update profile image"]);
            $conn->rollback();
            return;
        }
        $stmtUpdateImage->close();
    }

    // Commit transaction
    $conn->commit();
    echo json_encode(["success" => true, "message" => "User profile updated successfully"]);

    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

// Receive data from POST request
$userId = $_POST['userId'] ?? 0;
$username = $_POST['username'] ?? '';
$bio = $_POST['bio'] ?? '';

updateUserProfile($userId, $username, $bio);
?>
