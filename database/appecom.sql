-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 20 fév. 2026 à 17:51
-- Version du serveur : 8.0.31
-- Version de PHP : 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `appecom`
--

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `id_client` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ville` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sexe` char(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_client`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`id_client`, `nom`, `prenom`, `ville`, `sexe`, `contact`, `date_naissance`, `email`, `mot_de_passe`) VALUES
(1, 'Ngue', 'Paul', 'Douala', 'M', '690112233', '1990-05-12', 'paul.client@appecom.com', 'hash_paul'),
(2, 'Kamga', 'Alice', 'Yaoundé', 'F', '691223344', '1988-09-20', 'alice.client@appecom.com', 'hash_alice'),
(3, 'Tchoua', 'Marc', 'Bafoussam', 'M', '692334455', '1995-02-15', 'marc.client@appecom.com', 'hash_marc'),
(4, 'Mbappe', 'Claire', 'Douala', 'F', '693445566', '1992-07-10', 'claire.client@appecom.com', 'hash_claire'),
(5, 'Fokou', 'Jean', 'Yaoundé', 'M', '694556677', '1985-03-22', 'jean.client@appecom.com', 'hash_jean'),
(6, 'Nana', 'Lucie', 'Bafoussam', 'F', '695667788', '1993-11-05', 'lucie.client@appecom.com', 'hash_lucie'),
(7, 'Talla', 'Roger', 'Douala', 'M', '696778899', '1991-01-18', 'roger.client@appecom.com', 'hash_roger'),
(8, 'Manga', 'Sylvie', 'Yaoundé', 'F', '697889900', '1989-06-30', 'sylvie.client@appecom.com', 'hash_sylvie'),
(9, 'Kouam', 'Daniel', 'Bafoussam', 'M', '698990011', '1994-04-12', 'daniel.client@appecom.com', 'hash_daniel'),
(10, 'Ngassa', 'Brigitte', 'Douala', 'F', '699001122', '1996-08-25', 'brigitte.client@appecom.com', 'hash_brigitte');

-- --------------------------------------------------------

--
-- Structure de la table `commande_client`
--

DROP TABLE IF EXISTS `commande_client`;
CREATE TABLE IF NOT EXISTS `commande_client` (
  `id_commande` int NOT NULL AUTO_INCREMENT,
  `date_commande` date NOT NULL,
  `id_client` int DEFAULT NULL,
  `statut` enum('En attente','Validée','Livrée','Annulée') COLLATE utf8mb4_unicode_ci DEFAULT 'En attente',
  PRIMARY KEY (`id_commande`),
  KEY `id_client` (`id_client`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commande_client`
--

INSERT INTO `commande_client` (`id_commande`, `date_commande`, `id_client`, `statut`) VALUES
(1, '2026-02-01', 1, 'Validée'),
(2, '2026-02-02', 2, 'En attente'),
(3, '2026-02-03', 3, 'Livrée'),
(4, '2026-02-04', 4, 'Annulée'),
(5, '2026-02-05', 5, 'Validée'),
(6, '2026-02-06', 6, 'Livrée'),
(7, '2026-02-07', 7, 'En attente'),
(8, '2026-02-08', 8, 'Validée'),
(9, '2026-02-09', 9, 'Livrée'),
(10, '2026-02-10', 10, 'En attente'),
(11, '2026-02-20', 5, 'Validée');

-- --------------------------------------------------------

--
-- Structure de la table `commande_fournisseur`
--

DROP TABLE IF EXISTS `commande_fournisseur`;
CREATE TABLE IF NOT EXISTS `commande_fournisseur` (
  `id_commande_f` int NOT NULL AUTO_INCREMENT,
  `date_commande` date NOT NULL,
  `etat` enum('En attente','Reçue','Annulée') COLLATE utf8mb4_unicode_ci DEFAULT 'En attente',
  `id_fournisseur` int DEFAULT NULL,
  PRIMARY KEY (`id_commande_f`),
  KEY `id_fournisseur` (`id_fournisseur`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commande_fournisseur`
--

INSERT INTO `commande_fournisseur` (`id_commande_f`, `date_commande`, `etat`, `id_fournisseur`) VALUES
(1, '2026-01-20', 'Reçue', 1),
(2, '2026-01-21', 'En attente', 2),
(3, '2026-01-22', 'Annulée', 3),
(4, '2026-01-23', 'Reçue', 4),
(5, '2026-01-24', 'En attente', 5),
(6, '2026-01-25', 'Reçue', 6),
(7, '2026-01-26', 'Annulée', 7),
(8, '2026-01-27', 'Reçue', 8),
(9, '2026-01-28', 'En attente', 9),
(10, '2026-01-29', 'Reçue', 10);

-- --------------------------------------------------------

--
-- Structure de la table `concerner_client`
--

DROP TABLE IF EXISTS `concerner_client`;
CREATE TABLE IF NOT EXISTS `concerner_client` (
  `id_commande` int NOT NULL,
  `id_produit` int NOT NULL,
  `quantite` int NOT NULL,
  PRIMARY KEY (`id_commande`,`id_produit`),
  KEY `id_produit` (`id_produit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `concerner_client`
--

INSERT INTO `concerner_client` (`id_commande`, `id_produit`, `quantite`) VALUES
(11, 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `concerner_fournisseur`
--

DROP TABLE IF EXISTS `concerner_fournisseur`;
CREATE TABLE IF NOT EXISTS `concerner_fournisseur` (
  `id_commande_f` int NOT NULL,
  `id_produit` int NOT NULL,
  `quantite` int NOT NULL,
  PRIMARY KEY (`id_commande_f`,`id_produit`),
  KEY `id_produit` (`id_produit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `famille`
--

DROP TABLE IF EXISTS `famille`;
CREATE TABLE IF NOT EXISTS `famille` (
  `id_famille` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id_famille`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `famille`
--

INSERT INTO `famille` (`id_famille`, `libelle`) VALUES
(1, 'Smartphones'),
(2, 'Ordinateurs'),
(3, 'Accessoires'),
(4, 'Tablettes'),
(5, 'Télévisions'),
(6, 'Imprimantes'),
(7, 'Réseaux'),
(8, 'Stockage'),
(9, 'Audio'),
(10, 'Composants');

-- --------------------------------------------------------

--
-- Structure de la table `fournisseur`
--

DROP TABLE IF EXISTS `fournisseur`;
CREATE TABLE IF NOT EXISTS `fournisseur` (
  `id_fournisseur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_fournisseur`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `fournisseur`
--

INSERT INTO `fournisseur` (`id_fournisseur`, `nom`, `adresse`, `ville`, `contact`) VALUES
(1, 'ElectroCam', 'Marché Central', 'Douala', '670112233'),
(2, 'TechAfrique', 'Quartier Briqueterie', 'Yaoundé', '671223344'),
(3, 'GlobalElectro', 'Carrefour Tchitcha', 'Bafoussam', '672334455'),
(4, 'MegaTech', 'Bonapriso', 'Douala', '673445566'),
(5, 'InfoWorld', 'Mvog-Mbi', 'Yaoundé', '674556677'),
(6, 'CamElec', 'Marché A', 'Bafoussam', '675667788'),
(7, 'DigitalHouse', 'Akwa', 'Douala', '676778899'),
(8, 'SmartTech', 'Essos', 'Yaoundé', '677889900'),
(9, 'ElectroPlus', 'Marché B', 'Bafoussam', '678990011'),
(10, 'TechVision', 'Bonanjo', 'Douala', '679001122');

-- --------------------------------------------------------

--
-- Structure de la table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_action` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `id_utilisateur` (`id_utilisateur`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `logs`
--

INSERT INTO `logs` (`id_log`, `id_utilisateur`, `action`, `date_action`) VALUES
(1, 1, 'Création de la commande client #1', '2026-02-13 13:07:11'),
(2, 1, 'Validation de la commande fournisseur #1', '2026-02-13 13:07:11'),
(3, 2, 'Ajout du produit Samsung Galaxy S23', '2026-02-13 13:07:11'),
(4, 3, 'Suppression d’un client', '2026-02-13 13:07:11'),
(5, 4, 'Mise à jour du stock Dell Inspiron', '2026-02-13 13:07:11'),
(6, 5, 'Ajout d’un fournisseur TechVision', '2026-02-13 13:07:11'),
(7, 6, 'Modification du rôle de Jean Employe', '2026-02-13 13:07:11'),
(8, 7, 'Enregistrement du paiement #5', '2026-02-13 13:07:11'),
(9, 8, 'Annulation de la commande client #4', '2026-02-13 13:07:11'),
(10, 9, 'Exportation du rapport des ventes journalières', '2026-02-13 13:07:11'),
(11, 1, 'Ajout paiement client #11', '2026-02-20 17:03:17'),
(12, 1, 'Maj statut paiement client #7 -> En attente', '2026-02-20 17:03:33');

-- --------------------------------------------------------

--
-- Structure de la table `paiement`
--

DROP TABLE IF EXISTS `paiement`;
CREATE TABLE IF NOT EXISTS `paiement` (
  `id_paiement` int NOT NULL AUTO_INCREMENT,
  `type` enum('Client','Fournisseur') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_commande` int DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `mode` enum('Cash','Carte','MobileMoney','Virement') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_paiement` date NOT NULL,
  `statut` enum('Payé','En attente','Annulé') COLLATE utf8mb4_unicode_ci DEFAULT 'En attente',
  PRIMARY KEY (`id_paiement`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `paiement`
--

INSERT INTO `paiement` (`id_paiement`, `type`, `id_commande`, `montant`, `mode`, `date_paiement`, `statut`) VALUES
(1, 'Client', 1, '1200000.00', 'Carte', '2026-02-01', 'Payé'),
(2, 'Client', 2, '450000.00', 'MobileMoney', '2026-02-02', 'En attente'),
(3, 'Client', 3, '300000.00', 'Cash', '2026-02-03', 'Payé'),
(4, 'Client', 4, '250000.00', 'Carte', '2026-02-04', 'Annulé'),
(5, 'Client', 5, '800000.00', 'Virement', '2026-02-05', 'Payé'),
(6, 'Client', 6, '600000.00', 'MobileMoney', '2026-02-06', 'Payé'),
(7, 'Client', 7, '150000.00', 'Cash', '2026-02-07', 'En attente'),
(8, 'Client', 8, '950000.00', 'Carte', '2026-02-08', 'Payé'),
(9, 'Client', 9, '700000.00', 'Virement', '2026-02-09', 'Payé'),
(10, 'Client', 10, '500000.00', 'MobileMoney', '2026-02-10', 'En attente'),
(11, 'Client', 11, '500000.00', 'MobileMoney', '2026-02-20', 'Payé');

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

DROP TABLE IF EXISTS `produit`;
CREATE TABLE IF NOT EXISTS `produit` (
  `id_produit` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantite` int DEFAULT '0',
  `id_famille` int DEFAULT NULL,
  PRIMARY KEY (`id_produit`),
  KEY `id_famille` (`id_famille`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`id_produit`, `libelle`, `prix`, `description`, `quantite`, `id_famille`) VALUES
(1, 'Casque Audio HD', '5000.00', 'Son immersif', 50, 9),
(2, 'Tablette TabMax', '40000.00', 'Mobilité et confort', 30, 4),
(3, 'Télévision 4K', '80000.00', 'Image ultra HD', 20, 5),
(4, 'Smartwatch Fit', '10000.00', 'Suivi santé', 40, 1),
(5, 'Caméra Pro', '65000.00', 'Qualité professionnelle', 15, 3),
(6, 'Imprimante Jet', '200000.00', 'Rapide et fiable', 10, 6),
(7, 'Routeur WiFi 6', '35000.00', 'Connexion rapide', 25, 7),
(8, 'Enceinte Bluetooth', '10000.00', 'Son puissant', 60, 9),
(9, 'Souris Gamer', '3000.00', 'Précision extrême', 100, 10),
(10, 'Clavier Mécanique', '5000.00', 'Confort et rapidité', 80, 10),
(11, 'Projecteur HD', '45000.00', 'Cinéma maison', 12, 5),
(12, 'Drone Cam', '20000.00', 'Vue aérienne', 18, 3),
(13, 'Console NextGen', '70000.00', 'Jeux immersifs', 22, 2),
(14, 'Micro Studio', '80000.00', 'Qualité audio', 14, 9),
(15, 'Webcam HD', '20000.00', 'Visio claire', 35, 3),
(16, 'Powerbank 20000mAh', '15000.00', 'Énergie portable', 50, 8),
(17, 'Casque VR', '15000.00', 'Réalité virtuelle', 20, 4),
(18, 'Assistant Vocal', '75000.00', 'Maison connectée', 25, 1);

-- --------------------------------------------------------

--
-- Structure de la table `stock_mouvement`
--

DROP TABLE IF EXISTS `stock_mouvement`;
CREATE TABLE IF NOT EXISTS `stock_mouvement` (
  `id_mouvement` int NOT NULL AUTO_INCREMENT,
  `id_produit` int DEFAULT NULL,
  `type` enum('Entrée','Sortie') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantite` int NOT NULL,
  `date_mouvement` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mouvement`),
  KEY `id_produit` (`id_produit`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `stock_mouvement`
--

INSERT INTO `stock_mouvement` (`id_mouvement`, `id_produit`, `type`, `quantite`, `date_mouvement`) VALUES
(1, 1, 'Entrée', 10, '2026-02-13 13:07:11'),
(2, 2, 'Sortie', 2, '2026-02-13 13:07:11'),
(3, 3, 'Entrée', 5, '2026-02-13 13:07:11'),
(4, 4, 'Sortie', 1, '2026-02-13 13:07:11'),
(5, 5, 'Entrée', 20, '2026-02-13 13:07:11'),
(6, 6, 'Sortie', 10, '2026-02-13 13:07:11'),
(7, 7, 'Entrée', 15, '2026-02-13 13:07:11'),
(8, 8, 'Sortie', 3, '2026-02-13 13:07:11'),
(9, 9, 'Entrée', 5, '2026-02-13 13:07:11'),
(10, 10, 'Sortie', 2, '2026-02-13 13:07:11');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id_utilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('Administrateur','Employe') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_utilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `email`, `mot_de_passe`, `role`, `created_at`) VALUES
(1, 'Amiral', 'amiral@appecom.com', 'amiral2007', 'Administrateur', '2026-02-13 13:07:10'),
(2, 'Jean', 'jean@appecom.com', 'hash_jean', 'Employe', '2026-02-13 13:07:10'),
(3, 'Marie', 'marie@appecom.com', 'hash_marie', 'Employe', '2026-02-13 13:07:10'),
(4, 'Luc', 'luc@appecom.com', 'hash_luc', 'Employe', '2026-02-13 13:07:10'),
(5, 'Sophie', 'sophie@appecom.com', 'hash_sophie', 'Employe', '2026-02-13 13:07:10'),
(6, 'David', 'david@appecom.com', 'hash_david', 'Employe', '2026-02-13 13:07:10'),
(7, 'Nathalie', 'nathalie@appecom.com', 'hash_nathalie', 'Employe', '2026-02-13 13:07:10'),
(8, 'Eric', 'eric@appecom.com', 'hash_eric', 'Employe', '2026-02-13 13:07:10'),
(9, 'Julie', 'julie@appecom.com', 'hash_julie', 'Employe', '2026-02-13 13:07:10'),
(10, 'Marc', 'marc@appecom.com', 'hash_marc', 'Employe', '2026-02-13 13:07:10');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commande_client`
--
ALTER TABLE `commande_client`
  ADD CONSTRAINT `commande_client_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_client`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `commande_fournisseur`
--
ALTER TABLE `commande_fournisseur`
  ADD CONSTRAINT `commande_fournisseur_ibfk_1` FOREIGN KEY (`id_fournisseur`) REFERENCES `fournisseur` (`id_fournisseur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `concerner_client`
--
ALTER TABLE `concerner_client`
  ADD CONSTRAINT `concerner_client_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `commande_client` (`id_commande`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `concerner_client_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produit` (`id_produit`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `concerner_fournisseur`
--
ALTER TABLE `concerner_fournisseur`
  ADD CONSTRAINT `concerner_fournisseur_ibfk_1` FOREIGN KEY (`id_commande_f`) REFERENCES `commande_fournisseur` (`id_commande_f`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `concerner_fournisseur_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produit` (`id_produit`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `produit`
--
ALTER TABLE `produit`
  ADD CONSTRAINT `produit_ibfk_1` FOREIGN KEY (`id_famille`) REFERENCES `famille` (`id_famille`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `stock_mouvement`
--
ALTER TABLE `stock_mouvement`
  ADD CONSTRAINT `stock_mouvement_ibfk_1` FOREIGN KEY (`id_produit`) REFERENCES `produit` (`id_produit`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
