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

function postComment($postId, $userId, $comment)
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
        $fileDestination = '../../uploads/comments/' . $fileName;
        $fileSize = filesize($fileTmpName);
        $fileType = getFileType($fileExtension);

        move_uploaded_file($fileTmpName, $fileDestination);
        $fileUrl = 'uploads/comments/' . $fileName;

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
    }

    // Insert comment
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, media_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $postId, $userId, $comment, $mediaId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Commit transaction
        $conn->commit();
        echo json_encode(["success" => true, "message" => "Comment posted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to post comment"]);
        $conn->rollback();
    }

    $stmt->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $postId = isset($_POST['postId']) ? (int)$_POST['postId'] : 0;
    $userId = isset($_POST['userId']) ? (int)$_POST['userId'] : 0;
    $comment = isset($_POST['comment']) ? $_POST['comment'] : '';

    postComment($postId, $userId, $comment);
}
?>
