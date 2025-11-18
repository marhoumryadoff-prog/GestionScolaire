-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 09 nov. 2025 à 22:34
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_scolarite_tp7`
--

-- --------------------------------------------------------

--
-- Structure de la table `enseignants`
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
-- Déchargement des données de la table `enseignants`
--

INSERT INTO `enseignants` (`Id`, `Numéro`, `Civilité`, `Nom`, `Prénom`, `Adresse`, `DateNaissance`, `LieuNaissance`, `PaysId`, `Grade`, `Spécialité`, `created_at`) VALUES
(1, 'ENS001', 'Mlle', 'Alaoui', 'Mohammed', '123 Rue Hassan II, Casablanca', '1980-05-15', 'Casablanca', 7, 'Professeur', 'Informatique', '2025-10-14 23:56:08'),
(2, 'ENS002', 'Mme', 'Benali', 'Fatima', '456 Avenue Mohammed V, Rabat', '1985-08-22', 'Rabat', 1, 'MCA', 'Mathématiques', '2025-10-14 23:56:08'),
(3, 'ENS003', 'Mr', 'Smith', 'John', '789 Main Street, Paris', '1978-03-10', 'Paris', 2, 'MCB', 'Anglais', '2025-10-14 23:56:08');

-- --------------------------------------------------------

--
-- Structure de la table `etudiants`
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
-- Déchargement des données de la table `etudiants`
--

INSERT INTO `etudiants` (`id_etudiant`, `numero_etudiant`, `nom_etudiant`, `prenom_etudiant`, `civilite`, `date_naissance`, `adresse`, `localisation`, `platforme`, `application`, `photo`, `id_nationalite`, `FilièreId`, `created_at`) VALUES
(13, '2', 'Dahmani', 'Asia', 'Mme', '2005-11-22', '45 Avenue Emir', 'Oran', 'Windows', 'Visual Studio', '68eeead17f625_1760488145.jpg', 3, 3, '2025-10-15 00:12:28'),
(14, '3', 'Khaldi', 'Nassim', 'M', '2003-01-07', 'Lotissement 24', 'Constantine', 'Moodle', 'Mobile', 'photos/yacine.jpg', 2, 3, '2025-10-15 00:12:28'),
(15, '4', 'Lahmar', 'Hayat', 'Mme', '2000-08-19', 'Cité Universitaire', 'Annaba', 'Teams', 'Web', 'photos/leila.jpg', 3, 2, '2025-10-15 00:12:28'),
(16, '5', 'Zaidi', 'Youssef', 'M', '2002-05-01', 'Rue 14 Juillet', 'Tlemcen', 'iOS', 'Eclipse', '68eeeafea4aa7_1760488190.jpg', 3, 3, '2025-10-15 00:12:28'),
(17, '6', 'Sahraoui', 'Yasmina', 'Mme', '2001-09-30', 'Rue du Port', 'Bejaia', 'Google Classroom', 'Web', 'photos/nadia.jpg', 4, 2, '2025-10-15 00:12:28'),
(18, '7', 'Mansouri', 'Tarek', 'M', '2003-02-11', 'Cité 150 Logements', 'Setif', '', '', '68eeeb1bbd047_1760488219.jpg', 6, 3, '2025-10-15 00:12:28'),
(19, '8', 'Daoud', 'Samira', 'Mlle', '2002-12-05', 'Rue de la Liberté', 'Batna', '', '', '68f82333e2d2a_1761092403.jpg', 2, 1, '2025-10-15 00:12:28'),
(21, '10', 'Farsi', 'Sara', 'Mme', '2003-06-14', 'Cité El-Amel', 'Mostaganem', 'Moodle', 'Web', 'photos/rania.jpg', 1, 3, '2025-10-15 00:12:28'),
(22, '15', 'Fellah', 'Habib', 'M', '2005-12-27', '22000 Sidi Bel Abbès, Algeria', 'Sidi Bel abbes', 'Windows', 'Photoshop', '68eee77b6165c_1760487291.jpg', 3, 3, '2025-10-15 00:14:51'),
(23, '1', 'Benali', 'Farid', 'M', '2002-03-15', 'casa', 'Alger', 'Android', 'Visual Studio', '690e372f7a6a5_1762539311.jpg', 2, 2, '2025-11-07 18:15:11'),
(24, '9', 'Benbrahim', 'Reda', 'M', '2001-04-27', 'sba', 'Blida', 'Android', 'Eclipse', '690e37d7c5052_1762539479.jpg', 3, 3, '2025-11-07 18:17:59');

-- --------------------------------------------------------

--
-- Structure de la table `etudiant_sports`
--

CREATE TABLE `etudiant_sports` (
  `id_etudiant_sport` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `id_sport` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `etudiant_sports`
--

INSERT INTO `etudiant_sports` (`id_etudiant_sport`, `id_etudiant`, `id_sport`) VALUES
(4, 13, 1),
(7, 16, 1),
(6, 18, 4),
(2, 22, 1),
(8, 23, 3),
(9, 24, 9);

-- --------------------------------------------------------

--
-- Structure de la table `filières`
--

CREATE TABLE `filières` (
  `Id` int(11) NOT NULL,
  `CodeFilière` varchar(20) NOT NULL,
  `Désignation` varchar(100) NOT NULL,
  `Niveau` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `filières`
--

INSERT INTO `filières` (`Id`, `CodeFilière`, `Désignation`, `Niveau`) VALUES
(1, 'TC', 'Tronc Commun', '1ère année'),
(2, '2SC', '2ème Science', '2ème année'),
(3, '3ISIL', '3ème Informatique', '3ème année'),
(4, '4IID', '4ème Informatique', '4ème année'),
(5, '2SM', '2ème Science Maths', '2ème année'),
(6, '1BAC', '1ère Baccalauréat', '1ère année'),
(7, '2BAC', '2ème Baccalauréat', '2ème année'),
(8, '3SI', '3ème année - Systèmes Informatiques', '3ème année'),
(9, 'M1', 'Master 1 - Informatique', 'Master 1'),
(10, 'M2_ISI', 'Master 2 - Ingénierie des Systèmes d\'Information', 'Master 2'),
(11, 'M2_WIC', 'Master 2 - Web Intelligence et Cloud Computing', 'Master 2'),
(12, 'M2_RSSI', 'Master 2 - Réseaux et Sécurité des Systèmes Informatiques', 'Master 2'),
(13, '1ING', '1ère année cycle ingénieur', '1ère année ingénieur'),
(14, '2ING', '2ème année cycle ingénieur', '2ème année ingénieur');

-- --------------------------------------------------------

--
-- Structure de la table `modules`
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
-- Déchargement des données de la table `modules`
--

INSERT INTO `modules` (`Id`, `CodeModule`, `DésignationModule`, `FilièreId`, `created_at`, `Coefficient`) VALUES
(1, 'MATH101', 'Mathématiques Fondamentales', 1, '2025-10-14 23:56:08', 3.00),
(2, 'PHY101', 'Physique Générale', 1, '2025-10-14 23:56:08', 4.00),
(3, 'INFO101', 'Introduction à l\'Informatique', 1, '2025-10-14 23:56:08', 3.50),
(4, 'PROG201', 'Programmation Avancée', 3, '2025-10-14 23:56:08', 5.00),
(5, 'BDD301', 'Bases de Données Avancées', 3, '2025-10-14 23:56:08', 4.50),
(6, 'WEB301', 'Développement Web (Front-end & Back-end)', 3, '2025-10-14 23:56:08', 4.00),
(7, 'ALGO301', 'Algorithmique Avancée', 3, '2025-10-14 23:56:08', 3.50),
(16, 'ARCH101', 'Architecture des ordinateurs', 1, '2025-10-28 17:23:58', 3.00),
(17, 'SYSE101', 'Systèmes d\'exploitation (Introduction)', 1, '2025-10-28 17:23:58', 3.50),
(18, 'MATH102', 'Mathématiques discrètes', 1, '2025-10-28 17:23:58', 4.00),
(19, 'LANG101', 'Langue et Communication', 1, '2025-10-28 17:23:58', 2.00),
(20, 'BDD201', 'Bases de Données', 2, '2025-10-28 17:24:18', 4.00),
(21, 'RES201', 'Réseaux Informatiques', 2, '2025-10-28 17:24:18', 3.50),
(22, 'SYSE201', 'Systèmes d\'exploitation (avancé)', 2, '2025-10-28 17:24:18', 3.00),
(23, 'THL201', 'Théorie des Langages et Automates', 2, '2025-10-28 17:24:18', 3.00),
(24, 'POO201', 'Programmation Orientée Objet', 2, '2025-10-28 17:24:18', 4.50),
(30, 'GEN301', 'Génie Logiciel (UML, conception orientée objet)', 3, '2025-10-28 17:30:55', 4.00),
(31, 'SEC301', 'Sécurité Informatique et Administration Réseaux', 3, '2025-10-28 17:30:55', 3.50),
(32, 'PFE301', 'Projet de Fin d\'Études', 3, '2025-10-28 17:30:55', 5.00),
(36, 'WEB401', 'Développement Web Avancé', 4, '2025-10-28 17:35:15', 4.00),
(37, 'PROG401', 'Programmation Avancée', 4, '2025-10-28 17:35:15', 5.00),
(38, 'ARCH401', 'Architecture des Systèmes Informatiques', 4, '2025-10-28 17:35:15', 3.50),
(39, 'SEC401', 'Sécurité des Systèmes', 4, '2025-10-28 17:35:15', 4.00);

-- --------------------------------------------------------

--
-- Structure de la table `nationalites`
--

CREATE TABLE `nationalites` (
  `id_nationalite` int(11) NOT NULL,
  `libelle_nationalite` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `nationalites`
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
-- Structure de la table `notes`
--

CREATE TABLE `notes` (
  `Id` int(11) NOT NULL,
  `Num_Etudiant` varchar(50) NOT NULL,
  `Code_Module` varchar(50) NOT NULL,
  `Note` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notes`
