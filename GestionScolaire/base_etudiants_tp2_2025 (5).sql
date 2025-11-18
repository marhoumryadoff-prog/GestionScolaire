-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2025 at 01:44 AM
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
CREATE DATABASE IF NOT EXISTS `base_etudiants_tp2_2025` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `base_etudiants_tp2_2025`;

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertRandomStudents` ()   BEGIN
    WHILE @i < @num_students_to_add DO
        SET @current_num = @start_num + @i;
        
        -- Randomize gender and civilite
        SET @is_male = FLOOR(RAND(@random_seed + @i + 1) * 2); 
        SET @rand_civilite = ELT(1 + FLOOR(RAND(@random_seed + @i + 2) * 3), 'M', 'Mme', 'Mlle');
        SET @rand_prenom = IF(@is_male, 
                              ELT(1 + FLOOR(RAND(@random_seed + @i + 3) * 10), 'Yassine', 'Amine', 'Karim', 'Walid', 'Omar', 'Lucas', 'Noah', 'Ethan', 'Gabriel', 'Léo'),
                              ELT(1 + FLOOR(RAND(@random_seed + @i + 3) * 10), 'Inès', 'Sarah', 'Lina', 'Sofia', 'Chloé', 'Alice', 'Léa', 'Manon', 'Emma', 'Jade'));
        
        -- Generate diverse random fields
        SET @rand_name = SUBSTRING_INDEX(SUBSTRING_INDEX(@last_names, ',', 1 + FLOOR(RAND(@random_seed + @i + 4) * 20)), ',', -1);
        SET @rand_address = CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(@addresses, '|', 1 + FLOOR(RAND(@random_seed + @i + 5) * 9)), '|', -1), ', Bloc ', @current_num);
        SET @rand_city = SUBSTRING_INDEX(SUBSTRING_INDEX(@cities, ',', 1 + FLOOR(RAND(@random_seed + @i + 6) * 10)), ',', -1);
        SET @rand_year = 2005 - FLOOR(RAND(@random_seed + @i + 7) * 5);
        SET @rand_month = LPAD(FLOOR(1 + (RAND(@random_seed + @i + 8) * 12)), 2, '0');
        SET @rand_day = LPAD(FLOOR(1 + (RAND(@random_seed + @i + 9) * 28)), 2, '0');
        
        -- Platform and Application Multi-select (More complex combinations)
        SET @rand_platform = ELT(1 + FLOOR(RAND(@random_seed + @i + 10) * 5), 'Windows', 'Linux,Web', 'macOS,iOS', 'Android', 'Windows,Android');
        SET @rand_app = ELT(1 + FLOOR(RAND(@random_seed + @i + 11) * 5), 'Word,Excel', 'Visual Studio,Photoshop', 'Eclipse,NetBeans', 'PowerPoint', 'Autres');

        -- Insert into etudiants
        INSERT INTO `etudiants` 
            (`numero_etudiant`, `nom_etudiant`, `prenom_etudiant`, `civilite`, `date_naissance`, `adresse`, `localisation`, `platforme`, `application`, `id_nationalite`, `FilièreId`) 
        VALUES
        (
            CAST(@current_num AS CHAR), 
            @rand_name, 
            @rand_prenom, 
            @rand_civilite, 
            CONCAT(@rand_year, '-', @rand_month, '-', @rand_day), 
            @rand_address, 
            @rand_city, 
            @rand_platform, 
            @rand_app, 
            ELT(1 + FLOOR(RAND(@random_seed + @i + 12) * 10), 1, 2, 3, 4, 5, 6, 7, 8, 9, 10), 
            ELT(1 + FLOOR(RAND(@random_seed + @i + 13) * 7), 1, 2, 3, 4, 5, 6, 7)
        );
        
        -- Insert Random Sports Links (1 to 3 sports per student)
        INSERT INTO `etudiant_sports` (`id_etudiant`, `id_sport`)
        SELECT LAST_INSERT_ID(), s.id_sport
        FROM `sports` s
        WHERE RAND(@random_seed + @i + s.id_sport) < 0.30 -- 30% chance for each sport
        ORDER BY RAND()
        LIMIT 3;

        -- Insert Random Notes (2 to 4 notes per student)
        INSERT INTO `notes` (`Num_Etudiant`, `Code_Module`, `Note`)
        SELECT 
            CAST(@current_num AS CHAR),
            m.CodeModule,
            ROUND(5 + (RAND(@random_seed + @i + m.Id + 2) * 15), 2)
        FROM `modules` m
        WHERE RAND(@random_seed + @i + m.Id + 3) < 0.35 -- 35% chance of getting a note
        ORDER BY RAND()
        LIMIT 4;

        SET @i = @i + 1;
    END WHILE;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `enseignants`
--

CREATE TABLE `enseignants` (
  `Id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enseignants`
--

INSERT INTO `enseignants` (`Id`, `Numéro`, `Civilité`, `Nom`, `Prénom`, `Adresse`, `DateNaissance`, `LieuNaissance`, `PaysId`, `Grade`, `Spécialité`, `created_at`) VALUES
(1, 'ENS001', 'Mr', 'Alaoui', 'Mohammed', '123 Rue Hassan II, Casablanca', '1980-05-15', 'Casablanca', 3, 'Professeur', 'Informatique', '2025-10-14 23:56:08'),
(2, 'ENS002', 'Mlle', 'Benali', 'Fatima', '456 Avenue Mohammed V, Rabat', '1985-08-22', 'Rabat', 3, 'MCA', 'Mathématiques', '2025-10-14 23:56:08'),
(3, 'ENS003', 'Mr', 'Smith', 'John', '789 Main Street, Paris', '1978-03-10', 'Paris', 7, 'MCB', 'Anglais', '2025-10-14 23:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `etudiants`
--

CREATE TABLE `etudiants` (
  `id_etudiant` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `etudiants`
--

INSERT INTO `etudiants` (`id_etudiant`, `numero_etudiant`, `nom_etudiant`, `prenom_etudiant`, `civilite`, `date_naissance`, `adresse`, `localisation`, `platforme`, `application`, `photo`, `id_nationalite`, `FilièreId`, `created_at`) VALUES
(13, '2', 'Zerrouki', 'Ines', 'Mme', '2005-11-22', '45 Avenue Emir', 'Oran', 'Windows', 'Visual Studio', '68eeead17f625_1760488145.jpg', 3, 3, '2025-10-15 00:12:28'),
(14, '3', 'Haddad', 'Yacine', 'M', '2003-01-07', 'Lotissement 24', 'Constantine', 'Moodle', 'Mobile', 'photos/yacine.jpg', 2, 3, '2025-10-15 00:12:28'),
(15, '4', 'Brahimi', 'Leila', 'Mme', '2000-08-19', 'Cité Universitaire', 'Annaba', 'Teams', 'Web', 'photos/leila.jpg', 3, 2, '2025-10-15 00:12:28'),
(16, '5', 'Meziane', 'Walid', 'M', '2002-05-01', 'Rue 14 Juillet', 'Tlemcen', 'iOS', 'Eclipse', '68eeeafea4aa7_1760488190.jpg', 3, 3, '2025-10-15 00:12:28'),
(17, '6', 'Cherif', 'Nadia', 'Mme', '2001-09-30', 'Rue du Port', 'Bejaia', 'Google Classroom', 'Web', 'photos/nadia.jpg', 4, 2, '2025-10-15 00:12:28'),
(18, '7', 'Khelifi', 'Omar', 'M', '2003-02-11', 'Cité 150 Logements', 'Setif', '', '', '68eeeb1bbd047_1760488219.jpg', 6, 3, '2025-10-15 00:12:28'),
(19, '8', 'Touati', 'Sabrina', 'Mme', '2002-12-05', 'Rue de la Liberté', 'Batna', '', '', '68f82333e2d2a_1761092403.jpg', 2, 1, '2025-10-15 00:12:28'),
(20, '9', 'Belkacem', 'Adel', 'M', '2001-04-27', 'Rue des Martyrs', 'Blida', 'Google Classroom', 'Mobile', 'photos/adel.jpg', 3, 2, '2025-10-15 00:12:28'),
(21, '10', 'Djebbar', 'Rania', 'Mme', '2003-06-14', 'Cité El-Amel', 'Mostaganem', 'Moodle', 'Web', 'photos/rania.jpg', 1, 3, '2025-10-15 00:12:28'),
(22, '15', 'Marhoum', 'Ryad', 'M', '2005-12-27', '22000 Sidi Bel Abbès, Algeria', 'Sidi Bel abbes', 'Windows', 'Photoshop', '68eee77b6165c_1760487291.jpg', 3, 3, '2025-10-15 00:14:51'),
(23, '23', 'Leroy', 'Yassine', 'M', '2005-10-29', '45 Avenue du Port, Bloc 23', 'Alger', 'Windows,Android', 'Visual Studio', NULL, 3, 5, '2025-10-29 00:17:56'),
(24, '24', 'Fares', 'Sarah', 'Mme', '2004-10-29', 'Rue des Roses N°24', 'Oran', 'macOS,iOS', 'Photoshop', NULL, 1, 2, '2025-10-29 00:17:56'),
(25, '25', 'Benali', 'Omar', 'M', '2003-10-29', 'Boulevard du 1er Novembre, Bloc 25', 'Constantine', 'Linux,Web', 'Eclipse', NULL, 7, 7, '2025-10-29 00:17:56'),
(26, '26', 'Garcia', 'Lina', 'Mlle', '2007-10-29', 'Cité Universitaire N°26', 'Setif', 'Android', 'Word,Excel', NULL, 9, 3, '2025-10-29 00:17:56'),
(27, '27', 'Martin', 'Walid', 'M', '2005-10-29', '12 Rue des Roses N°27', 'Annaba', 'Windows', 'NetBeans', NULL, 5, 6, '2025-10-29 00:17:56'),
(28, '28', 'Roux', 'Chloé', 'Mme', '2004-10-29', 'Avenue du Port N°28', 'Tlemcen', 'macOS', 'Autres', NULL, 2, 4, '2025-10-29 00:17:56'),
(29, '29', 'Touati', 'Lucas', 'M', '2003-10-29', 'Place de la Liberté, Bloc 29', 'Blida', 'iOS', 'Visual Studio', NULL, 8, 1, '2025-10-29 00:17:56'),
(30, '30', 'Krim', 'Amine', 'M', '2006-10-29', 'Résidence Émir N°30', 'Mostaganem', 'Linux', 'Photoshop', NULL, 10, 5, '2025-10-29 00:17:56'),
(31, '31', 'Bernard', 'Alice', 'Mlle', '2004-10-29', '12 Rue des Roses N°31', 'Rabat', 'Web', 'Eclipse,NetBeans', NULL, 4, 3, '2025-10-29 00:17:56'),
(32, '32', 'Dubois', 'Manon', 'Mme', '2004-10-29', 'Rue des Martyrs N°32', 'Lyon', 'Windows,iOS', 'PowerPoint', NULL, 1, 6, '2025-10-29 00:17:56'),
(33, '33', 'Perrin', 'Noah', 'M', '2003-10-29', '45 Avenue Foch N°33', 'Marseille', 'Android', 'Word', NULL, 3, 2, '2025-10-29 00:17:56'),
(34, '34', 'Smith', 'Emma', 'Mlle', '2005-10-29', 'Lotissement 24 N°34', 'Paris', 'macOS', 'Excel', NULL, 6, 4, '2025-10-29 00:17:56'),
(35, '35', 'Ouazani', 'Ethan', 'M', '2005-10-29', 'Boulevard du 1er Novembre, Bloc 35', 'Nice', 'Linux,Web', 'Visual Studio', NULL, 2, 7, '2025-10-29 00:17:56'),
(36, '36', 'Zouaghi', 'Jade', 'Mme', '2004-10-29', 'Avenue du Port N°36', 'Nantes', 'Windows', 'Photoshop', NULL, 8, 1, '2025-10-29 00:17:56'),
(37, '37', 'Martin', 'Karim', 'M', '2003-10-29', 'Place de la Liberté N°37', 'Strasbourg', 'iOS,Android', 'NetBeans', NULL, 10, 5, '2025-10-29 00:17:56'),
(38, '38', 'Chen', 'Sarah', 'Mlle', '2007-10-29', 'Résidence Émir N°38', 'Bordeaux', 'macOS', 'PowerPoint', NULL, 4, 3, '2025-10-29 00:17:56'),
(39, '39', 'Dupont', 'Gabriel', 'M', '2005-10-29', '12 Rue des Roses N°39', 'Lille', 'Web', 'Word,Excel', NULL, 2, 6, '2025-10-29 00:17:56'),
(40, '40', 'Leroy', 'Inès', 'Mme', '2004-10-29', '45 Avenue Foch N°40', 'Rennes', 'Windows,iOS', 'Eclipse', NULL, 3, 2, '2025-10-29 00:17:56'),
(41, '41', 'Garcia', 'Walid', 'M', '2003-10-29', 'Lotissement 24 N°41', 'Reims', 'Android', 'Visual Studio', NULL, 6, 4, '2025-10-29 00:17:56'),
(42, '42', 'Williams', 'Lina', 'Mlle', '2006-10-29', 'Rue des Martyrs N°42', 'Le Havre', 'Linux', 'Photoshop', NULL, 2, 7, '2025-10-29 00:17:56'),
(43, '43', 'Johnson', 'Léo', 'M', '2004-10-29', 'Cité Universitaire N°43', 'Saint-Étienne', 'macOS,Web', 'NetBeans', NULL, 7, 1, '2025-10-29 00:17:56'),
(44, '44', 'Benali', 'Sofia', 'Mme', '2004-10-29', '12 Rue des Roses N°44', 'Toulon', 'iOS', 'PowerPoint', NULL, 5, 5, '2025-10-29 00:17:56'),
(45, '45', 'Moore', 'Yassine', 'M', '2003-10-29', 'Avenue du Port N°45', 'Grenoble', 'Windows', 'Word', NULL, 8, 3, '2025-10-29 00:17:56'),
(46, '46', 'Taylor', 'Chloé', 'Mlle', '2005-10-29', 'Place de la Liberté N°46', 'Dijon', 'Linux,Android', 'Excel', NULL, 10, 6, '2025-10-29 00:17:56'),
(47, '47', 'Anderson', 'Amine', 'M', '2005-10-29', 'Résidence Émir N°47', 'Angers', 'macOS,Web', 'Visual Studio', NULL, 4, 4, '2025-10-29 00:17:56'),
(48, '48', 'Thomas', 'Emma', 'Mme', '2004-10-29', '45 Avenue Foch N°48', 'Nîmes', 'iOS', 'Photoshop', NULL, 1, 2, '2025-10-29 00:17:56'),
(49, '49', 'Jackson', 'Noah', 'M', '2003-10-29', 'Lotissement 24 N°49', 'Villeurbanne', 'Windows,Android', 'NetBeans', NULL, 3, 7, '2025-10-29 00:17:56'),
(50, '50', 'White', 'Lina', 'Mlle', '2007-10-29', 'Rue des Martyrs N°50', 'Clermont-Ferrand', 'Linux', 'PowerPoint', NULL, 6, 1, '2025-10-29 00:17:56'),
(51, '51', 'Harris', 'Ethan', 'M', '2005-10-29', 'Cité Universitaire N°51', 'Aix-en-Provence', 'macOS', 'Word', NULL, 2, 5, '2025-10-29 00:17:56'),
(52, '52', 'Martin', 'Sarah', 'Mme', '2004-10-29', '12 Rue des Roses N°52', 'Le Mans', 'Android,Web', 'Excel', NULL, 7, 3, '2025-10-29 00:17:56'),
(53, '53', 'Thompson', 'Lucas', 'M', '2003-10-29', 'Boulevard du 1er Novembre, Bloc 53', 'Brest', 'Windows', 'Visual Studio', NULL, 5, 6, '2025-10-29 00:17:56'),
(54, '54', 'Garcia', 'Alice', 'Mlle', '2006-10-29', 'Avenue du Port N°54', 'Tours', 'Linux', 'Photoshop', NULL, 8, 4, '2025-10-29 00:17:56'),
(55, '55', 'Martinez', 'Gabriel', 'M', '2004-10-29', 'Place de la Liberté N°55', 'Amiens', 'macOS', 'NetBeans', NULL, 10, 2, '2025-10-29 00:17:56'),
(56, '56', 'Robinson', 'Jade', 'Mme', '2004-10-29', 'Résidence Émir N°56', 'Limoges', 'iOS', 'PowerPoint', NULL, 4, 7, '2025-10-29 00:17:56'),
(57, '57', 'Clark', 'Omar', 'M', '2003-10-29', '45 Avenue Foch N°57', 'Metz', 'Android,Web', 'Word', NULL, 9, 1, '2025-10-29 00:17:56'),
(58, '58', 'Rodriguez', 'Inès', 'Mlle', '2005-10-29', 'Lotissement 24 N°58', 'Perpignan', 'Windows', 'Excel', NULL, 1, 5, '2025-10-29 00:17:56'),
(59, '59', 'Lewis', 'Walid', 'M', '2005-10-29', 'Rue des Martyrs N°59', 'Besançon', 'Linux', 'Visual Studio', NULL, 3, 3, '2025-10-29 00:17:56'),
(60, '60', 'Lee', 'Manon', 'Mme', '2004-10-29', 'Cité Universitaire N°60', 'Orléans', 'macOS,iOS', 'Photoshop', NULL, 6, 6, '2025-10-29 00:17:56'),
(61, '61', 'Walker', 'Karim', 'M', '2003-10-29', '12 Rue des Roses N°61', 'Mulhouse', 'Android', 'NetBeans', NULL, 2, 4, '2025-10-29 00:17:56'),
(62, '62', 'Hall', 'Emma', 'Mlle', '2007-10-29', 'Boulevard du 1er Novembre, Bloc 62', 'Caen', 'Windows,Web', 'PowerPoint', NULL, 7, 2, '2025-10-29 00:17:56'),
(63, '63', 'Allen', 'Yassine', 'M', '2005-10-29', 'Avenue du Port N°63', 'Rouen', 'Linux', 'Word', NULL, 5, 7, '2025-10-29 00:17:56'),
(64, '64', 'Young', 'Lina', 'Mme', '2004-10-29', 'Place de la Liberté N°64', 'Nancy', 'macOS', 'Excel', NULL, 8, 1, '2025-10-29 00:17:56'),
(65, '65', 'Hernandez', 'Amine', 'M', '2003-10-29', 'Résidence Émir N°65', 'Argenteuil', 'iOS', 'Visual Studio', NULL, 10, 5, '2025-10-29 00:17:56'),
(66, '66', 'King', 'Sarah', 'Mlle', '2006-10-29', '45 Avenue Foch N°66', 'Montreuil', 'Android,Web', 'Photoshop', NULL, 4, 3, '2025-10-29 00:17:56'),
(67, '67', 'Wright', 'Noah', 'M', '2004-10-29', 'Lotissement 24 N°67', 'Saint-Denis', 'Windows', 'NetBeans', NULL, 1, 6, '2025-10-29 00:17:56'),
(68, '68', 'Lopez', 'Chloé', 'Mme', '2004-10-29', 'Rue des Martyrs N°68', 'Versailles', 'Linux', 'PowerPoint', NULL, 3, 2, '2025-10-29 00:17:56'),
(69, '69', 'Hill', 'Ethan', 'M', '2003-10-29', 'Cité Universitaire N°69', 'Nanterre', 'macOS,iOS', 'Word', NULL, 6, 4, '2025-10-29 00:17:56'),
(70, '70', 'Scott', 'Alice', 'Mlle', '2005-10-29', '12 Rue des Roses N°70', 'Créteil', 'Android', 'Excel', NULL, 2, 7, '2025-10-29 00:17:56'),
(71, '71', 'Green', 'Omar', 'M', '2005-10-29', 'Boulevard du 1er Novembre, Bloc 71', 'Pau', 'Windows,Web', 'Visual Studio', NULL, 7, 1, '2025-10-29 00:17:56'),
(72, '72', 'Adams', 'Léa', 'Mme', '2004-10-29', 'Avenue du Port N°72', 'La Rochelle', 'Linux', 'Photoshop', NULL, 9, 5, '2025-10-29 00:17:56'),
(501, '1', 'Touati', 'Zineb', 'Mlle', '2004-03-22', 'Cité des Mimosas N°501', 'Alger', 'Windows,Web', 'Word,Photoshop', NULL, 3, 3, '2025-10-29 00:25:12'),
(511, '11', 'Lamine', 'Samia', 'Mme', '2003-09-10', '45 Avenue de la République', 'Oran', 'macOS,iOS', 'Visual Studio', NULL, 1, 4, '2025-10-29 00:25:12'),
(512, '12', 'Boudiaf', 'Tarik', 'M', '2005-01-18', 'Résidence Émir, Bloc 12', 'Constantine', 'Linux', 'Eclipse', NULL, 2, 6, '2025-10-29 00:25:12'),
(513, '13', 'Saidi', 'Farah', 'Mlle', '2004-11-29', '10 Rue des Martyrs', 'Annaba', 'Android', 'NetBeans', NULL, 5, 2, '2025-10-29 00:25:12'),
(514, '14', 'Ziane', 'Yacine', 'M', '2003-07-05', 'Boulevard Principal, N°14', 'Blida', 'Windows', 'Excel', NULL, 4, 7, '2025-10-29 00:25:12'),
(516, '16', 'Hafsi', 'Amira', 'Mme', '2005-05-14', 'Place du 1er Novembre N°16', 'Tlemcen', 'macOS', 'PowerPoint', NULL, 8, 3, '2025-10-29 00:25:12'),
(517, '17', 'Kadi', 'Bilal', 'M', '2002-12-08', 'Rue des Jardins, Lot 17', 'Setif', 'Linux,Web', 'Visual Studio,Word', NULL, 10, 5, '2025-10-29 00:25:12'),
(518, '18', 'Moussaoui', 'Nour', 'Mlle', '2004-10-01', 'Avenue Foch N°18', 'Bejaia', 'iOS', 'Photoshop', NULL, 6, 1, '2025-10-29 00:25:12'),
(519, '19', 'Nouri', 'Zoubir', 'M', '2003-06-25', 'Cité El-Amel, N°19', 'Tiaret', 'Windows,Android', 'NetBeans', NULL, 9, 4, '2025-10-29 00:25:12'),
(520, '20', 'Rachid', 'Lyes', 'M', '2005-08-11', '12 Rue des Frères, N°20', 'Mascara', 'macOS', 'Eclipse', NULL, 1, 2, '2025-10-29 00:25:12'),
(521, '21', 'Slimani', 'Chaima', 'Mme', '2002-09-17', 'Résidence du Port, N°21', 'Skikda', 'Linux', 'Excel', NULL, 7, 6, '2025-10-29 00:25:12'),
(522, '22', 'Talbi', 'Houssem', 'M', '2004-04-04', 'Rue du Marché N°22', 'Mostaganem', 'Windows,Web', 'Word', NULL, 3, 3, '2025-10-29 00:25:12');

-- --------------------------------------------------------

--
-- Table structure for table `etudiant_sports`
--

CREATE TABLE `etudiant_sports` (
  `id_etudiant_sport` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `id_sport` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `etudiant_sports`
--

INSERT INTO `etudiant_sports` (`id_etudiant_sport`, `id_etudiant`, `id_sport`) VALUES
(4, 13, 1),
(7, 16, 1),
(6, 18, 4),
(2, 22, 1),
(9, 23, 3),
(8, 23, 6),
(10, 24, 7),
(11, 24, 10),
(14, 25, 4),
(13, 25, 5),
(12, 25, 8),
(19, 26, 1),
(16, 26, 2),
(18, 26, 4),
(20, 26, 6),
(17, 26, 8),
(15, 26, 9),
(22, 27, 2),
(23, 27, 3),
(24, 27, 9),
(21, 27, 10),
(28, 28, 1),
(27, 28, 2),
(29, 28, 4),
(25, 28, 5),
(26, 28, 8),
(31, 29, 2),
(30, 29, 9),
(32, 30, 1),
(34, 30, 6),
(33, 30, 8),
(35, 31, 4),
(37, 31, 7),
(36, 31, 8),
(40, 32, 2),
(39, 32, 5),
(38, 32, 9),
(42, 33, 2),
(43, 33, 3),
(41, 33, 6),
(46, 34, 7),
(45, 34, 9),
(44, 34, 10),
(47, 35, 6),
(48, 36, 9),
(50, 37, 6),
(49, 37, 7),
(53, 38, 2),
(51, 38, 5),
(52, 38, 9),
(141, 39, 7),
(57, 40, 1),
(59, 40, 2),
(55, 40, 5),
(56, 40, 6),
(58, 40, 9),
(61, 41, 2),
(60, 41, 6),
(63, 42, 2),
(62, 42, 8),
(65, 43, 7),
(64, 43, 9),
(66, 44, 6),
(69, 45, 1),
(70, 45, 2),
(67, 45, 4),
(68, 45, 6),
(72, 46, 1),
(71, 46, 2),
(73, 46, 3),
(74, 46, 9),
(76, 47, 6),
(75, 47, 8),
(77, 48, 7),
(136, 501, 1),
(135, 501, 3),
(137, 512, 1),
(138, 518, 4),
(139, 522, 2),
(140, 522, 6);

-- --------------------------------------------------------

--
-- Table structure for table `filières`
--

CREATE TABLE `filières` (
  `Id` int(11) NOT NULL,
  `CodeFilière` varchar(20) NOT NULL,
  `Désignation` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `filières`
--

INSERT INTO `filières` (`Id`, `CodeFilière`, `Désignation`) VALUES
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

CREATE TABLE `modules` (
  `Id` int(11) NOT NULL,
  `CodeModule` varchar(50) NOT NULL,
  `DésignationModule` varchar(100) NOT NULL,
  `FilièreId` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Coefficient` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`Id`, `CodeModule`, `DésignationModule`, `FilièreId`, `created_at`, `Coefficient`) VALUES
