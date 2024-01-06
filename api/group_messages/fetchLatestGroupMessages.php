<?php
include_once '../../lib/DatabaseConnection.php';

function fetchLatestGroupMessages($groupId) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $response = array();
    $response["messages"] = array();

    // Query to fetch the latest messages for the group
    $stmt = $conn->prepare("SELECT gm.message_id, gm.group_id, gm.sender_id, gm.message, m.file_url, me.file_url as image
                            FROM group_messages gm
                            JOIN users u ON gm.sender_id = u.user_id 
                            LEFT JOIN medias m ON m.media_id = u.profile_image_id 
                            LEFT JOIN group_message_medias gmm ON gm.message_id = gmm.group_message_id
                            LEFT JOIN medias me ON me.media_id = gmm.media_id
                            WHERE gm.group_id = ? AND gm.retracted = 0
                            ORDER BY gm.created_at DESC LIMIT 10");
    $stmt->bind_param("i", $groupId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($response["messages"], $row);
        }
        $stmt->close();
    } else {
        echo "Error executing query.";
        return;
    }

    $db->close();
    header('Content-Type: application/json');
    echo json_encode($response);
}

header('Content-Type: application/json; charset=utf-8');

// Receive data from POST request
$groupId = isset($_POST['groupId']) ? (int)$_POST['groupId'] : null;

if ($groupId !== null) {
    fetchLatestGroupMessages($groupId);
} else {
    echo json_encode(["success" => false, "message" => "Missing group ID"]);
}
?>