--

INSERT INTO `notes` (`Id`, `Num_Etudiant`, `Code_Module`, `Note`) VALUES
(15, '3', 'PROG201', 10.00),
(18, '8', 'ARCH101', 13.00),
(19, '8', 'LANG101', 14.00),
(20, '8', 'MATH102', 16.00),
(21, '8', 'PHY101', 8.00),
(22, '2', 'GEN301', 15.00),
(23, '2', 'ALGO301', 9.00),
(24, '7', 'BDD301', 13.00),
(25, '7', 'GEN301', 17.00),
(28, '1', 'BDD201', 15.00),
(29, '1', 'RES201', 13.00),
(30, '1', 'THL201', 16.00),
(31, '9', 'GEN301', 15.00),
(32, '9', 'PROG201', 18.00),
(33, '9', 'PFE301', 11.00);

-- --------------------------------------------------------

--
-- Structure de la table `sports`
--

CREATE TABLE `sports` (
  `id_sport` int(11) NOT NULL,
  `libelle_sport` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sports`
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

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `mdp`, `role`) VALUES
(1, 'admin@isil.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin'),
(2, 'etudiant@isil.com', '$2y$10$bb8wQkFW78X3l5/UVsEi3u5QErn2hLhZ.IHn1bF1F7s1wcjZTYF2K', 'User'),
(10, 'kawther@gmail.com', '$2y$10$VU5KMQ9vE9XtExAEuRt3NeYVGquuSrpBuiKR4pdsg5IjXTeVZz0XG', 'Admin'),
(11, 'l3@gmail.com', '$2y$10$2NA37mzd/UxMVYfw..azo.CZkePUasqRvlygEsVjx6tPc8W2tJptO', 'User');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `enseignants`
--
ALTER TABLE `enseignants`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Numéro` (`Numéro`),
  ADD KEY `PaysId` (`PaysId`);

--
-- Index pour la table `etudiants`
--
ALTER TABLE `etudiants`
  ADD PRIMARY KEY (`id_etudiant`),
  ADD UNIQUE KEY `numero_etudiant` (`numero_etudiant`),
  ADD KEY `id_nationalite` (`id_nationalite`),
  ADD KEY `FilièreId` (`FilièreId`);

--
-- Index pour la table `etudiant_sports`
--
ALTER TABLE `etudiant_sports`
  ADD PRIMARY KEY (`id_etudiant_sport`),
  ADD UNIQUE KEY `unique_etudiant_sport` (`id_etudiant`,`id_sport`),
  ADD KEY `id_sport` (`id_sport`);

--
-- Index pour la table `filières`
--
ALTER TABLE `filières`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `CodeFilière` (`CodeFilière`);

--
-- Index pour la table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `CodeModule` (`CodeModule`),
  ADD KEY `FilièreId` (`FilièreId`);

--
-- Index pour la table `nationalites`
--
ALTER TABLE `nationalites`
  ADD PRIMARY KEY (`id_nationalite`),
  ADD UNIQUE KEY `libelle_nationalite` (`libelle_nationalite`);

--
-- Index pour la table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `unique_note_module` (`Num_Etudiant`,`Code_Module`),
  ADD KEY `Code_Module` (`Code_Module`);

--
-- Index pour la table `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`id_sport`),
  ADD UNIQUE KEY `libelle_sport` (`libelle_sport`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `enseignants`
--
ALTER TABLE `enseignants`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `etudiants`
--
ALTER TABLE `etudiants`
  MODIFY `id_etudiant` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `etudiant_sports`
--
ALTER TABLE `etudiant_sports`
  MODIFY `id_etudiant_sport` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `filières`
--
ALTER TABLE `filières`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `modules`
--
ALTER TABLE `modules`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT pour la table `nationalites`
--
ALTER TABLE `nationalites`
  MODIFY `id_nationalite` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `notes`
--
ALTER TABLE `notes`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `sports`
--
ALTER TABLE `sports`
  MODIFY `id_sport` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `enseignants`
--
ALTER TABLE `enseignants`
  ADD CONSTRAINT `enseignants_ibfk_1` FOREIGN KEY (`PaysId`) REFERENCES `nationalites` (`id_nationalite`) ON DELETE SET NULL;

--
-- Contraintes pour la table `etudiants`
--
ALTER TABLE `etudiants`
  ADD CONSTRAINT `etudiants_ibfk_1` FOREIGN KEY (`id_nationalite`) REFERENCES `nationalites` (`id_nationalite`) ON DELETE SET NULL,
  ADD CONSTRAINT `etudiants_ibfk_2` FOREIGN KEY (`FilièreId`) REFERENCES `filières` (`Id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `etudiant_sports`
--
ALTER TABLE `etudiant_sports`
  ADD CONSTRAINT `etudiant_sports_ibfk_1` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id_etudiant`) ON DELETE CASCADE,
  ADD CONSTRAINT `etudiant_sports_ibfk_2` FOREIGN KEY (`id_sport`) REFERENCES `sports` (`id_sport`) ON DELETE CASCADE;

--
-- Contraintes pour la table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`FilièreId`) REFERENCES `filières` (`Id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`Num_Etudiant`) REFERENCES `etudiants` (`numero_etudiant`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`Code_Module`) REFERENCES `modules` (`CodeModule`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
