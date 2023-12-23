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

function createPost($userId, $content, $visible) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Start transaction
    $conn->begin_transaction();

    // Insert post
    $stmtPost = $conn->prepare("INSERT INTO posts (user_id, content, visible, created_at) VALUES (?, ?, ?, NOW())");
    $stmtPost->bind_param("isi", $userId, $content, $visible);
    if (!$stmtPost->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to create post"]);
        $conn->rollback();
        return;
    }
    $postId = $conn->insert_id; // Retrieve the newly created post's ID
    $stmtPost->close();

    // Check if multiple files are uploaded
    if (isset($_FILES['image'])) {
        foreach ($_FILES['image']['name'] as $key => $name) {
            if ($_FILES['image']['error'][$key] == 0) {
                $fileExtension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $fileName = $userId . '_' . time() . '_' . $key . '.' . $fileExtension;
                $fileTmpName = $_FILES['image']['tmp_name'][$key];
                $fileDestination = '../../uploads/posts/' . $fileName;
                $fileSize = $_FILES['image']['size'][$key];
                $fileType = getFileType($fileExtension);

                move_uploaded_file($fileTmpName, $fileDestination);
                $fileUrl = 'uploads/posts/' . $fileName;

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

                // Link media to post
                if ($mediaId !== null) {
                    $stmtLink = $conn->prepare("INSERT INTO post_medias (post_id, media_id) VALUES (?, ?)");
                    $stmtLink->bind_param("ii", $postId, $mediaId);
                    if (!$stmtLink->execute()) {
                        echo json_encode(["success" => false, "message" => "Failed to link media to post"]);
                        $conn->rollback();
                        return;
                    }
                    $stmtLink->close();
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
$content = $_POST['content'] ?? '';
$visible = $_POST['visible'] ?? 1;

createPost($userId, $content, $visible);
?>
