<?php
include_once '../../lib/DatabaseConnection.php';

function getFriendPosts($userId, $page = 0, $limit = 10) {
    if (!is_numeric($userId) || $userId < 0) {
        echo json_encode(["success" => false, "message" => "User ID không hợp lệ"]);
        return;
    }

    $db = new DatabaseConnection();
    $conn = $db->connect();

    try {
        // Fetch friends' user IDs
        $sql = "SELECT user_id1, user_id2 FROM friendships WHERE user_id1 = ? OR user_id2 = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $friendIds = array();
        while ($row = $result->fetch_assoc()) {
            $friendIds[] = ($row['user_id1'] == $userId) ? $row['user_id2'] : $row['user_id1'];
        }
        $friendIds[] = $userId; // Include current user's ID

        // Fetch posts and usernames
        $placeholders = implode(',', array_fill(0, count($friendIds), '?'));
        $offset = $page * $limit;
        $sql = "SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.user_id WHERE p.user_id IN ($placeholders) ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $params = array_merge($friendIds, [$limit, $offset]);
        $stmt->bind_param(str_repeat('i', count($friendIds)) . 'ii', ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $posts = array();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $postId = $row['post_id'];

                // Fetch media for each post
                $mediaSql = "SELECT m.file_url FROM medias m JOIN post_medias pm ON m.media_id = pm.media_id WHERE pm.post_id = ?";
                $mediaStmt = $conn->prepare($mediaSql);
                $mediaStmt->bind_param("i", $postId);
                $mediaStmt->execute();
                $mediaResult = $mediaStmt->get_result();

                $commentCountSql = "SELECT COUNT(*) as commentCount FROM comments WHERE post_id = ?";
                $commentCountStmt = $conn->prepare($commentCountSql);
                $commentCountStmt->bind_param("i", $postId);
                $commentCountStmt->execute();
                $commentCountResult = $commentCountStmt->get_result()->fetch_assoc();
                $row['commentCount'] = $commentCountResult['commentCount'];

                $mediaUrls = array();
                while ($mediaRow = $mediaResult->fetch_assoc()) {
                    $mediaUrls[] = $mediaRow['file_url'];
                }
                $row['media_urls'] = $mediaUrls;

                // Check if the current user has liked the post
                $likeCheckSql = "SELECT COUNT(*) as likeCount, EXISTS(SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?) as isLiked FROM likes WHERE post_id = ?";
                $likeCheckStmt = $conn->prepare($likeCheckSql);
                $likeCheckStmt->bind_param("iii", $userId, $postId, $postId);
                $likeCheckStmt->execute();
                $likeCheckResult = $likeCheckStmt->get_result()->fetch_assoc();

                $row['isLiked'] = $likeCheckResult['isLiked'];
                $row['likeCount'] = $likeCheckResult['likeCount'];
                $row['username'] = $row['username']; // Add username

                $posts[] = $row;
                $mediaStmt->close();
            }

            echo json_encode(["success" => true, "posts" => $posts]);
        } else {
            echo json_encode(["success" => false, "message" => "Không thể lấy danh sách bài viết của bạn bè"]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Có lỗi xảy ra: " . $e->getMessage()]);
    } finally {
        $db->close();
    }
}

header('Content-Type: application/json; charset=utf-8');

$userId = $_POST['userId'] ?? '';
$page = $_POST['page'] ?? 0;
$limit = $_POST['limit'] ?? 10;

getFriendPosts($userId, $page, $limit);
?>
