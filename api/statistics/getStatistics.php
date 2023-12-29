<?php
include_once '../../lib/DatabaseConnection.php';

function getStatistics() {
    $db = new DatabaseConnection();
    $conn = $db->connect();

    try {
        // Total number of users
        $totalUsersSql = "SELECT COUNT(*) as totalUsers FROM users";
        $totalUsersStmt = $conn->query($totalUsersSql);
        $totalUsers = $totalUsersStmt->fetch_assoc()['totalUsers'];

        // Total number of users with role 'user'
        $totalRoleUserSql = "SELECT COUNT(*) as totalRoleUser FROM users WHERE role = 'user'";
        $totalRoleUserStmt = $conn->query($totalRoleUserSql);
        $totalRoleUser = $totalRoleUserStmt->fetch_assoc()['totalRoleUser'];

        // Total number of users with role 'admin'
        $totalRoleAdminSql = "SELECT COUNT(*) as totalRoleAdmin FROM users WHERE role = 'admin'";
        $totalRoleAdminStmt = $conn->query($totalRoleAdminSql);
        $totalRoleAdmin = $totalRoleAdminStmt->fetch_assoc()['totalRoleAdmin'];

        // Total number of posts
        $totalPostsSql = "SELECT COUNT(*) as totalPosts FROM posts";
        $totalPostsStmt = $conn->query($totalPostsSql);
        $totalPosts = $totalPostsStmt->fetch_assoc()['totalPosts'];

        // User with the most posts and the number of posts
        $mostActiveUserSql = "SELECT u.user_id, u.username, COUNT(p.post_id) as postCount FROM posts p JOIN users u ON p.user_id = u.user_id GROUP BY p.user_id ORDER BY postCount DESC LIMIT 1";
        $mostActiveUserStmt = $conn->query($mostActiveUserSql);
        $mostActiveUserData = $mostActiveUserStmt->fetch_assoc();

        // User with the most followers
        $mostFollowedUserSql = "SELECT u.user_id, u.username, COUNT(f.following_id) as followerCount FROM followers f JOIN users u ON f.following_id = u.user_id GROUP BY f.following_id ORDER BY followerCount DESC LIMIT 1";
        $mostFollowedUserStmt = $conn->query($mostFollowedUserSql);
        $mostFollowedUserData = $mostFollowedUserStmt->fetch_assoc();

        // User with the most friends
        $mostFriendsUserSql = "SELECT u.user_id, u.username, (SELECT COUNT(*) FROM friendships WHERE user_id1 = u.user_id OR user_id2 = u.user_id) as friendCount FROM users u ORDER BY friendCount DESC LIMIT 1";
        $mostFriendsUserStmt = $conn->query($mostFriendsUserSql);
        $mostFriendsUserData = $mostFriendsUserStmt->fetch_assoc();


        // Preparing response
        $response = [
            "success" => true,
            "statistics" => [
                "total_users" => $totalUsers,
                "total_users_with_role_user" => $totalRoleUser,
                "total_users_with_role_admin" => $totalRoleAdmin,
                "total_posts" => $totalPosts,
                "most_active_user" => [
                    "user_id" => $mostActiveUserData['user_id'],
                    "username" => $mostActiveUserData['username'],
                    "post_count" => $mostActiveUserData['postCount']
                ],
                "most_followed_user" => [
                    "user_id" => $mostFollowedUserData['user_id'],
                    "username" => $mostFollowedUserData['username'],
                    "follower_count" => $mostFollowedUserData['followerCount']
                ],
                "user_with_most_friends" => [
                    "user_id" => $mostFriendsUserData['user_id'],
                    "username" => $mostFriendsUserData['username'],
                    "friend_count" => $mostFriendsUserData['friendCount']
                ]
            ]
        ];
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Có lỗi xảy ra: " . $e->getMessage()]);
    } finally {
        $db->close();
    }
}

header('Content-Type: application/json; charset=utf-8');
getStatistics();
?>