(1, 'MATH101', 'Mathématiques Fondamentales', 1, '2025-10-14 23:56:08', 3.00),
(2, 'PHY101', 'Physique Générale', 1, '2025-10-14 23:56:08', 4.00),
(3, 'INFO101', 'Introduction à l\'Informatique', 1, '2025-10-14 23:56:08', 3.50),
(4, 'PROG201', 'Programmation Avancée', 3, '2025-10-14 23:56:08', 5.00),
(5, 'BDD301', 'Bases de Données', 4, '2025-10-14 23:56:08', 4.50),
(6, 'WEB401', 'Développement Web', 4, '2025-10-14 23:56:08', 4.00),
(7, 'ALGO201', 'Algorithmique', 3, '2025-10-14 23:56:08', 3.50),
(8, 'TEC105', 'Techniques d\'Expression et Communication', 1, '2025-10-28 23:58:25', 2.00),
(9, 'ANG100', 'Anglais Général', 1, '2025-10-28 23:58:25', 1.50),
(10, 'STAT210', 'Statistiques et Probabilités', 2, '2025-10-28 23:58:25', 4.00),
(11, 'PHY220', 'Électromagnétisme', 2, '2025-10-28 23:58:25', 4.50),
(12, 'RESEAU350', 'Introduction aux Réseaux', 3, '2025-10-28 23:58:25', 3.50),
(13, 'SECUR490', 'Sécurité des Systèmes d\'Information', 4, '2025-10-28 23:58:25', 5.00),
(14, 'METH100', 'Méthodologie de Recherche', NULL, '2025-10-28 23:58:25', 2.00),
(15, 'CS105', 'Initiation à la Programmation', 1, '2025-10-29 00:15:40', 3.00),
(16, 'CS110', 'Outils Informatiques et Bureautique', 1, '2025-10-29 00:15:40', 1.50),
(17, 'CS115', 'Logique et Résolution de Problèmes', 1, '2025-10-29 00:15:40', 2.50),
(18, 'CS120', 'Structure Discrètes', 1, '2025-10-29 00:15:40', 3.50),
(19, 'CS125', 'Introduction aux Systèmes d\'Exploitation', 1, '2025-10-29 00:15:40', 3.00),
(20, 'DATA205', 'Statistiques pour l\'Informatique', 2, '2025-10-29 00:15:40', 4.00),
(21, 'CALC210', 'Calcul Différentiel et Intégral', 2, '2025-10-29 00:15:40', 5.00),
(22, 'SECU215', 'Principes de la Sécurité', 2, '2025-10-29 00:15:40', 3.50),
(23, 'CPRO220', 'Concepts de Programmation C', 2, '2025-10-29 00:15:40', 4.50),
(24, 'WEB225', 'Fondamentaux du HTML/CSS', 2, '2025-10-29 00:15:40', 2.00),
(25, 'POO305', 'Programmation Orientée Objet (Java)', 3, '2025-10-29 00:15:40', 5.50),
(26, 'RESAU310', 'Réseaux et Protocoles TCP/IP', 3, '2025-10-29 00:15:40', 4.00),
(27, 'ARCH315', 'Architecture des Ordinateurs II', 3, '2025-10-29 00:15:40', 3.50),
(28, 'AGILE320', 'Méthodologie Agile et Scrum', 3, '2025-10-29 00:15:40', 2.50),
(29, 'DATA325', 'Stockage et Bases NoSQL', 3, '2025-10-29 00:15:40', 4.50),
(30, 'CLOUD405', 'Développement d\'Applications Cloud', 4, '2025-10-29 00:15:40', 5.00),
(31, 'DEVOPS410', 'Conteneurisation et DevOps', 4, '2025-10-29 00:15:40', 4.50),
(32, 'IA415', 'Machine Learning', 4, '2025-10-29 00:15:40', 5.50),
(33, 'BIGD420', 'Traitement des Données Massives', 4, '2025-10-29 00:15:40', 4.00),
(34, 'API425', 'Conception d\'APIs REST', 4, '2025-10-29 00:15:40', 3.00),
(35, 'SIMU505', 'Simulation et Modélisation', 5, '2025-10-29 00:15:40', 4.00),
(36, 'ALGO510', 'Analyse d\'Algorithmes', 5, '2025-10-29 00:15:40', 5.00),
(37, 'CSCI515', 'Programmation Scientifique (Matlab/R)', 5, '2025-10-29 00:15:40', 4.50),
(38, 'WEB520', 'Développement Full-Stack (MERN/MEAN)', 5, '2025-10-29 00:15:40', 4.00),
(39, 'SECURE525', 'Cryptographie et Sécurité des Communications', 5, '2025-10-29 00:15:40', 5.50),
(40, 'INFO605', 'Techniques de Recherche Documentaire', 6, '2025-10-29 00:15:40', 1.50),
(41, 'CS610', 'Sécurité et Éthique sur Internet', 6, '2025-10-29 00:15:40', 1.00),
(42, 'MATH615', 'Outils Mathématiques (Base 2/10)', 6, '2025-10-29 00:15:40', 2.00),
(43, 'GRAP620', 'Manipulation Logiciels Graphiques', 6, '2025-10-29 00:15:40', 1.50),
(44, 'PROJ625', 'Gestion de Petit Projet', 6, '2025-10-29 00:15:40', 2.50),
(45, 'PROJ705', 'Projet Annuel Informatique', 7, '2025-10-29 00:15:40', 5.00),
(46, 'RES710', 'Installation et Maintenance PC', 7, '2025-10-29 00:15:40', 3.50),
(47, 'BD715', 'Concepts de Bases de Données (SQL)', 7, '2025-10-29 00:15:40', 4.00),
(48, 'HTML720', 'Création de Sites Statiques', 7, '2025-10-29 00:15:40', 3.00),
(49, 'CYB725', 'Sensibilisation au Hacking Éthique', 7, '2025-10-29 00:15:40', 2.50);

