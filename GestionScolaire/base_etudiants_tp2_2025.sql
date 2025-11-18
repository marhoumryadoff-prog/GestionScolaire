-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2025 at 03:15 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `base_etudiants_tp2_2025`
--

-- --------------------------------------------------------

--
-- Table structure for table `enseignants`
--

CREATE TABLE IF NOT EXISTS `enseignants` (
  `Id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `Numéro` varchar(50) NOT NULL,
  `Civilité` enum('Mr','Mme','Mlle') NOT NULL,
  `Nom` varchar(100) NOT NULL,
  `Prénom` varchar(100) NOT NULL,
  `Adresse` text DEFAULT NULL,
  `DateNaissance` date DEFAULT NULL,
  `LieuNaissance` varchar(100) DEFAULT NULL,
  `PaysId` int(11) DEFAULT NULL,
  `Grade` enum('Assistant','MAB','MAA','MCB','MCA','Professeur') NOT NULL,
  `Spécialité` enum('Informatique','Mathématiques','Anglais','autres') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `Numéro` (`Numéro`),
  KEY `PaysId` (`PaysId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enseignants`
--

INSERT IGNORE INTO `enseignants` (`Id`, `Numéro`, `Civilité`, `Nom`, `Prénom`, `Adresse`, `DateNaissance`, `LieuNaissance`, `PaysId`, `Grade`, `Spécialité`, `created_at`) VALUES
(1, 'ENS001', 'Mlle', 'Alaoui', 'Mohammed', '123 Rue Hassan II, Casablanca', '1980-05-15', 'Casablanca', 3, 'Professeur', 'Informatique', '2025-10-14 23:56:08'),
(2, 'ENS002', 'Mme', 'Benali', 'Fatima', '456 Avenue Mohammed V, Rabat', '1985-08-22', 'Rabat', 1, 'MCA', 'Mathématiques', '2025-10-14 23:56:08'),
(3, 'ENS003', 'Mr', 'Smith', 'John', '789 Main Street, Paris', '1978-03-10', 'Paris', 2, 'MCB', 'Anglais', '2025-10-14 23:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `etudiants`
--

CREATE TABLE IF NOT EXISTS `etudiants` (
  `id_etudiant` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `numero_etudiant` varchar(50) NOT NULL,
  `nom_etudiant` varchar(100) NOT NULL,
  `prenom_etudiant` varchar(100) NOT NULL,
  `civilite` enum('M','Mme','Mlle') DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `localisation` varchar(100) DEFAULT NULL,
  `platforme` varchar(50) DEFAULT NULL,
  `application` varchar(50) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `id_nationalite` int(11) DEFAULT NULL,
  `FilièreId` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  UNIQUE KEY `numero_etudiant` (`numero_etudiant`),
  KEY `id_nationalite` (`id_nationalite`),
  KEY `FilièreId` (`FilièreId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `etudiants`
--

INSERT IGNORE INTO `etudiants` (`id_etudiant`, `numero_etudiant`, `nom_etudiant`, `prenom_etudiant`, `civilite`, `date_naissance`, `adresse`, `localisation`, `platforme`, `application`, `photo`, `id_nationalite`, `FilièreId`, `created_at`) VALUES
(12, '1', 'Benali', 'Sami', 'M', '2002-03-15', '12 Rue des Roses', 'Alger', '', '', '68f822f6d357b_1761092342.jpg', 1, 2, '2025-10-15 00:12:28'),
(13, '2', 'Zerrouki', 'Ines', 'Mme', '2005-11-22', '45 Avenue Emir', 'Oran', 'Windows', 'Visual Studio', '68eeead17f625_1760488145.jpg', 3, 3, '2025-10-15 00:12:28'),
(14, '3', 'Haddad', 'Yacine', 'M', '2003-01-07', 'Lotissement 24', 'Constantine', 'Moodle', 'Mobile', 'photos/yacine.jpg', 2, 3, '2025-10-15 00:12:28'),
(15, '4', 'Brahimi', 'Leila', 'Mme', '2000-08-19', 'Cité Universitaire', 'Annaba', 'Teams', 'Web', 'photos/leila.jpg', 3, 2, '2025-10-15 00:12:28'),
(16, '5', 'Meziane', 'Walid', 'M', '2002-05-01', 'Rue 14 Juillet', 'Tlemcen', 'iOS', 'Eclipse', '68eeeafea4aa7_1760488190.jpg', 3, 3, '2025-10-15 00:12:28'),
(17, '6', 'Cherif', 'Nadia', 'Mme', '2001-09-30', 'Rue du Port', 'Bejaia', 'Google Classroom', 'Web', 'photos/nadia.jpg', 4, 2, '2025-10-15 00:12:28'),
(18, '7', 'Khelifi', 'Omar', 'M', '2003-02-11', 'Cité 150 Logements', 'Setif', '', '', '68eeeb1bbd047_1760488219.jpg', 6, 3, '2025-10-15 00:12:28'),
(19, '8', 'Touati', 'Sabrina', 'Mme', '2002-12-05', 'Rue de la Liberté', 'Batna', '', '', '68f82333e2d2a_1761092403.jpg', 2, 1, '2025-10-15 00:12:28'),
(20, '9', 'Belkacem', 'Adel', 'M', '2001-04-27', 'Rue des Martyrs', 'Blida', 'Google Classroom', 'Mobile', 'photos/adel.jpg', 3, 2, '2025-10-15 00:12:28'),
(21, '10', 'Djebbar', 'Rania', 'Mme', '2003-06-14', 'Cité El-Amel', 'Mostaganem', 'Moodle', 'Web', 'photos/rania.jpg', 1, 3, '2025-10-15 00:12:28'),
(22, '15', 'Marhoum', 'Ryad', 'M', '2005-12-27', '22000 Sidi Bel Abbès, Algeria', 'Sidi Bel abbes', 'Windows', 'Photoshop', '68eee77b6165c_1760487291.jpg', 3, 3, '2025-10-15 00:14:51');

-- --------------------------------------------------------

--
-- Table structure for table `etudiant_sports`
--

CREATE TABLE IF NOT EXISTS `etudiant_sports` (
  `id_etudiant_sport` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_etudiant` int(11) NOT NULL,
  `id_sport` int(11) NOT NULL,
  UNIQUE KEY `unique_etudiant_sport` (`id_etudiant`,`id_sport`),
  KEY `id_sport` (`id_sport`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `etudiant_sports`
--

INSERT IGNORE INTO `etudiant_sports` (`id_etudiant_sport`, `id_etudiant`, `id_sport`) VALUES
(4, 13, 1),
(7, 16, 1),
(6, 18, 4),
(2, 22, 1);

-- --------------------------------------------------------

--
-- Table structure for table `filières`
--

CREATE TABLE IF NOT EXISTS `filières` (
  `Id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `CodeFilière` varchar(20) NOT NULL,
  `Désignation` varchar(100) NOT NULL,
  UNIQUE KEY `CodeFilière` (`CodeFilière`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `filières`
--

INSERT IGNORE INTO `filières` (`Id`, `CodeFilière`, `Désignation`) VALUES
(1, 'TC', 'Tronc Commun'),
(2, '2SC', '2ème Science'),
(3, '3ISIL', '3ème Informatique'),
(4, '4IID', '4ème Informatique'),
(5, '2SM', '2ème Science Maths'),
(6, '1BAC', '1ère Baccalauréat'),
(7, '2BAC', '2ème Baccalauréat');

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE IF NOT EXISTS `modules` (
  `Id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `CodeModule` varchar(50) NOT NULL,
  `DésignationModule` varchar(100) NOT NULL,
  `FilièreId` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Coefficient` decimal(4,2) DEFAULT NULL,
  UNIQUE KEY `CodeModule` (`CodeModule`),
  KEY `FilièreId` (`FilièreId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT IGNORE INTO `modules` (`Id`, `CodeModule`, `DésignationModule`, `FilièreId`, `created_at`, `Coefficient`) VALUES
(1, 'MATH101', 'Mathématiques Fondamentales', 1, '2025-10-14 23:56:08', 3.00),
(2, 'PHY101', 'Physique Générale', 1, '2025-10-14 23:56:08', 4.00),
(3, 'INFO101', 'Introduction à l\'Informatique', 1, '2025-10-14 23:56:08', 3.50),
(4, 'PROG201', 'Programmation Avancée', 3, '2025-10-14 23:56:08', 5.00),
(5, 'BDD301', 'Bases de Données', 4, '2025-10-14 23:56:08', 4.50),
(6, 'WEB401', 'Développement Web', 4, '2025-10-14 23:56:08', 4.00),
(7, 'ALGO201', 'Algorithmique', 3, '2025-10-14 23:56:08', 3.50);

-- --------------------------------------------------------

--
-- Table structure for table `nationalites`
--

CREATE TABLE IF NOT EXISTS `nationalites` (
  `id_nationalite` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `libelle_nationalite` varchar(100) NOT NULL,
  UNIQUE KEY `libelle_nationalite` (`libelle_nationalite`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nationalites`
--

INSERT IGNORE INTO `nationalites` (`id_nationalite`, `libelle_nationalite`) VALUES
(3, 'Algérienne'),
(10, 'Allemande'),
(7, 'Américaine'),
(9, 'Belge'),
(8, 'Canadienne'),
(5, 'Espagnole'),
(2, 'Française'),
(6, 'Italienne'),
(1, 'Marocaine'),
(4, 'Tunisienne');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE IF NOT EXISTS `notes` (
  `Id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `Num_Etudiant` varchar(50) NOT NULL,
  `Code_Module` varchar(50) NOT NULL,
  `Note` decimal(4,2) DEFAULT NULL,
  UNIQUE KEY `unique_note_module` (`Num_Etudiant`,`Code_Module`),
  KEY `Code_Module` (`Code_Module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT IGNORE INTO `notes` (`Id`, `Num_Etudiant`, `Code_Module`, `Note`) VALUES
(1, '1', 'BDD301', 14.50),
(2, '1', 'ALGO201', 18.00),
(3, '3', 'PROG201', 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `sports`
--

CREATE TABLE IF NOT EXISTS `sports` (
  `id_sport` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `libelle_sport` varchar(100) NOT NULL,
  UNIQUE KEY `libelle_sport` (`libelle_sport`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sports`
--

INSERT IGNORE INTO `sports` (`id_sport`, `libelle_sport`) VALUES
(5, 'Athlétisme'),
(2, 'Basketball'),
(10, 'Boxe'),
(1, 'Football'),
(7, 'Handball'),
(8, 'Judo'),
(9, 'Karate'),
(4, 'Natation'),
(3, 'Tennis'),
(6, 'Volleyball');

--
-- Indexes for dumped tables
--

--
-- AUTO_INCREMENT for dumped tables
--

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enseignants`
--
ALTER TABLE `enseignants` DROP FOREIGN KEY IF EXISTS `enseignants_ibfk_1`;
ALTER TABLE `enseignants`
  ADD CONSTRAINT `enseignants_ibfk_1` FOREIGN KEY (`PaysId`) REFERENCES `nationalites` (`id_nationalite`) ON DELETE SET NULL;

--
-- Constraints for table `etudiants`
--
ALTER TABLE `etudiants` DROP FOREIGN KEY IF EXISTS `etudiants_ibfk_1`;
ALTER TABLE `etudiants` DROP FOREIGN KEY IF EXISTS `etudiants_ibfk_2`;
ALTER TABLE `etudiants`
  ADD CONSTRAINT `etudiants_ibfk_1` FOREIGN KEY (`id_nationalite`) REFERENCES `nationalites` (`id_nationalite`) ON DELETE SET NULL,
  ADD CONSTRAINT `etudiants_ibfk_2` FOREIGN KEY (`FilièreId`) REFERENCES `filières` (`Id`) ON DELETE SET NULL;

--
-- Constraints for table `etudiant_sports`
--
ALTER TABLE `etudiant_sports` DROP FOREIGN KEY IF EXISTS `etudiant_sports_ibfk_1`;
ALTER TABLE `etudiant_sports` DROP FOREIGN KEY IF EXISTS `etudiant_sports_ibfk_2`;
ALTER TABLE `etudiant_sports`
  ADD CONSTRAINT `etudiant_sports_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id_etudiant`) ON DELETE CASCADE,
  ADD CONSTRAINT `etudiant_sports_ibfk_2` FOREIGN KEY (`id_sport`) REFERENCES `sports` (`id_sport`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules` DROP FOREIGN KEY IF EXISTS `modules_ibfk_1`;
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`FilièreId`) REFERENCES `filières` (`Id`) ON DELETE SET NULL;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes` DROP FOREIGN KEY IF EXISTS `notes_ibfk_1`;
ALTER TABLE `notes` DROP FOREIGN KEY IF EXISTS `notes_ibfk_2`;
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`Num_Etudiant`) REFERENCES `etudiants` (`numero_etudiant`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`Code_Module`) REFERENCES `modules` (`CodeModule`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `email` varchar(255) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT IGNORE INTO `user` (`id`, `email`, `mdp`, `role`) VALUES
(1, 'admin@isil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin'),
(2, 'etudiant@isil.com', '$2y$10$bb8wQkFW78X3l5/UVsEi3u5QErn2hLhZ.IHn1bF1F7s1wcjZTYF2K', 'User'),
(10, 'kawther@gmail.com', '$2y$10$VU5KMQ9vE9XtExAEuRt3NeYVGquuSrpBuiKR4pdsg5IjXTeVZz0XG', 'Admin'),
(11, 'l3@gmail.com', '$2y$10$2NA37mzd/UxMVYfw..azo.CZkePUasqRvlygEsVjx6tPc8W2tJptO', 'User');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
