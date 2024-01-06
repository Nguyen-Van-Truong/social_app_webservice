<?php
include_once '../../lib/DatabaseConnection.php';

function fetchLatestMessage($user1, $user2) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $response = array();
    $response["messages"] = array();

    // Query to fetch the most recent message between the two users
    $stmt = $conn->prepare("SELECT m.message_id, m.sender_id, m.receiver_id, m.message, m.retracted, m.created_at, m.status, m.retracted_at, m.deleted_at, me.media_id, me.file_url, me.file_type, me.file_size, me.uploaded_at FROM messages m LEFT JOIN message_medias mm ON m.message_id = mm.message_id LEFT JOIN medias me ON mm.media_id = me.media_id WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) ORDER BY m.created_at DESC LIMIT 1");
    $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
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

// Check for POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user1 = isset($_POST['user1']) ? (int)$_POST['user1'] : 0;
    $user2 = isset($_POST['user2']) ? (int)$_POST['user2'] : 0;

    // Call the function with parameters
    fetchLatestMessage($user1, $user2);
}
?>
