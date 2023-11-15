<?php
include_once '../../lib/DatabaseConnection.php';

function getChatBetweenTwoUsers($user1, $user2) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $response = array();
    $response["messages"] = array();

    $stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at");
    $stmt->bind_param("iiii", $user1, $user2, $user2, $user1);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            array_push($response["messages"], $row);
        }
        $stmt->close();
    } else {
        echo "Lỗi khi thực hiện truy vấn.";
        return;
    }

    $db->close();
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Ví dụ: Lấy chat giữa user 1 và user 2
getChatBetweenTwoUsers(1, 2);
?>
