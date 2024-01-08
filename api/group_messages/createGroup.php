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

function createGroup($userId, $nameGroup, $description) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $conn->begin_transaction();

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

    $stmtLink = $conn->prepare("INSERT INTO group_members (group_id, user_id, role, joined_at) VALUES (?, ?, 'admin', NOW())");
    $stmtLink->bind_param("ii", $groupId, $userId);
    if (!$stmtLink->execute()) {
        echo json_encode(["success" => false, "message" => "Failed to link user to group"]);
        $conn->rollback();
        return;
    }
    $stmtLink->close();

    if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $file = $_FILES['media'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = $userId . '_' . time() . '.' . $fileExtension;
        $fileTmpName = $file['tmp_name'];
        $fileDestination = '../../uploads/groups_messages/' . $fileName;
        $fileSize = $_FILES['media']['size'];
        $fileType = getFileType($fileExtension);

        move_uploaded_file($fileTmpName, $fileDestination);
        $fileUrl = 'uploads/groups_messages/' . $fileName;

        $stmtMedia = $conn->prepare("INSERT INTO medias (file_url, file_type, file_size, uploaded_at) VALUES (?, ?, ?, NOW())");
        $stmtMedia->bind_param("ssi", $fileUrl, $fileType, $fileSize);
        if (!$stmtMedia->execute()) {
            echo json_encode(["success" => false, "message" => "Failed to upload media"]);
            $conn->rollback();
            return;
        }

        $mediaId = $conn->insert_id;
        $stmtMedia->close();

        $stmtUpdateGroup = $conn->prepare("UPDATE `groups` SET profile_image_id = ? WHERE group_id = ?");
        $stmtUpdateGroup->bind_param("ii", $mediaId, $groupId);
        if (!$stmtUpdateGroup->execute()) {
            echo json_encode(["success" => false, "message" => "Failed to update group's profile image"]);
            $conn->rollback();
            return;
        }
        $stmtUpdateGroup->close();
    }

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Group created successfully", "group_id" => $groupId]);

    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

$userId = $_POST['userId'] ?? '';
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
createGroup($userId, $name, $description);
?>
