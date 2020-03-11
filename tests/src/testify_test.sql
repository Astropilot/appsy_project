SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


DROP TABLE IF EXISTS `tf_faq`;
CREATE TABLE `tf_faq` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tf_forum_post`;
CREATE TABLE `tf_forum_post` (
  `id` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `title` varchar(70) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL,
  `category` int(11) NOT NULL,
  `post_response` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tf_forum_category`;
CREATE TABLE `tf_forum_category` (
  `id` int(11) NOT NULL,
  `title` varchar(70) NOT NULL,
  `description` varchar(150) NOT NULL,
  `display_order` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tf_message`;
CREATE TABLE `tf_message` (
  `id` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `recipient` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tf_ticket_comment`;
CREATE TABLE `tf_ticket_comment` (
  `id` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `content` text NOT NULL,
  `ticket` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tf_ticket`;
CREATE TABLE `tf_ticket` (
  `id` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `title` varchar(70) NOT NULL,
  `content` text NOT NULL,
  `status` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tf_user`;
CREATE TABLE `tf_user` (
  `id` int(11) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password` varchar(64) NOT NULL,
  `lastname` varchar(75) NOT NULL,
  `firstname` varchar(75) NOT NULL,
  `role` int(11) NOT NULL,
  `banned` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `tf_user_invited`;
CREATE TABLE `tf_user_invited` (
  `id` int(11) NOT NULL,
  `email` varchar(254) NOT NULL,
  `firstname` varchar(75) NOT NULL,
  `lastname` varchar(75) NOT NULL,
  `role` int(11) NOT NULL,
  `invite_token` varchar(64) NOT NULL,
  `expire_date` date NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `tf_faq`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tf_forum_category`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `tf_forum_post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_forumpost_forumcategory` (`category`),
  ADD KEY `FK_forumpost_forumpost` (`post_response`),
  ADD KEY `FK_forumpost_user` (`author`);

ALTER TABLE `tf_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_message_user` (`author`),
  ADD KEY `FK_message_user2` (`recipient`);

ALTER TABLE `tf_ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_ticket_user` (`author`);

ALTER TABLE `tf_ticket_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ticket_comment_ticket` (`ticket`),
  ADD KEY `fk_ticket_comment_user` (`author`);

ALTER TABLE `tf_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`email`);

ALTER TABLE `tf_user_invited`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invite_token` (`invite_token`);


ALTER TABLE `tf_faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tf_forum_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tf_forum_post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tf_message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tf_ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tf_ticket_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tf_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tf_user_invited`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `tf_forum_post`
  ADD CONSTRAINT `FK_forumpost_forumcategory` FOREIGN KEY (`category`) REFERENCES `tf_forum_category` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_forumpost_forumpost` FOREIGN KEY (`post_response`) REFERENCES `tf_forum_post` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_forumpost_user` FOREIGN KEY (`author`) REFERENCES `tf_user` (`id`) ON DELETE CASCADE;

ALTER TABLE `tf_message`
  ADD CONSTRAINT `FK_message_user` FOREIGN KEY (`author`) REFERENCES `tf_user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_message_user2` FOREIGN KEY (`recipient`) REFERENCES `tf_user` (`id`) ON DELETE CASCADE;

ALTER TABLE `tf_ticket`
  ADD CONSTRAINT `FK_ticket_user` FOREIGN KEY (`author`) REFERENCES `tf_user` (`id`) ON DELETE CASCADE;

ALTER TABLE `tf_ticket_comment`
  ADD CONSTRAINT `fk_ticket_comment_ticket` FOREIGN KEY (`ticket`) REFERENCES `tf_ticket` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ticket_comment_user` FOREIGN KEY (`author`) REFERENCES `tf_user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
