-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le :  lun. 18 nov. 2019 à 22:40
-- Version du serveur :  10.4.8-MariaDB
-- Version de PHP :  7.3.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `testify`
--

-- --------------------------------------------------------

--
-- Structure de la table `tf_faq`
--

CREATE TABLE `tf_faq` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `tf_forum_category`
--

CREATE TABLE `tf_forum_category` (
  `id` int(11) NOT NULL,
  `title` varchar(70) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `tf_forum_category`
--

INSERT INTO `tf_forum_category` (`id`, `title`, `created_at`, `updated_at`) VALUES
(1, 'Assistance', '2019-11-18 19:42:40', '2019-11-18 19:42:40');

-- --------------------------------------------------------

--
-- Structure de la table `tf_forum_post`
--

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

--
-- Déchargement des données de la table `tf_forum_post`
--

INSERT INTO `tf_forum_post` (`id`, `author`, `title`, `content`, `created_at`, `updated_at`, `category`, `post_response`) VALUES
(1, 2, 'Petit test', '&#60;span style=&#34;font-weight: bold;&#34;&#62;Bonjour &#60;/span&#62;tout le &#60;span style=&#34;color: rgb(204, 0, 0);&#34;&#62;&#60;span style=&#34;text-decoration: underline;&#34;&#62;monde &#60;/span&#62;&#60;/span&#62;!&#60;br&#62;', '2019-11-18 20:00:34', '2019-11-18 20:00:34', 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `tf_message`
--

CREATE TABLE `tf_message` (
  `id` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `recipient` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `tf_message`
--

INSERT INTO `tf_message` (`id`, `author`, `recipient`, `message`, `created_at`) VALUES
(1, 2, 3, 'Yep ca va merci ;-)', '2019-11-08 13:50:54'),
(2, 2, 3, 'Hey petit test !', '2019-11-08 15:52:29'),
(3, 3, 2, 'Yo', '2019-11-08 16:07:28'),
(4, 2, 3, 'Ok ça m&#39;a l&#39;air de fonctionner :D', '2019-11-08 16:07:58'),
(5, 3, 2, 'Ah nice', '2019-11-08 16:08:34'),
(6, 2, 3, 'Salut !', '2019-11-08 16:10:18'),
(7, 3, 2, 'Hey comment ca va ?', '2019-11-08 16:10:38');

-- --------------------------------------------------------

--
-- Structure de la table `tf_ticket`
--

CREATE TABLE `tf_ticket` (
  `id` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `title` varchar(70) NOT NULL,
  `content` text NOT NULL,
  `status` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Structure de la table `tf_user`
--

CREATE TABLE `tf_user` (
  `id` int(11) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password` varchar(64) NOT NULL,
  `lastname` varchar(75) NOT NULL,
  `firstname` varchar(75) NOT NULL,
  `role` int(11) NOT NULL,
  `banned` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `tf_user`
--

INSERT INTO `tf_user` (`id`, `email`, `password`, `lastname`, `firstname`, `role`, `banned`) VALUES
(2, 'demo@testify.com', 'A6548C32A358B9E7F65F7F56926ED7C34856116CD6015F9322C8CE57A791042C', 'John', 'Doe', 2, 0),
(3, 'demo2@testify.com', 'A6548C32A358B9E7F65F7F56926ED7C34856116CD6015F9322C8CE57A791042C', 'Alice', 'O\'connel', 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `tf_user_invited`
--

CREATE TABLE `tf_user_invited` (
  `id` int(11) NOT NULL,
  `email` varchar(254) NOT NULL,
  `firstname` varchar(75) NOT NULL,
  `lastname` varchar(75) NOT NULL,
  `invite_token` varchar(64) NOT NULL,
  `expire_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `tf_faq`
--
ALTER TABLE `tf_faq`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tf_forum_category`
--
ALTER TABLE `tf_forum_category`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tf_forum_post`
--
ALTER TABLE `tf_forum_post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_forumpost_forumcategory` (`category`),
  ADD KEY `FK_forumpost_forumpost` (`post_response`),
  ADD KEY `FK_forumpost_user` (`author`);

--
-- Index pour la table `tf_message`
--
ALTER TABLE `tf_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_message_user` (`author`),
  ADD KEY `FK_message_user2` (`recipient`);

--
-- Index pour la table `tf_ticket`
--
ALTER TABLE `tf_ticket`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_ticket_user` (`author`);

--
-- Index pour la table `tf_user`
--
ALTER TABLE `tf_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`email`);

--
-- Index pour la table `tf_user_invited`
--
ALTER TABLE `tf_user_invited`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invite_token` (`invite_token`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `tf_faq`
--
ALTER TABLE `tf_faq`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tf_forum_category`
--
ALTER TABLE `tf_forum_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `tf_forum_post`
--
ALTER TABLE `tf_forum_post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `tf_message`
--
ALTER TABLE `tf_message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `tf_ticket`
--
ALTER TABLE `tf_ticket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tf_user`
--
ALTER TABLE `tf_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `tf_user_invited`
--
ALTER TABLE `tf_user_invited`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `tf_forum_post`
--
ALTER TABLE `tf_forum_post`
  ADD CONSTRAINT `FK_forumpost_forumcategory` FOREIGN KEY (`category`) REFERENCES `tf_forum_category` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_forumpost_forumpost` FOREIGN KEY (`post_response`) REFERENCES `tf_forum_post` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_forumpost_user` FOREIGN KEY (`author`) REFERENCES `tf_user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tf_message`
--
ALTER TABLE `tf_message`
  ADD CONSTRAINT `FK_message_user` FOREIGN KEY (`author`) REFERENCES `tf_user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_message_user2` FOREIGN KEY (`recipient`) REFERENCES `tf_user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tf_ticket`
--
ALTER TABLE `tf_ticket`
  ADD CONSTRAINT `FK_ticket_user` FOREIGN KEY (`author`) REFERENCES `tf_user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
