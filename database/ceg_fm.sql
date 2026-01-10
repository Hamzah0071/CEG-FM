-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 07 jan. 2026 à 16:42
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
-- Base de données : `ceg_fm`
--

DELIMITER $$
--
-- Procédures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `calculer_rangs_bulletins` (IN `p_periode_id` INT)   BEGIN
    UPDATE bulletins b
    JOIN (
        SELECT 
            inscription_id,
            periode_id,
            ROW_NUMBER() OVER (
                PARTITION BY i.classe_id 
                ORDER BY b.moyenne_generale DESC, b.id
            ) AS rang_calcule
        FROM bulletins b
        JOIN inscriptions i ON b.inscription_id = i.id
        WHERE b.periode_id = p_periode_id
          AND b.deleted_at IS NULL
          AND b.moyenne_generale IS NOT NULL
    ) ranked ON b.inscription_id = ranked.inscription_id AND b.periode_id = ranked.periode_id
    SET b.rang = ranked.rang_calcule
    WHERE b.periode_id = p_periode_id AND b.deleted_at IS NULL;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `generer_matricule_professeur` (OUT `p_matricule` VARCHAR(20))   BEGIN
    DECLARE annee_courante INT;
    DECLARE numero INT;
    
    SET annee_courante = YEAR(CURRENT_DATE);
    
    -- Compter les professeurs de l'année courante
    SELECT COUNT(*) + 1 INTO numero
    FROM professeurs
    WHERE YEAR(created_at) = annee_courante;
    
    -- Générer le matricule: PROF2025001, PROF2025002, etc.
    SET p_matricule = CONCAT('PROF', annee_courante, LPAD(numero, 4, '0'));
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_statistiques_professeur` (IN `p_professeur_id` INT, IN `p_annee_scolaire_id` INT)   BEGIN
    SELECT 
        COUNT(DISTINCT e.classe_id) as nombre_classes,
        COUNT(DISTINCT e.matiere_id) as nombre_matieres,
        SUM(e.volume_horaire_hebdo) as total_heures_hebdo,
        COUNT(DISTINCT CASE WHEN e.est_titulaire = 1 THEN e.classe_id END) as classes_titulaire,
        (SELECT COUNT(*) FROM inscriptions i 
         JOIN enseignements e2 ON i.classe_id = e2.classe_id 
         WHERE e2.professeur_id = p_professeur_id 
         AND e2.annee_scolaire_id = p_annee_scolaire_id
         AND i.annee_scolaire_id = p_annee_scolaire_id
         AND i.deleted_at IS NULL) as nombre_eleves_total
    FROM enseignements e
    WHERE e.professeur_id = p_professeur_id
    AND e.annee_scolaire_id = p_annee_scolaire_id
    AND e.deleted_at IS NULL;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `set_annee_active` (IN `p_annee_id` INT)   BEGIN
    -- Désactiver toutes les autres années
    UPDATE annee_scolaire SET actif = 0 WHERE actif = 1 AND id != p_annee_id;
    
    -- Activer l'année demandée
    UPDATE annee_scolaire SET actif = 1 WHERE id = p_annee_id;
END$$

--
-- Fonctions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `peut_supprimer_professeur` (`p_professeur_id` INT) RETURNS TINYINT(1) DETERMINISTIC READS SQL DATA BEGIN
    DECLARE nb_enseignements INT;
    DECLARE nb_notes INT;
    DECLARE nb_appels INT;
    
    -- Vérifier s'il a des enseignements actifs
    SELECT COUNT(*) INTO nb_enseignements
    FROM enseignements
    WHERE professeur_id = p_professeur_id
    AND deleted_at IS NULL;
    
    -- Vérifier s'il a saisi des notes
    SELECT COUNT(*) INTO nb_notes
    FROM notes n
    JOIN enseignements e ON n.affectation_id = e.id
    WHERE e.professeur_id = p_professeur_id
    AND n.deleted_at IS NULL;
    
    -- Vérifier s'il a fait des appels
    SELECT COUNT(*) INTO nb_appels
    FROM appel a
    JOIN enseignements e ON a.affectation_id = e.id
    WHERE e.professeur_id = p_professeur_id;
    
    -- Peut supprimer uniquement s'il n'a rien
    IF (nb_enseignements = 0 AND nb_notes = 0 AND nb_appels = 0) THEN
        RETURN TRUE;
    ELSE
        RETURN FALSE;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `affectations_prof`
--

CREATE TABLE `affectations_prof` (
  `id` int(11) NOT NULL,
  `prof_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `matiere_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `volume_horaire_hebdo` decimal(4,1) DEFAULT NULL,
  `est_titulaire` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `annee_scolaire`
--

CREATE TABLE `annee_scolaire` (
  `id` int(11) NOT NULL,
  `libelle` varchar(20) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ;

--
-- Déchargement des données de la table `annee_scolaire`
--

INSERT INTO `annee_scolaire` (`id`, `libelle`, `date_debut`, `date_fin`, `actif`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2024-2025', '2024-09-01', '2025-06-30', 1, '2026-01-05 00:45:57', NULL, NULL);

--
-- Déclencheurs `annee_scolaire`
--
DELIMITER $$
CREATE TRIGGER `trg_annee_scolaire_check_unique_actif` BEFORE INSERT ON `annee_scolaire` FOR EACH ROW BEGIN
    DECLARE count_actif INT;
    
    -- Si on insère une année active, vérifier qu'il n'y en a pas déjà une
    IF NEW.actif = 1 THEN
        SELECT COUNT(*) INTO count_actif 
        FROM annee_scolaire 
        WHERE actif = 1 AND deleted_at IS NULL;
        
        IF count_actif > 0 THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Une année scolaire est déjà active. Désactivez-la d''abord.';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_annee_scolaire_check_unique_actif_update` BEFORE UPDATE ON `annee_scolaire` FOR EACH ROW BEGIN
    DECLARE count_actif INT;
    
    -- Si on active une année, vérifier qu'il n'y en a pas déjà une autre d'active
    IF NEW.actif = 1 AND OLD.actif = 0 THEN
        SELECT COUNT(*) INTO count_actif 
        FROM annee_scolaire 
        WHERE actif = 1 AND id != NEW.id AND deleted_at IS NULL;
        
        IF count_actif > 0 THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Une année scolaire est déjà active. Désactivez-la d''abord.';
        END IF;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `appel`
