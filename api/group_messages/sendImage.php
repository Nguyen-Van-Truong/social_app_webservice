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

function sendImage($senderId, $groupId, $message)
{
    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Start transaction
    $conn->begin_transaction();

    $mediaId = null;
    $fileUrl ="";
    // Handle media upload
    if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $file = $_FILES['media'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = $senderId . '_' . time() . '.' . $fileExtension; // Using userId and timestamp
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
    }

    // Insert comment
    $stmt = $conn->prepare("INSERT INTO group_messages (group_id, sender_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $groupId, $senderId, $message);
    $stmt->execute();

    $message_id = $conn->insert_id;


    $stmtLink = $conn->prepare("INSERT INTO group_message_medias (group_message_id, media_id) VALUES (?, ?)");
    $stmtLink->bind_param("ii", $message_id, $mediaId);
    if (!$stmtLink->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to link media to post"]);
        $conn->rollback();
        return;
    }

    if ($stmtLink->affected_rows > 0) {
        $conn->commit();
        echo json_encode(["success" => true, "message" => $fileUrl]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to post comment"]);
        $conn->rollback();
    }
    $stmt->close();
    $stmtLink->close();
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $senderId = isset($_POST['senderId']) ? (int)$_POST['senderId'] : 0;
    $groupId = isset($_POST['groupId']) ? (int)$_POST['groupId'] : 0;
    $message = isset($_POST['message']) ? $_POST['message'] : '';

    sendImage($senderId, $groupId, $message);
}
?>
