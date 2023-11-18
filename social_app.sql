/*
 Navicat MySQL Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100428 (10.4.28-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : social_app

 Target Server Type    : MySQL
 Target Server Version : 100428 (10.4.28-MariaDB)
 File Encoding         : 65001

 Date: 18/11/2023 09:38:07
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for comments
-- ----------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments`  (
  `comment_id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NULL DEFAULT NULL,
  `user_id` int NULL DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`comment_id`) USING BTREE,
  INDEX `comments_ibfk_1`(`post_id` ASC) USING BTREE,
  INDEX `comments_ibfk_2`(`user_id` ASC) USING BTREE,
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 20 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of comments
-- ----------------------------
INSERT INTO `comments` VALUES (1, 1, 1, 'Bình luận mẫu 1', '2023-11-17 10:27:09');
INSERT INTO `comments` VALUES (2, 2, 2, 'Bình luận mẫu 2', '2023-11-17 10:27:09');
INSERT INTO `comments` VALUES (3, 3, 3, 'Bình luận mẫu 3', '2023-11-17 10:27:09');
INSERT INTO `comments` VALUES (4, 1, 1, 'Bình luận mẫu 1', '2023-11-17 10:27:27');
INSERT INTO `comments` VALUES (5, 2, 2, 'Bình luận mẫu 2', '2023-11-17 10:27:27');
INSERT INTO `comments` VALUES (6, 3, 3, 'Bình luận mẫu 3', '2023-11-17 10:27:27');
INSERT INTO `comments` VALUES (14, 1, 1, 'con ga the gioi', '2023-11-17 21:34:17');
INSERT INTO `comments` VALUES (16, 1, 1, 'con ga the gioi', '2023-11-17 21:34:23');
INSERT INTO `comments` VALUES (18, 17, 1, 'con ga the gioi', '2023-11-17 21:35:16');
INSERT INTO `comments` VALUES (19, 1, 1, 'hay', '2023-11-17 21:58:54');

-- ----------------------------
-- Table structure for followers
-- ----------------------------
DROP TABLE IF EXISTS `followers`;
CREATE TABLE `followers`  (
  `follower_id` int NOT NULL,
  `following_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`follower_id`, `following_id`) USING BTREE,
  INDEX `followers_ibfk_2`(`following_id` ASC) USING BTREE,
  CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `followers_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of followers
-- ----------------------------
INSERT INTO `followers` VALUES (1, 2, '2023-11-17 10:25:43');
INSERT INTO `followers` VALUES (2, 3, '2023-11-17 10:25:43');
INSERT INTO `followers` VALUES (3, 1, '2023-11-17 10:25:43');

-- ----------------------------
-- Table structure for friendships
-- ----------------------------
DROP TABLE IF EXISTS `friendships`;
CREATE TABLE `friendships`  (
  `friendship_id` int NOT NULL AUTO_INCREMENT,
  `user_id1` int NULL DEFAULT NULL,
  `user_id2` int NULL DEFAULT NULL,
  `status` enum('requested','accepted','declined','blocked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`friendship_id`) USING BTREE,
  INDEX `friendships_ibfk_1`(`user_id1` ASC) USING BTREE,
  INDEX `friendships_ibfk_2`(`user_id2` ASC) USING BTREE,
  CONSTRAINT `friendships_ibfk_1` FOREIGN KEY (`user_id1`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `friendships_ibfk_2` FOREIGN KEY (`user_id2`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of friendships
-- ----------------------------
INSERT INTO `friendships` VALUES (1, 1, 2, 'accepted', '2023-11-17 10:25:43');
INSERT INTO `friendships` VALUES (2, 2, 3, 'accepted', '2023-11-17 10:25:43');
INSERT INTO `friendships` VALUES (3, 3, 1, 'requested', '2023-11-17 10:25:43');
INSERT INTO `friendships` VALUES (7, 1, 4, 'accepted', '2023-11-17 21:45:47');

-- ----------------------------
-- Table structure for group_comments
-- ----------------------------
DROP TABLE IF EXISTS `group_comments`;
CREATE TABLE `group_comments`  (
  `comment_id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`comment_id`) USING BTREE,
  INDEX `post_id`(`post_id` ASC) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  CONSTRAINT `group_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `group_posts` (`post_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `group_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of group_comments
-- ----------------------------

-- ----------------------------
-- Table structure for group_members
-- ----------------------------
DROP TABLE IF EXISTS `group_members`;
CREATE TABLE `group_members`  (
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` enum('member','admin','moderator') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`group_id`, `user_id`) USING BTREE,
  INDEX `group_members_ibfk_2`(`user_id` ASC) USING BTREE,
  CONSTRAINT `group_members_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `group_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of group_members
-- ----------------------------
INSERT INTO `group_members` VALUES (1, 1, 'admin', '2023-11-17 10:25:43');
INSERT INTO `group_members` VALUES (1, 2, 'member', '2023-11-17 10:25:43');
INSERT INTO `group_members` VALUES (1, 3, 'member', '2023-11-18 09:26:16');
INSERT INTO `group_members` VALUES (1, 4, 'member', '2023-11-18 09:25:56');

-- ----------------------------
-- Table structure for group_messages
-- ----------------------------
DROP TABLE IF EXISTS `group_messages`;
CREATE TABLE `group_messages`  (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NULL DEFAULT NULL,
  `sender_id` int NULL DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `retracted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`message_id`) USING BTREE,
  INDEX `group_messages_ibfk_1`(`group_id` ASC) USING BTREE,
  INDEX `group_messages_ibfk_2`(`sender_id` ASC) USING BTREE,
  CONSTRAINT `group_messages_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `group_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of group_messages
-- ----------------------------
INSERT INTO `group_messages` VALUES (1, 1, 1, 'Tin nhắn nhóm mẫu 1', 0, '2023-11-17 10:25:43');
INSERT INTO `group_messages` VALUES (2, 1, 2, 'Tin nhắn nhóm mẫu 2', 0, '2023-11-17 10:25:43');
INSERT INTO `group_messages` VALUES (3, 2, 3, 'Tin nhắn nhóm mẫu 3', 0, '2023-11-17 10:25:43');
INSERT INTO `group_messages` VALUES (4, 1, 1, 'xin chao ca nhom', 0, '2023-11-18 08:38:10');
INSERT INTO `group_messages` VALUES (9, 1, 2, 'xin chao ca nhom', 0, '2023-11-18 08:40:35');
INSERT INTO `group_messages` VALUES (10, 1, 1, 'xin chao ca nhom', 0, '2023-11-18 08:41:47');

-- ----------------------------
-- Table structure for group_posts
-- ----------------------------
DROP TABLE IF EXISTS `group_posts`;
CREATE TABLE `group_posts`  (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`) USING BTREE,
  INDEX `group_id`(`group_id` ASC) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  CONSTRAINT `group_posts_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `group_posts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of group_posts
-- ----------------------------

-- ----------------------------
-- Table structure for groups
-- ----------------------------
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups`  (
  `group_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  `type` enum('chat','post') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'chat',
  PRIMARY KEY (`group_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of groups
-- ----------------------------
INSERT INTO `groups` VALUES (1, 'Group 1', 'Description of Group 1', '2023-11-14 22:22:31', 'chat');
INSERT INTO `groups` VALUES (2, 'Group 2', 'Description of Group 2', '2023-11-14 22:22:31', 'chat');
INSERT INTO `groups` VALUES (3, 'Group 3', 'Description of Group 3', '2023-11-14 22:22:31', 'chat');

-- ----------------------------
-- Table structure for likes
-- ----------------------------
DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes`  (
  `user_id` int NOT NULL,
  `post_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`user_id`, `post_id`) USING BTREE,
  INDEX `likes_ibfk_2`(`post_id` ASC) USING BTREE,
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of likes
-- ----------------------------
INSERT INTO `likes` VALUES (1, 1, '2023-11-17 20:21:41');
INSERT INTO `likes` VALUES (1, 2, '2023-11-17 20:21:38');
INSERT INTO `likes` VALUES (1, 5, '2023-11-17 22:08:30');
INSERT INTO `likes` VALUES (2, 1, '2023-11-17 20:21:30');
INSERT INTO `likes` VALUES (3, 4, '2023-11-17 20:36:12');

-- ----------------------------
-- Table structure for messages
-- ----------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages`  (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NULL DEFAULT NULL,
  `receiver_id` int NULL DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `retracted` tinyint(1) NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`message_id`) USING BTREE,
  INDEX `messages_ibfk_1`(`sender_id` ASC) USING BTREE,
  INDEX `messages_ibfk_2`(`receiver_id` ASC) USING BTREE,
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 36 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of messages
-- ----------------------------
INSERT INTO `messages` VALUES (1, 1, 2, 'Tin nhắn mẫu 1', 0, '2023-11-17 10:25:58');
INSERT INTO `messages` VALUES (2, 2, 3, 'Tin nhắn mẫu 2', 0, '2023-11-17 10:25:58');
INSERT INTO `messages` VALUES (3, 3, 1, 'Tin nhắn mẫu 3', 0, '2023-11-17 10:25:58');
INSERT INTO `messages` VALUES (4, 2, 3, 'Tin nhắn mẫu 2', 0, '2023-11-17 10:25:58');
INSERT INTO `messages` VALUES (5, 1, 2, 'Tin nhắn mẫu 1', 0, '2023-11-17 10:25:58');
INSERT INTO `messages` VALUES (34, 3, 2, 'ga32', 0, '2023-11-17 21:01:33');
INSERT INTO `messages` VALUES (35, 1, 3, 'xin chao ban, ban khoe khong', 0, '2023-11-17 22:10:57');

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications`  (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NULL DEFAULT NULL,
  `type` enum('like','comment','follow','message') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `reference_id` int NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`notification_id`) USING BTREE,
  INDEX `notifications_ibfk_1`(`user_id` ASC) USING BTREE,
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notifications
-- ----------------------------
INSERT INTO `notifications` VALUES (1, 1, 'like', 1, '2023-11-17 10:25:58');
INSERT INTO `notifications` VALUES (2, 2, 'comment', 2, '2023-11-17 10:25:58');
INSERT INTO `notifications` VALUES (3, 3, 'follow', 3, '2023-11-17 10:25:58');

-- ----------------------------
-- Table structure for password_resets
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_resets
-- ----------------------------
INSERT INTO `password_resets` VALUES (1, '20130187@st.hcmuaf.edu.vn', 355785, '2023-11-16 16:29:53');
INSERT INTO `password_resets` VALUES (2, '20130187@st.hcmuaf.edu.vn', 142475, '2023-11-16 16:30:20');
INSERT INTO `password_resets` VALUES (3, '20130187@st.hcmuaf.edu.vn', 417434, '2023-11-16 16:30:22');
INSERT INTO `password_resets` VALUES (4, '20130187@st.hcmuaf.edu.vn', 670111, '2023-11-16 17:08:57');
INSERT INTO `password_resets` VALUES (5, '20130187@st.hcmuaf.edu.vn', 914711, '2023-11-16 17:09:05');
INSERT INTO `password_resets` VALUES (6, '20130187@st.hcmuaf.edu.vn', 482152, '2023-11-16 17:09:12');

-- ----------------------------
-- Table structure for posts
-- ----------------------------
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts`  (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NULL DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `visible` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`post_id`) USING BTREE,
  INDEX `posts_ibfk_1`(`user_id` ASC) USING BTREE,
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of posts
-- ----------------------------
INSERT INTO `posts` VALUES (1, 1, 'adad', 'aaa', 1, '2023-11-16 17:22:09');
INSERT INTO `posts` VALUES (2, 2, 'adad', 'aaa', 1, '2023-11-16 17:22:32');
INSERT INTO `posts` VALUES (3, 2, 'adad', 'aaa', 1, '2023-11-16 17:32:01');
INSERT INTO `posts` VALUES (4, 2, 'adad', '', 1, '2023-11-16 17:32:05');
INSERT INTO `posts` VALUES (5, 2, 'adad', '', 1, '2023-11-16 17:32:11');
INSERT INTO `posts` VALUES (6, 2, 'adad', 'adad', 1, '2023-11-16 17:32:46');
INSERT INTO `posts` VALUES (7, 1, 'gagaga', '', 1, '2023-11-16 17:54:31');
INSERT INTO `posts` VALUES (8, 1, 'gagaga', '', 1, '2023-11-16 17:54:42');
INSERT INTO `posts` VALUES (9, 1, 'gagaga', 'uploads/20231114_081404.jpg', 1, '2023-11-16 17:54:52');
INSERT INTO `posts` VALUES (10, 1, 'haha', 'uploads/DNZqU0cUMAAxIHC.jpg', 1, '2023-11-16 17:55:43');
INSERT INTO `posts` VALUES (11, 1, 'haha', 'uploads/DNZqU0cUMAAxIHC.jpg', 1, '2023-11-16 17:56:05');
INSERT INTO `posts` VALUES (12, 4, 'hg4', 'uploads/1_1700132550.jpg', 1, '2023-11-16 18:02:30');
INSERT INTO `posts` VALUES (13, 4, 'hg4', 'uploads/1_1700132559.jpg', 1, '2023-11-16 18:02:39');
INSERT INTO `posts` VALUES (14, 1, 'hg', 'uploads/1_1700132563.jpg', 1, '2023-11-16 18:02:43');
INSERT INTO `posts` VALUES (15, 1, 'hg', 'uploads/1_1700132565.jpg', 1, '2023-11-16 18:02:45');
INSERT INTO `posts` VALUES (16, 1, 'hg', 'uploads/1_1700132580.jpg', 1, '2023-11-16 18:03:00');
INSERT INTO `posts` VALUES (17, 1, 'con ga con', 'uploads/1_1700140311.jpg', 1, '2023-11-16 20:11:51');
INSERT INTO `posts` VALUES (23, 22, 'bai viet ve con ga', '', 1, '2023-11-17 22:12:42');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'truong', 'male', 'truong@gmail.com', '$2y$10$4EpG1RRfogzT4klWzcryH.OxaozI4oYT2nUZNh2fe7K.j6nczw.d.', NULL, 'truongpro', '2023-11-15 13:35:22');
INSERT INTO `users` VALUES (2, 'truong', 'male', 'truong272@gmail.com', '$2y$10$M3dGeLvXpKIgeiHKBcUBt.p6GP5ZOVmdknpfE70NWNPgMJSKA9cT2', NULL, 'trtr', '2023-11-15 13:52:03');
INSERT INTO `users` VALUES (3, 'tr', 'female', 'truong123@gmail.com', '$2y$10$yul3HkmEk8dtRpOayNMIG.NLbwXZcfVCJny/2UHmRWPQ2LF1lcVaC', NULL, 'trtr', '2023-11-15 14:16:49');
INSERT INTO `users` VALUES (4, 'truongpro123', 'male', '20130187@st.hcmuaf.edu.vn', '$2y$10$yDJdm3fQ6IcUN9Ms3kSbY.1/ty1VM1ZvcA4bZuImbLQ7tairQp.Ie', NULL, 'account chinh', '2023-11-16 16:11:23');
INSERT INTO `users` VALUES (22, 'gacon1', 'male', 'ga@gmail.com', '$2y$10$9xOf4I7WA8BULUVqW.7bU.DQj7GWpdUTO0yNkepIVqT0tkOsx1ami', NULL, 'toi la con ga', '2023-11-17 22:16:43');

SET FOREIGN_KEY_CHECKS = 1;