-- --------------------------------------------------------

--
-- Table structure for table `nationalites`
--

CREATE TABLE `nationalites` (
  `id_nationalite` int(11) NOT NULL,
  `libelle_nationalite` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nationalites`
--

INSERT INTO `nationalites` (`id_nationalite`, `libelle_nationalite`) VALUES
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

CREATE TABLE `notes` (
  `Id` int(11) NOT NULL,
  `Num_Etudiant` varchar(50) NOT NULL,
  `Code_Module` varchar(50) NOT NULL,
  `Note` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`Id`, `Num_Etudiant`, `Code_Module`, `Note`) VALUES
(28, '1', 'ALGO201', 15.00),
(29, '72', 'ALGO510', 14.00),
(30, '72', 'SECURE525', 14.00),
(31, '72', 'WEB520', 5.00),
(32, '72', 'CSCI515', 10.00),
(33, '72', 'SIMU505', 9.00);

-- --------------------------------------------------------

--
-- Table structure for table `sports`
--

CREATE TABLE `sports` (
  `id_sport` int(11) NOT NULL,
  `libelle_sport` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sports`
--

INSERT INTO `sports` (`id_sport`, `libelle_sport`) VALUES
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
-- Indexes for table `enseignants`
--
ALTER TABLE `enseignants`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Numéro` (`Numéro`),
  ADD KEY `PaysId` (`PaysId`);

--
-- Indexes for table `etudiants`
--
ALTER TABLE `etudiants`
  ADD PRIMARY KEY (`id_etudiant`),
  ADD UNIQUE KEY `numero_etudiant` (`numero_etudiant`),
  ADD KEY `id_nationalite` (`id_nationalite`),
  ADD KEY `FilièreId` (`FilièreId`);

--
-- Indexes for table `etudiant_sports`
--
ALTER TABLE `etudiant_sports`
  ADD PRIMARY KEY (`id_etudiant_sport`),
  ADD UNIQUE KEY `unique_etudiant_sport` (`id_etudiant`,`id_sport`),
  ADD KEY `id_sport` (`id_sport`);

--
-- Indexes for table `filières`
--
ALTER TABLE `filières`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `CodeFilière` (`CodeFilière`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `CodeModule` (`CodeModule`),
  ADD KEY `FilièreId` (`FilièreId`);

--
-- Indexes for table `nationalites`
--
ALTER TABLE `nationalites`
  ADD PRIMARY KEY (`id_nationalite`),
  ADD UNIQUE KEY `libelle_nationalite` (`libelle_nationalite`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `unique_note_module` (`Num_Etudiant`,`Code_Module`),
  ADD KEY `Code_Module` (`Code_Module`);

--
-- Indexes for table `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`id_sport`),
  ADD UNIQUE KEY `libelle_sport` (`libelle_sport`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `enseignants`
--
ALTER TABLE `enseignants`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `etudiants`
--
ALTER TABLE `etudiants`
  MODIFY `id_etudiant` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=523;

--
-- AUTO_INCREMENT for table `etudiant_sports`
--
ALTER TABLE `etudiant_sports`
  MODIFY `id_etudiant_sport` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `filières`
--
ALTER TABLE `filières`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `nationalites`
--
ALTER TABLE `nationalites`
  MODIFY `id_nationalite` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `sports`
--
ALTER TABLE `sports`
  MODIFY `id_sport` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enseignants`
--
ALTER TABLE `enseignants`
  ADD CONSTRAINT `enseignants_ibfk_1` FOREIGN KEY (`PaysId`) REFERENCES `nationalites` (`id_nationalite`) ON DELETE SET NULL;

--
-- Constraints for table `etudiants`
--
ALTER TABLE `etudiants`
  ADD CONSTRAINT `etudiants_ibfk_1` FOREIGN KEY (`id_nationalite`) REFERENCES `nationalites` (`id_nationalite`) ON DELETE SET NULL,
  ADD CONSTRAINT `etudiants_ibfk_2` FOREIGN KEY (`FilièreId`) REFERENCES `filières` (`Id`) ON DELETE SET NULL;

--
-- Constraints for table `etudiant_sports`
--
ALTER TABLE `etudiant_sports`
  ADD CONSTRAINT `etudiant_sports_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id_etudiant`) ON DELETE CASCADE,
  ADD CONSTRAINT `etudiant_sports_ibfk_2` FOREIGN KEY (`id_sport`) REFERENCES `sports` (`id_sport`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`FilièreId`) REFERENCES `filières` (`Id`) ON DELETE SET NULL;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`Num_Etudiant`) REFERENCES `etudiants` (`numero_etudiant`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`Code_Module`) REFERENCES `modules` (`CodeModule`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
