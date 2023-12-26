<?php
include_once '../../lib/DatabaseConnection.php';

function getFileType($extension)
{
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

function updateImageGroup($postId, $userId, $comment)
{
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Start transaction
    $conn->begin_transaction();

    $mediaId = null;

    // Handle media upload
    if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $file = $_FILES['media'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = $userId . '_' . time() . '.' . $fileExtension; // Using userId and timestamp
        $fileTmpName = $file['tmp_name'];
        $fileDestination = '../../uploads/groups_messages/' . $fileName;
        $fileSize = filesize($fileTmpName);
        $fileType = getFileType($fileExtension);

        move_uploaded_file($fileTmpName, $fileDestination);
        $fileUrl = 'uploads/groups_messages/' . $fileName;

        // Insert into medias table
        $stmtMedia = $conn->prepare("INSERT INTO medias (file_url, file_type, file_size, uploaded_at) VALUES (?, ?, ?, NOW())");
        $stmtMedia->bind_param("ssi", $fileUrl, $fileType, $fileSize);
        if (!$stmtMedia->execute()) {
            echo json_encode(["success" => false, "message" => "Failed to upload media"]);
            $conn->rollback();
            return;
        }
        $mediaId = $conn->insert_id;
        $stmtMedia->close();

        if ($mediaId !== null) {
            $stmt = $conn->prepare("UPDATE groups SET profile_image_id = ? WHERE group_id = ?");
            $stmt->bind_param("ii", $mediaId,$postId);
            if(!$stmt->execute()){
                echo json_encode(["success" => false, "message" => "Failed to update image"]);
                $conn->rollback();
                    return;
            }
        }
    }

   
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Update image successfully"]);

    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $postId = isset($_POST['postId']) ? (int)$_POST['postId'] : 0;
    $userId = isset($_POST['userId']) ? (int)$_POST['userId'] : 0;
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';

    updateImageGroup($postId, $userId, $comment);
}
?>
