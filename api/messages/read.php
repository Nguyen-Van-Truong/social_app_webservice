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

// Kiểm tra xem yêu cầu POST có tồn tại không
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy giá trị từ POST (có thể sử dụng các kiểm tra bảo mật tại đây)
    $user1 = isset($_POST['user1']) ? (int) $_POST['user1'] : 0;
    $user2 = isset($_POST['user2']) ? (int) $_POST['user2'] : 0;

    // Gọi hàm với các tham số
    getChatBetweenTwoUsers($user1, $user2);
}
?>
