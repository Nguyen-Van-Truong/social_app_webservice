<?php

include_once '../../lib/DatabaseConnection.php';

function getFileType($extension) {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $videoExtensions = ['mp4', 'avi', 'mov', 'wmv'];
    $documentExtensions = ['pdf', 'doc', 'docx', 'txt'];

    if (in_array($extension, $imageExtensions)) {
        return 'image';
    } elseif (in_array($extension, $videoExtensions)) {
        return 'video';
    } elseif (in_array($extension, $documentExtensions)) {
        return 'document';
    } else {
        return 'document';
    }
}

function createGroup($userId, $nameGroup, $description) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Start transaction
    $conn->begin_transaction();

    // Insert group
    $stmtGroup = $conn->prepare("INSERT INTO `groups` (name, description, created_at) VALUES (?, ?, NOW())");
    if ($stmtGroup === false) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }

    $stmtGroup->bind_param("ss", $nameGroup, $description);

    if (!$stmtGroup->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to create group"]);
        $conn->rollback();
        return;
    }

    $groupId = $conn->insert_id;
    $stmtGroup->close();

    // Insert user as a group member and admin
    $stmtLink = $conn->prepare("INSERT INTO group_members (group_id, user_id, role, joined_at) VALUES (?, ?, 'admin', NOW())");
    $stmtLink->bind_param("ii", $groupId, $userId);
    if (!$stmtLink->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to link user to group"]);
        $conn->rollback();
        return;
    }
    $stmtLink->close();

    // Check if multiple files are uploaded
    if (isset($_FILES['image'])) {
        foreach ($_FILES['image']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['image']['error'][$key] == 0) {
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'][$key], PATHINFO_EXTENSION));
                $fileName = $userId . '_' . time() . '_' . $key . '.' . $fileExtension;
                $fileTmpName = $_FILES['image']['tmp_name'][$key];
                $fileDestination = '../../uploads/groups_messages/' . $fileName;
                $fileSize = $_FILES['image']['size'][$key];
                $fileType = getFileType($fileExtension);

                move_uploaded_file($fileTmpName, $fileDestination);
                $fileUrl = 'uploads/groups_messages/' . $fileName;

                // Insert into medias table
                $stmtMedia = $conn->prepare("INSERT INTO medias (file_url, file_type, file_size, uploaded_at) VALUES (?, ?, ?, NOW())");
                $stmtMedia->bind_param("ssi", $fileUrl, $fileType, $fileSize);
                if (!$stmtMedia->execute()) {
                    echo json_encode(["success" => false, "message" => "Failed to upload image"]);
                    $conn->rollback();
                    return;
                }

                $mediaId = $conn->insert_id;
                $stmtMedia->close();

                // Update group's profile image with the first uploaded image
                if ($key == 0) {
                    $stmtUpdateGroup = $conn->prepare("UPDATE groups SET profile_image_id = ? WHERE group_id = ?");
                    $stmtUpdateGroup->bind_param("ii", $mediaId, $groupId);
                    if (!$stmtUpdateGroup->execute()) {
                        echo json_encode(["success" => false, "message" => "Failed to update group's profile image"]);
                        $conn->rollback();
                        return;
                    }
                    $stmtUpdateGroup->close();
                }
            }
        }
    }

    // Commit transaction
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Group created successfully", "group_id" => $groupId]);

    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

// Receive data from POST request
$userId = $_POST['userId'] ?? '';
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
createGroup($userId, $name, $description);

?>
