-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 29 mai 2026 à 11:26
-- Version du serveur : 8.4.7
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `cfashop`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `identifiant` varchar(100) NOT NULL,
  `mot_depasse` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifiant` (`identifiant`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `admin_users`
--

INSERT INTO `admin_users` (`id`, `identifiant`, `mot_depasse`) VALUES
(1, 'admin', '$2y$10$fvR4eYjRR6hCNuAksV6qAu0MdvdEiLunf//Yxn.974GVQ7b2b4rNi');

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE IF NOT EXISTS `articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `categorie` varchar(50) NOT NULL,
  `prix` int NOT NULL,
  `image` varchar(255) NOT NULL,
  `date_ajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `articles`
--

INSERT INTO `articles` (`id`, `nom`, `categorie`, `prix`, `image`, `date_ajout`) VALUES
(1, 'robe', 'femme', 2000, 'f4627804fa8a612ccc955ea62ef9dc0b.jpg', '2026-05-29 09:26:14'),
(2, 'robe', 'femme', 2000, '887b0dbfd944aa4bbd8a47c353cace97.jpg', '2026-05-29 09:37:21');

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_complet` varchar(150) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `newsletter` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id`, `nom_complet`, `ville`, `email`, `telephone`, `mot_de_passe`, `newsletter`, `created_at`) VALUES
(1, 'assime', 'bafoussam', 'assime@gmail.com', '0677531191', '$2y$10$BioRrlNKh0uJiTjs6hqwoes0SrQ8bY80/hxRWWMjzTwBfYfkoWypu', 0, '2026-05-29 09:40:17'),
(2, 'rodrigue', 'douala', 'rodrigue@gmail.com', '0695509631', '$2y$10$7y1ZxIMt8SIkNlbS158/he47bk5lxFNp8pqPkMzufBivkw0bmIsCC', 0, '2026-05-29 11:15:41');

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

DROP TABLE IF EXISTS `commandes`;
CREATE TABLE IF NOT EXISTS `commandes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_client` varchar(255) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `articles` text NOT NULL,
  `total` int NOT NULL,
  `date_commande` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` varchar(50) DEFAULT 'En attente',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`id`, `nom_client`, `telephone`, `ville`, `articles`, `total`, `date_commande`, `statut`) VALUES
(1, 'rodrigue', '0695509631', 'douala', 'robe (x4)', 8000, '2026-05-29 11:19:32', 'En attente');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
