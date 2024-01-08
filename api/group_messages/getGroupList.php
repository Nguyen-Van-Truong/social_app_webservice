<?php
include_once '../../lib/DatabaseConnection.php';

function getGroupList($userId, $sortOrder) {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    $orderClause = $sortOrder == "recent" ? "DESC" : "ASC";
    $stmt = $conn->prepare("SELECT 
    g.group_id,
    g.name,
    m.file_url,
    (SELECT gm_inner.message 
     FROM group_messages gm_inner
     WHERE gm_inner.group_id = g.group_id 
     ORDER BY gm_inner.created_at DESC LIMIT 1) AS last_message
FROM 
    group_members gm
JOIN 
    `groups` g ON gm.group_id = g.group_id
LEFT JOIN 
    medias m ON g.profile_image_id = m.media_id
WHERE 
    gm.user_id = ?
ORDER BY 
    g.created_at $orderClause");


    if (!$stmt) {
        echo json_encode(["success" => false, "error" => $conn->error]);
        return;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $groups = array();
    while ($row = $result->fetch_assoc()) {
        array_push($groups, $row);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "groups" => $groups]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : 0;
    $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'recent';

    if ($userId > 0) {
        getGroupList($userId, $sortOrder);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid user ID"]);
    }
}
?>