--

CREATE TABLE `appel` (
  `id` int(11) NOT NULL,
  `date_appel` date NOT NULL,
  `inscription_id` int(11) NOT NULL,
  `affectation_id` int(11) NOT NULL,
  `heure_cours` time NOT NULL,
  `present` tinyint(1) NOT NULL DEFAULT 1,
  `retard` tinyint(1) NOT NULL DEFAULT 0,
  `justifie` tinyint(1) NOT NULL DEFAULT 0,
  `motif` text DEFAULT NULL,
  `saisie_par` int(11) NOT NULL,
  `saisie_le` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `bulletins`
--

CREATE TABLE `bulletins` (
  `id` int(11) NOT NULL,
  `inscription_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `moyenne_generale` decimal(5,2) DEFAULT NULL,
  `rang` int(11) DEFAULT NULL,
  `effectif_classe` int(11) DEFAULT NULL,
  `appreciation_generale` text DEFAULT NULL,
  `decision_conseil` enum('passage','redoublement','reorientation') DEFAULT NULL,
  `date_generation` datetime NOT NULL DEFAULT current_timestamp(),
  `genere_par` int(11) NOT NULL,
  `publie` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Structure de la table `cahier_de_texte`
--

CREATE TABLE `cahier_de_texte` (
  `id` int(11) NOT NULL,
  `date_cours` date NOT NULL,
  `affectation_id` int(11) NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `titre` varchar(100) DEFAULT NULL,
  `contenu` text NOT NULL,
  `devoir` text DEFAULT NULL,
  `date_rendu_devoir` date DEFAULT NULL,
  `fichier_joint_path` varchar(255) DEFAULT NULL,
  `statut` enum('brouillon','publie','archive') DEFAULT 'brouillon',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `certificats`
--

CREATE TABLE `certificats` (
  `id` int(11) NOT NULL,
  `inscription_id` int(11) NOT NULL,
  `type_certificat` enum('scolarite','inscription','presence','reussite','transfert') NOT NULL,
  `annee_reference` varchar(20) DEFAULT NULL,
  `contenu_supplementaire` text DEFAULT NULL,
  `fichier_pdf_path` varchar(255) DEFAULT NULL,
  `date_generation` datetime NOT NULL DEFAULT current_timestamp(),
  `genere_par` int(11) NOT NULL,
  `numero_certificat` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `niveau` varchar(10) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `effectif_max` int(11) DEFAULT 50,
  `salle_principale` varchar(20) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `classes`
--

INSERT INTO `classes` (`id`, `nom`, `niveau`, `annee_scolaire_id`, `effectif_max`, `salle_principale`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'A', '6ème', 1, 40, NULL, '2026-01-05 00:45:58', NULL, NULL),
(2, 'B', '6ème', 1, 40, NULL, '2026-01-05 00:45:58', NULL, NULL),
(3, 'A', '5ème', 1, 40, NULL, '2026-01-05 00:45:58', NULL, NULL),
(4, 'B', '5ème', 1, 40, NULL, '2026-01-05 00:45:58', NULL, NULL),
(5, 'A', '4ème', 1, 40, NULL, '2026-01-05 00:45:58', NULL, NULL),
(6, 'B', '4ème', 1, 40, NULL, '2026-01-05 00:45:58', NULL, NULL),
(7, 'A', '3ème', 1, 40, NULL, '2026-01-05 00:45:58', NULL, NULL),
(8, 'B', '3ème', 1, 40, NULL, '2026-01-05 00:45:58', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `eleves`
--

CREATE TABLE `eleves` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `personne_id` int(11) NOT NULL,
  `matricule` varchar(20) DEFAULT NULL,
  `date_inscription` date NOT NULL,
  `nom_parent` varchar(100) DEFAULT NULL,
  `telephone_parent` varchar(15) DEFAULT NULL,
  `email_parent` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `eleves`
--

INSERT INTO `eleves` (`id`, `utilisateur_id`, `personne_id`, `matricule`, `date_inscription`, `nom_parent`, `telephone_parent`, `email_parent`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 2, '2026EL0001', '2026-01-01', 'RABEARIVELO', '', 'rabearivelo@gmail.com', '2026-01-05 11:39:21', '2026-01-06 07:53:56', NULL),
(2, 3, 3, NULL, '2026-01-06', '', '', '', '2026-01-06 07:51:29', '2026-01-06 11:00:32', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `emploi_du_temps`
--

CREATE TABLE `emploi_du_temps` (
  `id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `jour` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi') NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `affectation_id` int(11) NOT NULL,
  `salle` varchar(20) DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Structure de la table `enseignements`
--

CREATE TABLE `enseignements` (
  `id` int(11) NOT NULL,
  `professeur_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `matiere_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `volume_horaire_hebdo` decimal(4,1) DEFAULT NULL,
  `est_titulaire` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
--

CREATE TABLE `inscriptions` (
  `id` int(11) NOT NULL,
  `eleve_id` int(11) NOT NULL,
  `classe_id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `date_inscription` date NOT NULL,
  `statut` enum('actif','redouble','abandonne','transfere','diplome') DEFAULT 'actif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `logs_audit`
--

CREATE TABLE `logs_audit` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `action` enum('CREATE','UPDATE','DELETE','LOGIN','LOGOUT') NOT NULL,
  `ancien_contenu` text DEFAULT NULL,
  `nouveau_contenu` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `logs_audit`
--

INSERT INTO `logs_audit` (`id`, `utilisateur_id`, `table_name`, `record_id`, `action`, `ancien_contenu`, `nouveau_contenu`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'utilisateurs', 1, 'LOGIN', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-05 00:47:08'),
(2, 1, 'utilisateurs', 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-05 04:26:17'),
(3, 1, 'utilisateurs', 1, 'LOGOUT', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-05 07:07:05');

-- --------------------------------------------------------

--
-- Structure de la table `matieres`
--

CREATE TABLE `matieres` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `coefficient` decimal(3,1) NOT NULL DEFAULT 1.0,
  `categorie` varchar(30) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ;

--
-- Déchargement des données de la table `matieres`
--

INSERT INTO `matieres` (`id`, `code`, `nom`, `coefficient`, `categorie`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'MATH', 'Mathématiques', 4.0, 'Sciences', '2026-01-05 00:45:58', NULL, NULL),
(2, 'PC', 'Physique-Chimie', 3.0, 'Sciences', '2026-01-05 00:45:58', NULL, NULL),
(3, 'SVT', 'Sciences de la Vie et de la Terre', 2.0, 'Sciences', '2026-01-05 00:45:58', NULL, NULL),
(4, 'FR', 'Français', 4.0, 'Lettres', '2026-01-05 00:45:58', NULL, NULL),
(5, 'ANG', 'Anglais', 2.0, 'Langues', '2026-01-05 00:45:58', NULL, NULL),
(6, 'HG', 'Histoire-Géographie', 2.0, 'Sciences Humaines', '2026-01-05 00:45:58', NULL, NULL),
(7, 'EPS', 'Éducation Physique et Sportive', 1.0, 'Sport', '2026-01-05 00:45:58', NULL, NULL),
(8, 'INFO', 'Informatique', 2.0, 'Technologie', '2026-01-05 00:45:58', NULL, NULL),
(9, 'PHILO', 'Philosophie', 3.0, 'Lettres', '2026-01-07 18:08:13', NULL, NULL),
(10, 'ECO', 'Sciences Économiques', 2.0, 'Sciences Humaines', '2026-01-07 18:08:13', NULL, NULL),
(11, 'MLG', 'Malgache', 3.0, 'Langues', '2026-01-07 18:08:13', NULL, NULL),
(12, 'ARTS', 'Arts Plastiques', 1.0, 'Arts', '2026-01-07 18:08:13', NULL, NULL),
(13, 'MUS', 'Éducation Musicale', 1.0, 'Arts', '2026-01-07 18:08:13', NULL, NULL),
(14, 'TECH', 'Technologie', 2.0, 'Technologie', '2026-01-07 18:08:13', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `inscription_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `affectation_id` int(11) NOT NULL,
  `type_note` enum('devoir','examen','composition','controle_continu','oral') NOT NULL,
  `valeur` decimal(4,2) NOT NULL,
  `sur_combien` decimal(4,2) NOT NULL DEFAULT 20.00,
  `date_evaluation` date NOT NULL,
  `date_saisie` datetime NOT NULL DEFAULT current_timestamp(),
  `commentaire` text DEFAULT NULL,
  `publiee` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Structure de la table `parametres_etablissement`
--

CREATE TABLE `parametres_etablissement` (
  `id` int(11) NOT NULL,
  `nom_etablissement` varchar(100) NOT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `annee_scolaire_active_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `parametres_etablissement`
--

INSERT INTO `parametres_etablissement` (`id`, `nom_etablissement`, `adresse`, `telephone`, `email`, `logo_path`, `annee_scolaire_active_id`, `created_at`, `updated_at`) VALUES
(1, 'CEG FM', 'Antsiranana, Madagascar', '+261 34 00 000 ', 'contact@ceg-fm.mg', NULL, 1, '2026-01-05 00:45:58', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `periodes`
--

CREATE TABLE `periodes` (
  `id` int(11) NOT NULL,
  `annee_scolaire_id` int(11) NOT NULL,
  `nom` varchar(20) NOT NULL,
  `type_periode` enum('trimestre','semestre','quadrimestre') NOT NULL,
  `numero` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ;

--
-- Déchargement des données de la table `periodes`
--

INSERT INTO `periodes` (`id`, `annee_scolaire_id`, `nom`, `type_periode`, `numero`, `date_debut`, `date_fin`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Trimestre 1', 'trimestre', 1, '2024-09-01', '2024-12-15', '2026-01-05 00:45:58', NULL, NULL),
(2, 1, 'Trimestre 2', 'trimestre', 2, '2025-01-06', '2025-03-31', '2026-01-05 00:45:58', NULL, NULL),
(3, 1, 'Trimestre 3', 'trimestre', 3, '2025-04-01', '2025-06-30', '2026-01-05 00:45:58', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `personnes`
--

CREATE TABLE `personnes` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(100) DEFAULT NULL,
  `nationalite` varchar(50) DEFAULT 'Malgache',
  `sexe` enum('M','F') NOT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `personnes`
--

INSERT INTO `personnes` (`id`, `nom`, `prenom`, `date_naissance`, `lieu_naissance`, `nationalite`, `sexe`, `telephone`, `adresse`, `photo_path`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', 'Système', '1990-01-01', NULL, 'Malgache', 'M', NULL, NULL, NULL, '2026-01-05 00:45:57', NULL, NULL),
(2, 'RAKOTO', 'Jean', '2005-01-01', '403MTLE lazaret', 'Malgache', 'M', '032 00 000 00', 'DIANA', NULL, '2026-01-05 11:36:12', NULL, NULL),
(3, 'HAMZAH', 'Nasser', '2001-10-19', NULL, 'Malgache', 'M', '0320297271', '403MTLE Lazaret', NULL, '2026-01-06 07:51:29', NULL, NULL),
(4, 'IBRAHIM', 'Traore', '1999-06-03', NULL, 'Malgache', 'M', '0320297272', 'DIANA', NULL, '2026-01-06 11:14:12', NULL, NULL),
(5, 'RAKOTO', 'Jean', '1985-05-15', 'Antananarivo', 'Malgache', 'M', '034 12 345 67', NULL, NULL, '2026-01-07 18:08:14', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `professeurs`
--

CREATE TABLE `professeurs` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `personne_id` int(11) NOT NULL,
  `matricule` varchar(20) DEFAULT NULL,
  `date_recrutement` date DEFAULT NULL,
  `specialite` varchar(50) DEFAULT NULL,
  `diplome_principal` varchar(100) DEFAULT NULL,
  `autres_diplomes` text DEFAULT NULL,
  `experience_annees` int(11) DEFAULT 0,
  `situation_familiale` enum('Célibataire','Marié(e)','Divorcé(e)','Veuf/Veuve') DEFAULT NULL,
  `personne_urgence_nom` varchar(100) DEFAULT NULL,
  `personne_urgence_tel` varchar(15) DEFAULT NULL,
  `statut_emploi` enum('permanent','contractuel','vacataire') DEFAULT 'contractuel',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `professeurs`
--

INSERT INTO `professeurs` (`id`, `utilisateur_id`, `personne_id`, `matricule`, `date_recrutement`, `specialite`, `diplome_principal`, `autres_diplomes`, `experience_annees`, `situation_familiale`, `personne_urgence_nom`, `personne_urgence_tel`, `statut_emploi`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 4, 4, '2026PR0001', '2026-01-06', 'Mathématiques ', NULL, NULL, 0, NULL, NULL, NULL, 'permanent', '2026-01-06 11:14:12', '2026-01-06 11:18:45', NULL);

--
-- Déclencheurs `professeurs`
--
DELIMITER $$
CREATE TRIGGER `trg_professeur_before_insert` BEFORE INSERT ON `professeurs` FOR EACH ROW BEGIN
    -- Vérifier que la personne n'est pas déjà un élève
    IF EXISTS (SELECT 1 FROM eleves WHERE personne_id = NEW.personne_id AND deleted_at IS NULL) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cette personne est déjà enregistrée comme élève';
    END IF;
    
    -- Générer le matricule si non fourni
    IF NEW.matricule IS NULL OR NEW.matricule = '' THEN
        SET NEW.matricule = CONCAT('PROF', YEAR(CURRENT_DATE), LPAD((SELECT COUNT(*) + 1 FROM professeurs WHERE YEAR(created_at) = YEAR(CURRENT_DATE)), 4, '0'));
    END IF;
    
    -- Date de recrutement par défaut
    IF NEW.date_recrutement IS NULL THEN
        SET NEW.date_recrutement = CURRENT_DATE;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `personne_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','prof','eleve','parent') NOT NULL,
  `statut` enum('en_attente','actif','refuse','suspendu','archive') NOT NULL DEFAULT 'en_attente',
  `tentatives_connexion` int(11) DEFAULT 0,
  `compte_verrouille` tinyint(1) DEFAULT 0,
  `verrouille_jusqu_a` datetime DEFAULT NULL,
  `password_reset_token` varchar(100) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `validated_by` int(11) DEFAULT NULL,
  `validated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `personne_id`, `email`, `password_hash`, `role`, `statut`, `tentatives_connexion`, `compte_verrouille`, `verrouille_jusqu_a`, `password_reset_token`, `token_expiration`, `last_login`, `created_at`, `updated_at`, `created_by`, `validated_by`, `validated_at`, `deleted_at`) VALUES
(1, 1, 'admin@ecole.mg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'actif', 0, 0, NULL, NULL, NULL, '2026-01-05 00:47:08', '2026-01-05 00:45:57', '2026-01-05 00:47:08', NULL, NULL, '2026-01-05 00:45:57', NULL),
(2, 2, 'rakoto@gmail.com', '123456789', 'eleve', 'actif', 0, 0, NULL, NULL, NULL, NULL, '2026-01-05 11:37:03', NULL, NULL, NULL, NULL, NULL),
(3, 3, 'hamzah@gmail.com', '$2y$10$0h9FWIVsJWYEmaqJLl82/OPSuqLjCjomkKQzQ7w9/FPaMHUc70tIC', 'eleve', 'en_attente', 0, 0, NULL, NULL, NULL, NULL, '2026-01-06 07:51:29', NULL, NULL, NULL, NULL, NULL),
(4, 4, 'ibra@gmail.com', '$2y$10$nJJRidS5WORFaDdzXLm/.OjTZAklJxHJd3/RJDUeasgL5vLgyWCXi', 'prof', 'en_attente', 0, 0, NULL, NULL, NULL, NULL, '2026-01-06 11:14:12', NULL, NULL, NULL, NULL, NULL),
(5, 5, 'prof.rakoto@ceg-fm.mg', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'prof', 'actif', 0, 0, NULL, NULL, NULL, NULL, '2026-01-07 18:08:14', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_enseignements_professeurs`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_enseignements_professeurs` (
`enseignement_id` int(11)
,`professeur_id` int(11)
,`matricule` varchar(20)
,`prof_nom` varchar(50)
,`prof_prenom` varchar(50)
,`classe_id` int(11)
,`classe_nom` varchar(31)
,`matiere_id` int(11)
,`matiere_nom` varchar(50)
,`matiere_code` varchar(10)
,`coefficient` decimal(3,1)
,`volume_horaire_hebdo` decimal(4,1)
,`est_titulaire` tinyint(1)
,`annee_scolaire_id` int(11)
,`annee_scolaire` varchar(20)
,`created_at` datetime
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_professeurs_complet`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_professeurs_complet` (
`professeur_id` int(11)
,`matricule` varchar(20)
,`date_recrutement` date
,`specialite` varchar(50)
,`diplome_principal` varchar(100)
,`experience_annees` int(11)
,`statut_emploi` enum('permanent','contractuel','vacataire')
,`situation_familiale` enum('Célibataire','Marié(e)','Divorcé(e)','Veuf/Veuve')
,`personne_urgence_nom` varchar(100)
,`personne_urgence_tel` varchar(15)
,`nom` varchar(50)
,`prenom` varchar(50)
,`date_naissance` date
,`lieu_naissance` varchar(100)
,`sexe` enum('M','F')
,`telephone` varchar(15)
,`adresse` text
,`nationalite` varchar(50)
,`photo_path` varchar(255)
,`utilisateur_id` int(11)
,`email` varchar(150)
,`statut_compte` enum('en_attente','actif','refuse','suspendu','archive')
,`last_login` datetime
,`created_at` datetime
,`updated_at` datetime
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_enseignements_professeurs`
--
DROP TABLE IF EXISTS `v_enseignements_professeurs`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_enseignements_professeurs`  AS SELECT `e`.`id` AS `enseignement_id`, `e`.`professeur_id` AS `professeur_id`, `prof`.`matricule` AS `matricule`, `p`.`nom` AS `prof_nom`, `p`.`prenom` AS `prof_prenom`, `c`.`id` AS `classe_id`, concat(`c`.`niveau`,' ',`c`.`nom`) AS `classe_nom`, `m`.`id` AS `matiere_id`, `m`.`nom` AS `matiere_nom`, `m`.`code` AS `matiere_code`, `m`.`coefficient` AS `coefficient`, `e`.`volume_horaire_hebdo` AS `volume_horaire_hebdo`, `e`.`est_titulaire` AS `est_titulaire`, `a`.`id` AS `annee_scolaire_id`, `a`.`libelle` AS `annee_scolaire`, `e`.`created_at` AS `created_at` FROM (((((`enseignements` `e` join `professeurs` `prof` on(`e`.`professeur_id` = `prof`.`id`)) join `personnes` `p` on(`prof`.`personne_id` = `p`.`id`)) join `classes` `c` on(`e`.`classe_id` = `c`.`id`)) join `matieres` `m` on(`e`.`matiere_id` = `m`.`id`)) join `annee_scolaire` `a` on(`e`.`annee_scolaire_id` = `a`.`id`)) WHERE `e`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_professeurs_complet`
--
DROP TABLE IF EXISTS `v_professeurs_complet`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_professeurs_complet`  AS SELECT `prof`.`id` AS `professeur_id`, `prof`.`matricule` AS `matricule`, `prof`.`date_recrutement` AS `date_recrutement`, `prof`.`specialite` AS `specialite`, `prof`.`diplome_principal` AS `diplome_principal`, `prof`.`experience_annees` AS `experience_annees`, `prof`.`statut_emploi` AS `statut_emploi`, `prof`.`situation_familiale` AS `situation_familiale`, `prof`.`personne_urgence_nom` AS `personne_urgence_nom`, `prof`.`personne_urgence_tel` AS `personne_urgence_tel`, `p`.`nom` AS `nom`, `p`.`prenom` AS `prenom`, `p`.`date_naissance` AS `date_naissance`, `p`.`lieu_naissance` AS `lieu_naissance`, `p`.`sexe` AS `sexe`, `p`.`telephone` AS `telephone`, `p`.`adresse` AS `adresse`, `p`.`nationalite` AS `nationalite`, `p`.`photo_path` AS `photo_path`, `u`.`id` AS `utilisateur_id`, `u`.`email` AS `email`, `u`.`statut` AS `statut_compte`, `u`.`last_login` AS `last_login`, `prof`.`created_at` AS `created_at`, `prof`.`updated_at` AS `updated_at` FROM ((`professeurs` `prof` join `personnes` `p` on(`prof`.`personne_id` = `p`.`id`)) join `utilisateurs` `u` on(`prof`.`utilisateur_id` = `u`.`id`)) WHERE `prof`.`deleted_at` is null ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `affectations_prof`
--
ALTER TABLE `affectations_prof`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prof_id` (`prof_id`,`classe_id`,`matiere_id`,`annee_scolaire_id`),
  ADD KEY `matiere_id` (`matiere_id`),
  ADD KEY `annee_scolaire_id` (`annee_scolaire_id`),
  ADD KEY `idx_prof_annee` (`prof_id`,`annee_scolaire_id`),
  ADD KEY `idx_classe_matiere` (`classe_id`,`matiere_id`);

--
-- Index pour la table `annee_scolaire`
--
ALTER TABLE `annee_scolaire`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `libelle` (`libelle`),
  ADD KEY `idx_actif` (`actif`),
  ADD KEY `idx_dates` (`date_debut`,`date_fin`);

--
-- Index pour la table `appel`
--
ALTER TABLE `appel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date_appel` (`date_appel`,`inscription_id`,`affectation_id`,`heure_cours`),
  ADD KEY `affectation_id` (`affectation_id`),
  ADD KEY `saisie_par` (`saisie_par`),
  ADD KEY `idx_date_appel` (`date_appel`),
  ADD KEY `idx_inscription` (`inscription_id`),
  ADD KEY `idx_present` (`present`);

--
-- Index pour la table `bulletins`
--
ALTER TABLE `bulletins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inscription_id` (`inscription_id`,`periode_id`),
  ADD KEY `periode_id` (`periode_id`),
  ADD KEY `genere_par` (`genere_par`),
  ADD KEY `idx_inscription_periode` (`inscription_id`,`periode_id`);

--
-- Index pour la table `cahier_de_texte`
--
ALTER TABLE `cahier_de_texte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_cours` (`date_cours`),
  ADD KEY `idx_affectation` (`affectation_id`),
  ADD KEY `idx_statut` (`statut`);

--
-- Index pour la table `certificats`
--
ALTER TABLE `certificats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_certificat` (`numero_certificat`),
  ADD KEY `genere_par` (`genere_par`),
  ADD KEY `idx_inscription` (`inscription_id`),
  ADD KEY `idx_type` (`type_certificat`),
  ADD KEY `idx_date_generation` (`date_generation`);

--
-- Index pour la table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom` (`nom`,`niveau`,`annee_scolaire_id`),
  ADD KEY `idx_annee` (`annee_scolaire_id`),
  ADD KEY `idx_niveau` (`niveau`);

--
-- Index pour la table `eleves`
--
ALTER TABLE `eleves`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`),
  ADD UNIQUE KEY `matricule` (`matricule`),
  ADD KEY `idx_matricule` (`matricule`),
  ADD KEY `idx_personne` (`personne_id`);

--
-- Index pour la table `emploi_du_temps`
--
ALTER TABLE `emploi_du_temps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `classe_id` (`classe_id`,`jour`,`heure_debut`,`annee_scolaire_id`),
  ADD KEY `affectation_id` (`affectation_id`),
  ADD KEY `idx_classe_jour` (`classe_id`,`jour`),
  ADD KEY `idx_annee` (`annee_scolaire_id`),
  ADD KEY `idx_actif` (`actif`);

--
-- Index pour la table `enseignements`
--
ALTER TABLE `enseignements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `professeur_id` (`professeur_id`,`classe_id`,`matiere_id`,`annee_scolaire_id`),
  ADD KEY `matiere_id` (`matiere_id`),
  ADD KEY `idx_prof_annee` (`professeur_id`,`annee_scolaire_id`),
  ADD KEY `idx_classe_matiere` (`classe_id`,`matiere_id`),
  ADD KEY `idx_enseignements_annee` (`annee_scolaire_id`);

--
-- Index pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `eleve_id` (`eleve_id`,`annee_scolaire_id`),
  ADD KEY `annee_scolaire_id` (`annee_scolaire_id`),
  ADD KEY `idx_eleve_annee` (`eleve_id`,`annee_scolaire_id`),
  ADD KEY `idx_classe` (`classe_id`),
  ADD KEY `idx_statut` (`statut`);

--
-- Index pour la table `logs_audit`
--
ALTER TABLE `logs_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_table_record` (`table_name`,`record_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Index pour la table `matieres`
--
ALTER TABLE `matieres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`);

--
-- Index pour la table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `periode_id` (`periode_id`),
  ADD KEY `idx_inscription_periode` (`inscription_id`,`periode_id`),
  ADD KEY `idx_affectation` (`affectation_id`),
  ADD KEY `idx_date_eval` (`date_evaluation`);

--
-- Index pour la table `parametres_etablissement`
--
ALTER TABLE `parametres_etablissement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `annee_scolaire_active_id` (`annee_scolaire_active_id`);

--
-- Index pour la table `periodes`
--
ALTER TABLE `periodes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `annee_scolaire_id` (`annee_scolaire_id`,`type_periode`,`numero`),
  ADD KEY `idx_annee_periode` (`annee_scolaire_id`,`numero`);

--
-- Index pour la table `personnes`
--
ALTER TABLE `personnes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nom_prenom` (`nom`,`prenom`),
  ADD KEY `idx_deleted` (`deleted_at`),
  ADD KEY `idx_personnes_nom_complet` (`nom`,`prenom`);

--
-- Index pour la table `professeurs`
--
ALTER TABLE `professeurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`),
  ADD UNIQUE KEY `matricule` (`matricule`),
  ADD KEY `personne_id` (`personne_id`),
  ADD KEY `idx_matricule` (`matricule`),
  ADD KEY `idx_statut` (`statut_emploi`),
  ADD KEY `idx_professeurs_specialite` (`specialite`),
  ADD KEY `idx_professeurs_statut_emploi` (`statut_emploi`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personne_id` (`personne_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `validated_by` (`validated_by`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_role_statut` (`role`,`statut`),
  ADD KEY `idx_deleted` (`deleted_at`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `affectations_prof`
--
ALTER TABLE `affectations_prof`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `annee_scolaire`
--
ALTER TABLE `annee_scolaire`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `appel`
--
ALTER TABLE `appel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `bulletins`
--
ALTER TABLE `bulletins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cahier_de_texte`
--
ALTER TABLE `cahier_de_texte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `certificats`
--
ALTER TABLE `certificats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `eleves`
--
ALTER TABLE `eleves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `emploi_du_temps`
--
ALTER TABLE `emploi_du_temps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `enseignements`
--
ALTER TABLE `enseignements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `logs_audit`
--
ALTER TABLE `logs_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `matieres`
--
ALTER TABLE `matieres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `parametres_etablissement`
--
ALTER TABLE `parametres_etablissement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `periodes`
--
ALTER TABLE `periodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `personnes`
--
ALTER TABLE `personnes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `professeurs`
--
ALTER TABLE `professeurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `affectations_prof`
--
ALTER TABLE `affectations_prof`
  ADD CONSTRAINT `affectations_prof_ibfk_1` FOREIGN KEY (`prof_id`) REFERENCES `professeurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `affectations_prof_ibfk_2` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `affectations_prof_ibfk_3` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`),
  ADD CONSTRAINT `affectations_prof_ibfk_4` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaire` (`id`);

--
-- Contraintes pour la table `appel`
--
ALTER TABLE `appel`
  ADD CONSTRAINT `appel_ibfk_1` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appel_ibfk_2` FOREIGN KEY (`affectation_id`) REFERENCES `affectations_prof` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appel_ibfk_3` FOREIGN KEY (`saisie_par`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `bulletins`
--
ALTER TABLE `bulletins`
  ADD CONSTRAINT `bulletins_ibfk_1` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulletins_ibfk_2` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bulletins_ibfk_3` FOREIGN KEY (`genere_par`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `cahier_de_texte`
--
ALTER TABLE `cahier_de_texte`
  ADD CONSTRAINT `cahier_de_texte_ibfk_1` FOREIGN KEY (`affectation_id`) REFERENCES `affectations_prof` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `certificats`
--
ALTER TABLE `certificats`
  ADD CONSTRAINT `certificats_ibfk_1` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificats_ibfk_2` FOREIGN KEY (`genere_par`) REFERENCES `utilisateurs` (`id`);

--
-- Contraintes pour la table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaire` (`id`);

--
-- Contraintes pour la table `eleves`
--
ALTER TABLE `eleves`
  ADD CONSTRAINT `eleves_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eleves_ibfk_2` FOREIGN KEY (`personne_id`) REFERENCES `personnes` (`id`);

--
-- Contraintes pour la table `emploi_du_temps`
--
ALTER TABLE `emploi_du_temps`
  ADD CONSTRAINT `emploi_du_temps_ibfk_1` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `emploi_du_temps_ibfk_2` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaire` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `emploi_du_temps_ibfk_3` FOREIGN KEY (`affectation_id`) REFERENCES `affectations_prof` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `enseignements`
--
ALTER TABLE `enseignements`
  ADD CONSTRAINT `enseignements_ibfk_1` FOREIGN KEY (`professeur_id`) REFERENCES `professeurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enseignements_ibfk_2` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `enseignements_ibfk_3` FOREIGN KEY (`matiere_id`) REFERENCES `matieres` (`id`),
  ADD CONSTRAINT `enseignements_ibfk_4` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaire` (`id`);

--
-- Contraintes pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`eleve_id`) REFERENCES `eleves` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`classe_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `inscriptions_ibfk_3` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaire` (`id`);

--
-- Contraintes pour la table `logs_audit`
--
ALTER TABLE `logs_audit`
  ADD CONSTRAINT `logs_audit_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_3` FOREIGN KEY (`affectation_id`) REFERENCES `affectations_prof` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `parametres_etablissement`
--
ALTER TABLE `parametres_etablissement`
  ADD CONSTRAINT `parametres_etablissement_ibfk_1` FOREIGN KEY (`annee_scolaire_active_id`) REFERENCES `annee_scolaire` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `periodes`
--
ALTER TABLE `periodes`
  ADD CONSTRAINT `periodes_ibfk_1` FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaire` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `professeurs`
--
ALTER TABLE `professeurs`
  ADD CONSTRAINT `professeurs_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `professeurs_ibfk_2` FOREIGN KEY (`personne_id`) REFERENCES `personnes` (`id`);

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`personne_id`) REFERENCES `personnes` (`id`),
  ADD CONSTRAINT `utilisateurs_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `utilisateurs_ibfk_3` FOREIGN KEY (`validated_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
