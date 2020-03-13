SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


TRUNCATE TABLE `tf_faq`;
TRUNCATE TABLE `tf_forum_post`;
TRUNCATE TABLE `tf_forum_category`;
TRUNCATE TABLE `tf_message`;
TRUNCATE TABLE `tf_ticket_comment`;
TRUNCATE TABLE `tf_ticket`;
TRUNCATE TABLE `tf_user`;
TRUNCATE TABLE `tf_user_invited`;

SET FOREIGN_KEY_CHECKS=1;

INSERT INTO `tf_faq` (`id`, `question`, `answer`, `created_at`) VALUES
(1, 'Je n\'arrive pas à me connecter.', 'La reponse c\'est la vie', '2020-03-11 21:04:44'),
(2, 'Comment s\'inscrire ?', 'L\'agent 42 a ete neutralise', '2020-03-11 21:05:06'),
(3, 'Que faire ?', 'Beaucoup de choses', '2020-03-11 21:05:06');

INSERT INTO `tf_user` (`id`, `email`, `password`, `lastname`, `firstname`, `role`, `banned`) VALUES
(1, 'demo@testify.com', 'A6548C32A358B9E7F65F7F56926ED7C34856116CD6015F9322C8CE57A791042C', 'Doe', 'John', 2, 0),
(2, 'demo2@testify.com', 'A6548C32A358B9E7F65F7F56926ED7C34856116CD6015F9322C8CE57A791042C', 'Tessier', 'Alice', 1, 0),
(3, 'demo3@testify.com', 'A6548C32A358B9E7F65F7F56926ED7C34856116CD6015F9322C8CE57A791042C', 'Lousier', 'Ben', 0, 0);

INSERT INTO `tf_user_invited` (`id`, `email`, `firstname`, `lastname`, `role`, `invite_token`, `expire_date`, `active`) VALUES
(1, 'demo4@testify.com', 'foo', 'bar', 1, 'foo', '2099-03-11 21:05:06', 1);


INSERT INTO `tf_message` (`id`, `author`, `recipient`, `message`, `created_at`) VALUES
(1, 1, 2, 'Salut !', '2020-03-11 21:05:06'),
(2, 2, 1, 'Hello', '2020-03-11 21:07:54');


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
