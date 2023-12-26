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
        return 'document'; // Default to 'document' if the type is unknown
    }
}

function updateImageGroup($userId, $nameGroup, $description) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Start transaction
    $conn->begin_transaction();

    // Check if multiple files are uploaded
    if (isset($_FILES['image'])) {
        foreach ($_FILES['image']['name'] as $key => $name) {
            if ($_FILES['image']['error'][$key] == 0) {

                $fileExtension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
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

                if ($mediaId !== null) {
                    // Insert group
                   $stmtGroup = $conn->prepare("INSERT INTO groups (name, description, created_at, profile_image_id) VALUES (?, ?, NOW(), ?)");
                   $stmtGroup->bind_param("ssi", $nameGroup, $description, $mediaId );
   
                   if (!$stmtGroup->execute()) {
                    echo json_encode(["success" => false, "message" => "Failed to create post"]);
                    $conn->rollback();
                    return;
                   }
                $groupId = $conn->insert_id;
                $stmtGroup->close();

                if ($groupId !== null) {
                    $stmtLink = $conn->prepare("INSERT INTO group_members (group_id, user_id, joined_at) VALUES (?, ?, NOW())");
                    $stmtLink->bind_param("ii", $groupId, $userId);
                    if (!$stmtLink->execute()) {
                        echo json_encode(["success" => false, "message" => "Failed to link media to post"]);
                        $conn->rollback();
                        return;
                    }
                    $stmtLink->close();
                }
                $stmtUpdateRole = $conn->prepare("UPDATE group_members SET role = 'admin' WHERE group_id = ? AND user_id = ?");
                $stmtUpdateRole->bind_param("ii", $groupId, $userId);
                if (!$stmtUpdateRole->execute()) {
                    echo json_encode(["success" => false, "message" => "Failed to update role to admin"]);
                    $conn->rollback();
                    return;
                }
                $stmtUpdateRole->close();
                }
                
            }
        }
    }

    // Commit transaction
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Post created successfully"]);

    $db->close();
}


header('Content-Type: application/json; charset=utf-8');

// Receive data from POST request
$userId = $_POST['userId'] ?? '';
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
updateImageGroup($userId, $name, $description);
?>