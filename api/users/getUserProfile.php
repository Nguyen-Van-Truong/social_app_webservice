<?php
include_once '../../lib/DatabaseConnection.php';

function getUserProfile($viewerId, $profileOwnerId, $page = 0, $limit = 10) {
    if (!is_numeric($profileOwnerId) || $profileOwnerId <= 0) {
        echo json_encode(["success" => false, "message" => "Invalid user ID"]);
        return;
    }

    $db = new DatabaseConnection();
    $conn = $db->connect();

    // Fetch user profile data with additional fields
    $userStmt = $conn->prepare("SELECT u.user_id, u.username, u.gender, u.email, u.bio, 
                                       m1.file_url as profile_image, m2.file_url as background_image,
                                       u.last_activity_at, u.status,
                                       (SELECT COUNT(*) FROM friendships WHERE user_id1 = ? OR user_id2 = ?) as friendCount,
                                       (SELECT COUNT(*) FROM followers WHERE following_id = ?) as followerCount
                                FROM users u
                                LEFT JOIN medias m1 ON u.profile_image_id = m1.media_id
                                LEFT JOIN medias m2 ON u.background_image_id = m2.media_id
                                WHERE u.user_id = ?");
    $userStmt->bind_param("iiii", $profileOwnerId, $profileOwnerId, $profileOwnerId, $profileOwnerId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userStmt->close();

    if ($userResult->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "User not found"]);
        return;
    }

    $userData = $userResult->fetch_assoc();

    // Determine the friendship status between the viewer and profile owner
// Determine the friendship status and who sent the friend request
    $friendshipStatus = 'none'; // default status
    $requestSender = null; // this will hold the ID of the friend request sender
    if ($viewerId != $profileOwnerId) {
        $friendshipStmt = $conn->prepare("SELECT status, user_id1 FROM friendships WHERE (user_id1 = ? AND user_id2 = ?) OR (user_id1 = ? AND user_id2 = ?)");
        $friendshipStmt->bind_param("iiii", $viewerId, $profileOwnerId, $profileOwnerId, $viewerId);
        $friendshipStmt->execute();
        $friendshipResult = $friendshipStmt->get_result();
        if ($row = $friendshipResult->fetch_assoc()) {
            $friendshipStatus = $row['status'];
            if ($friendshipStatus === 'requested') {
                // Determine who sent the friend request
                $requestSender = $row['user_id1'];
            }
        }
        $friendshipStmt->close();
    }
    $userData['friendship_status'] = $friendshipStatus;
    $userData['request_sender'] = $requestSender;


    // Fetch user's posts with pagination
    $offset = $page * $limit;
    $postSql = "SELECT p.*, 
                        (SELECT COUNT(*) FROM comments WHERE post_id = p.post_id) AS commentCount,
                        (SELECT GROUP_CONCAT(m.file_url SEPARATOR ', ') 
                            FROM post_medias pm JOIN medias m ON pm.media_id = m.media_id 
                            WHERE pm.post_id = p.post_id) AS media_urls,
                        (SELECT COUNT(*) FROM likes WHERE post_id = p.post_id) AS likeCount,
                        EXISTS(SELECT 1 FROM likes WHERE user_id = ? AND post_id = p.post_id) AS isLiked
                FROM posts p
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
    $postStmt = $conn->prepare($postSql);
    $postStmt->bind_param("iiii", $userId, $profileOwnerId, $limit, $offset);
    $postStmt->execute();
    $postResult = $postStmt->get_result();
    $postStmt->close();

    $posts = array();
    while ($postRow = $postResult->fetch_assoc()) {
        $mediaUrls = explode(', ', $postRow['media_urls']);
        $postRow['media_urls'] = $mediaUrls;
        $posts[] = $postRow;
    }

    // Combine user data and posts
    $profileData = [
        "user_info" => $userData,
        "posts" => $posts
    ];

    echo json_encode(["success" => true, "data" => $profileData]);
    $db->close();
}

header('Content-Type: application/json; charset=utf-8');

$viewerId = $_POST['viewerId'] ?? 0;
$profileOwnerId = $_POST['userId'] ?? 0;
$page = isset($_POST['page']) ? (int)$_POST['page'] : 0;
$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;

getUserProfile($viewerId, $profileOwnerId, $page, $limit);
?>
