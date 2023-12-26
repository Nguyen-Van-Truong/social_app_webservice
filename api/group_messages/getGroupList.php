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
    gm_last_message.message AS last_message
FROM 
    group_members gm
JOIN 
    groups g ON gm.group_id = g.group_id
LEFT JOIN 
    medias m ON g.profile_image_id = m.media_id
LEFT JOIN 
    group_messages gm_last_message ON gm_last_message.group_id = g.group_id
    AND gm_last_message.created_at = (
        SELECT 
            MAX(created_at)
        FROM 
            group_messages gm
        WHERE 
            gm.group_id = g.group_id
    )
WHERE 
    gm.user_id = ?
 ORDER BY g.created_at $orderClause");

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $friends = array();
    while ($row = $result->fetch_assoc()) {
        array_push($friends, $row);
    }

    $stmt->close();
    $db->close();

    echo json_encode(["success" => true, "groups" => $friends]);
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $userId = isset($_GET['userId']) ? (int) $_GET['userId'] : 0;
    $sortOrder = isset($_GET['sortOrder']) ? $_GET['sortOrder'] : 'recent';

    getGroupList($userId, $sortOrder);
}
?>
