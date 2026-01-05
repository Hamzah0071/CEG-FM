-- =========================
-- BASE DE DONNÉES CEG-FM
-- Version corrigée et complète
-- =========================

-- Supprimer la base si elle existe et la recréer
DROP DATABASE IF EXISTS `ceg_fm`;
CREATE DATABASE `ceg_fm` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ceg_fm`;

-- =========================
-- 1. PERSONNES (Données communes à tous)
-- =========================
CREATE TABLE `personnes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(50) NOT NULL,
  `prenom` VARCHAR(50) NOT NULL,
  `date_naissance` DATE NOT NULL,
  `lieu_naissance` VARCHAR(100),
  `sexe` ENUM('M','F') NOT NULL,
  `telephone` VARCHAR(15),
  `adresse` TEXT,
  `photo_path` VARCHAR(255),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  INDEX `idx_nom_prenom` (`nom`, `prenom`),
  INDEX `idx_deleted` (`deleted_at`)
) ENGINE=InnoDB;

-- =========================
-- 2. UTILISATEURS (Authentification)
-- =========================
CREATE TABLE `utilisateurs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `personne_id` INT NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','prof','eleve','parent') NOT NULL,
  `statut` ENUM('en_attente','actif','refuse','suspendu','archive') NOT NULL DEFAULT 'en_attente',
  
  -- Sécurité
  `tentatives_connexion` INT DEFAULT 0,
  `compte_verrouille` TINYINT(1) DEFAULT 0,
  `verrouille_jusqu_a` DATETIME,
  `password_reset_token` VARCHAR(100),
  `token_expiration` DATETIME,
  `last_login` DATETIME,
  
  -- Audit
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `created_by` INT,
  `validated_by` INT,
  `validated_at` DATETIME,
  `deleted_at` DATETIME,
  
  FOREIGN KEY (`personne_id`) REFERENCES `personnes`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`created_by`) REFERENCES `utilisateurs`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`validated_by`) REFERENCES `utilisateurs`(`id`) ON DELETE SET NULL,
  
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  INDEX `idx_role_statut` (`role`, `statut`),
  INDEX `idx_deleted` (`deleted_at`)
) ENGINE=InnoDB;

-- =========================
-- 3. ANNÉE SCOLAIRE
-- =========================
CREATE TABLE `annee_scolaire` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `libelle` VARCHAR(20) NOT NULL UNIQUE,
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `actif` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  CHECK (`date_debut` < `date_fin`),
  INDEX `idx_actif` (`actif`),
  INDEX `idx_dates` (`date_debut`, `date_fin`)
) ENGINE=InnoDB;

-- ⚠️ SOLUTION : Utiliser une procédure stockée au lieu de triggers
-- Cette procédure doit être appelée AVANT l'insertion/update d'une année
DELIMITER $$
CREATE PROCEDURE `set_annee_active`(IN p_annee_id INT)
BEGIN
    -- Désactiver toutes les autres années
    UPDATE annee_scolaire SET actif = 0 WHERE actif = 1 AND id != p_annee_id;
    
    -- Activer l'année demandée
    UPDATE annee_scolaire SET actif = 1 WHERE id = p_annee_id;
END$$
DELIMITER ;

-- Alternative : Validation au niveau application
-- Mais on peut aussi utiliser un trigger AFTER (plus propre)
DELIMITER $$
CREATE TRIGGER `trg_annee_scolaire_check_unique_actif` 
BEFORE INSERT ON `annee_scolaire` FOR EACH ROW 
BEGIN
    DECLARE count_actif INT;
    
    -- Si on insère une année active, vérifier qu'il n'y en a pas déjà une
    IF NEW.actif = 1 THEN
        SELECT COUNT(*) INTO count_actif 
        FROM annee_scolaire 
        WHERE actif = 1 AND deleted_at IS NULL;
        
        IF count_actif > 0 THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Une année scolaire est déjà active. Désactivez-la d\'abord.';
        END IF;
    END IF;
END$$

CREATE TRIGGER `trg_annee_scolaire_check_unique_actif_update` 
BEFORE UPDATE ON `annee_scolaire` FOR EACH ROW 
BEGIN
    DECLARE count_actif INT;
    
    -- Si on active une année, vérifier qu'il n'y en a pas déjà une autre d'active
    IF NEW.actif = 1 AND OLD.actif = 0 THEN
        SELECT COUNT(*) INTO count_actif 
        FROM annee_scolaire 
        WHERE actif = 1 AND id != NEW.id AND deleted_at IS NULL;
        
        IF count_actif > 0 THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Une année scolaire est déjà active. Désactivez-la d\'abord.';
        END IF;
    END IF;
END$$
DELIMITER ;

-- =========================
-- 4. PÉRIODES (Trimestres/Semestres)
-- =========================
CREATE TABLE `periodes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `annee_scolaire_id` INT NOT NULL,
  `nom` VARCHAR(20) NOT NULL,
  `type_periode` ENUM('trimestre','semestre','quadrimestre') NOT NULL,
  `numero` INT NOT NULL,
  `date_debut` DATE NOT NULL,
  `date_fin` DATE NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  CHECK (`date_debut` < `date_fin`),
  CHECK (`numero` > 0),
  UNIQUE KEY (`annee_scolaire_id`, `type_periode`, `numero`),
  FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaire`(`id`) ON DELETE CASCADE,
  
  INDEX `idx_annee_periode` (`annee_scolaire_id`, `numero`)
) ENGINE=InnoDB;

-- =========================
-- 5. CLASSES
-- =========================
CREATE TABLE `classes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(20) NOT NULL,
  `niveau` VARCHAR(10) NOT NULL,
  `annee_scolaire_id` INT NOT NULL,
  `effectif_max` INT DEFAULT 50,
  `salle_principale` VARCHAR(20),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  UNIQUE KEY (`nom`, `niveau`, `annee_scolaire_id`),
  FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaire`(`id`) ON DELETE RESTRICT,
  
  INDEX `idx_annee` (`annee_scolaire_id`),
  INDEX `idx_niveau` (`niveau`)
) ENGINE=InnoDB;

-- =========================
-- 6. MATIÈRES
-- =========================
CREATE TABLE `matieres` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(10) NOT NULL UNIQUE,
  `nom` VARCHAR(50) NOT NULL,
  `coefficient` DECIMAL(3,1) NOT NULL DEFAULT 1.0,
  `categorie` VARCHAR(30),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  CHECK (`coefficient` > 0),
  INDEX `idx_code` (`code`)
) ENGINE=InnoDB;

-- =========================
-- 7. PROFESSEURS
-- =========================
CREATE TABLE `professeurs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `utilisateur_id` INT NOT NULL UNIQUE,
  `personne_id` INT NOT NULL,
  `matricule` VARCHAR(20) UNIQUE,
  `date_recrutement` DATE,
  `specialite` VARCHAR(50),
  `statut_emploi` ENUM('permanent','contractuel','vacataire') DEFAULT 'contractuel',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`personne_id`) REFERENCES `personnes`(`id`) ON DELETE RESTRICT,
  
  INDEX `idx_matricule` (`matricule`),
  INDEX `idx_statut` (`statut_emploi`)
) ENGINE=InnoDB;

-- =========================
-- 8. ÉLÈVES
-- =========================
CREATE TABLE `eleves` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `utilisateur_id` INT NOT NULL UNIQUE,
  `personne_id` INT NOT NULL,
  `matricule` VARCHAR(20) UNIQUE,
  `date_inscription` DATE NOT NULL,
  `nom_parent` VARCHAR(100),
  `telephone_parent` VARCHAR(15),
  `email_parent` VARCHAR(150),
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`personne_id`) REFERENCES `personnes`(`id`) ON DELETE RESTRICT,
  
  INDEX `idx_matricule` (`matricule`),
  INDEX `idx_personne` (`personne_id`)
) ENGINE=InnoDB;

-- =========================
-- 9. INSCRIPTIONS (HISTORIQUE CLASSE/ANNÉE)
-- =========================
CREATE TABLE `inscriptions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `eleve_id` INT NOT NULL,
  `classe_id` INT NOT NULL,
  `annee_scolaire_id` INT NOT NULL,
  `date_inscription` DATE NOT NULL,
  `statut` ENUM('actif','redouble','abandonne','transfere','diplome') DEFAULT 'actif',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  UNIQUE KEY (`eleve_id`, `annee_scolaire_id`),
  FOREIGN KEY (`eleve_id`) REFERENCES `eleves`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`classe_id`) REFERENCES `classes`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaire`(`id`) ON DELETE RESTRICT,
  
  INDEX `idx_eleve_annee` (`eleve_id`, `annee_scolaire_id`),
  INDEX `idx_classe` (`classe_id`),
  INDEX `idx_statut` (`statut`)
) ENGINE=InnoDB;

-- =========================
-- 10. AFFECTATIONS PROF
-- =========================
CREATE TABLE `affectations_prof` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `prof_id` INT NOT NULL,
  `classe_id` INT NOT NULL,
  `matiere_id` INT NOT NULL,
  `annee_scolaire_id` INT NOT NULL,
  `volume_horaire_hebdo` DECIMAL(4,1),
  `est_titulaire` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  UNIQUE KEY (`prof_id`, `classe_id`, `matiere_id`, `annee_scolaire_id`),
  FOREIGN KEY (`prof_id`) REFERENCES `professeurs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`classe_id`) REFERENCES `classes`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`matiere_id`) REFERENCES `matieres`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaire`(`id`) ON DELETE RESTRICT,
  
  INDEX `idx_prof_annee` (`prof_id`, `annee_scolaire_id`),
  INDEX `idx_classe_matiere` (`classe_id`, `matiere_id`)
) ENGINE=InnoDB;

-- =========================
-- 11. NOTES
-- =========================
CREATE TABLE `notes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `inscription_id` INT NOT NULL,
  `periode_id` INT NOT NULL,
  `affectation_id` INT NOT NULL,
  `type_note` ENUM('devoir','examen','composition','controle_continu','oral') NOT NULL,
  `valeur` DECIMAL(4,2) NOT NULL,
  `sur_combien` DECIMAL(4,2) NOT NULL DEFAULT 20.00,
  `date_evaluation` DATE NOT NULL,
  `date_saisie` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `commentaire` TEXT,
  `publiee` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  CHECK (`valeur` >= 0 AND `valeur` <= `sur_combien`),
  CHECK (`sur_combien` > 0),
  FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`periode_id`) REFERENCES `periodes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`affectation_id`) REFERENCES `affectations_prof`(`id`) ON DELETE CASCADE,
  
  INDEX `idx_inscription_periode` (`inscription_id`, `periode_id`),
  INDEX `idx_affectation` (`affectation_id`),
  INDEX `idx_date_eval` (`date_evaluation`)
) ENGINE=InnoDB;

-- =========================
-- 12. BULLETINS
-- =========================
CREATE TABLE `bulletins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `inscription_id` INT NOT NULL,
  `periode_id` INT NOT NULL,
  `moyenne_generale` DECIMAL(5,2),
  `rang` INT,
  `effectif_classe` INT,
  `appreciation_generale` TEXT,
  `decision_conseil` ENUM('passage','redoublement','reorientation'),
  `date_generation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `genere_par` INT NOT NULL,
  `publie` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  CHECK (`moyenne_generale` >= 0 AND `moyenne_generale` <= 20),
  CHECK (`rang` > 0),
  UNIQUE KEY (`inscription_id`, `periode_id`),
  FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`periode_id`) REFERENCES `periodes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`genere_par`) REFERENCES `utilisateurs`(`id`) ON DELETE RESTRICT,
  
  INDEX `idx_inscription_periode` (`inscription_id`, `periode_id`)
) ENGINE=InnoDB;

-- =========================
-- 13. APPEL (Présences)
-- =========================
CREATE TABLE `appel` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `date_appel` DATE NOT NULL,
  `inscription_id` INT NOT NULL,
  `affectation_id` INT NOT NULL,
  `heure_cours` TIME NOT NULL,
  `present` TINYINT(1) NOT NULL DEFAULT 1,
  `retard` TINYINT(1) NOT NULL DEFAULT 0,
  `justifie` TINYINT(1) NOT NULL DEFAULT 0,
  `motif` TEXT,
  `saisie_par` INT NOT NULL,
  `saisie_le` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  
  UNIQUE KEY (`date_appel`, `inscription_id`, `affectation_id`, `heure_cours`),
  FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`affectation_id`) REFERENCES `affectations_prof`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`saisie_par`) REFERENCES `utilisateurs`(`id`) ON DELETE RESTRICT,
  
  INDEX `idx_date_appel` (`date_appel`),
  INDEX `idx_inscription` (`inscription_id`),
  INDEX `idx_present` (`present`)
) ENGINE=InnoDB;

-- =========================
-- 14. CAHIER DE TEXTE
-- =========================
CREATE TABLE `cahier_de_texte` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `date_cours` DATE NOT NULL,
  `affectation_id` INT NOT NULL,
  `heure_debut` TIME NOT NULL,
  `heure_fin` TIME NOT NULL,
  `titre` VARCHAR(100),
  `contenu` TEXT NOT NULL,
  `devoir` TEXT,
  `date_rendu_devoir` DATE,
  `fichier_joint_path` VARCHAR(255),
  `statut` ENUM('brouillon','publie','archive') DEFAULT 'brouillon',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  FOREIGN KEY (`affectation_id`) REFERENCES `affectations_prof`(`id`) ON DELETE CASCADE,
  
  INDEX `idx_date_cours` (`date_cours`),
  INDEX `idx_affectation` (`affectation_id`),
  INDEX `idx_statut` (`statut`)
) ENGINE=InnoDB;

-- =========================
-- 15. EMPLOI DU TEMPS
-- =========================
CREATE TABLE `emploi_du_temps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `classe_id` INT NOT NULL,
  `annee_scolaire_id` INT NOT NULL,
  `jour` ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi') NOT NULL,
  `heure_debut` TIME NOT NULL,
  `heure_fin` TIME NOT NULL,
  `affectation_id` INT NOT NULL,
  `salle` VARCHAR(20),
  `actif` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME,
  
  CHECK (`heure_debut` < `heure_fin`),
  UNIQUE KEY (`classe_id`, `jour`, `heure_debut`, `annee_scolaire_id`),
  FOREIGN KEY (`classe_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`annee_scolaire_id`) REFERENCES `annee_scolaire`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`affectation_id`) REFERENCES `affectations_prof`(`id`) ON DELETE CASCADE,
  
  INDEX `idx_classe_jour` (`classe_id`, `jour`),
  INDEX `idx_annee` (`annee_scolaire_id`),
  INDEX `idx_actif` (`actif`)
) ENGINE=InnoDB;

-- =========================
-- 16. CERTIFICATS
-- =========================
CREATE TABLE `certificats` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `inscription_id` INT NOT NULL,
  `type_certificat` ENUM('scolarite','inscription','presence','reussite','transfert') NOT NULL,
  `annee_reference` VARCHAR(20),
  `contenu_supplementaire` TEXT,
  `fichier_pdf_path` VARCHAR(255),
  `date_generation` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `genere_par` INT NOT NULL,
  `numero_certificat` VARCHAR(50) UNIQUE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`inscription_id`) REFERENCES `inscriptions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`genere_par`) REFERENCES `utilisateurs`(`id`) ON DELETE RESTRICT,
  
  INDEX `idx_inscription` (`inscription_id`),
  INDEX `idx_type` (`type_certificat`),
  INDEX `idx_date_generation` (`date_generation`)
) ENGINE=InnoDB;

-- =========================
-- 17. LOGS AUDIT
-- =========================
CREATE TABLE `logs_audit` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `utilisateur_id` INT,
  `table_name` VARCHAR(50) NOT NULL,
  `record_id` INT NOT NULL,
  `action` ENUM('CREATE','UPDATE','DELETE','LOGIN','LOGOUT') NOT NULL,
  `ancien_contenu` TEXT,
  `nouveau_contenu` TEXT,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs`(`id`) ON DELETE SET NULL,
  
  INDEX `idx_utilisateur` (`utilisateur_id`),
  INDEX `idx_table_record` (`table_name`, `record_id`),
  INDEX `idx_action` (`action`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB;

-- =========================
-- 18. PARAMÈTRES ÉTABLISSEMENT
-- =========================
CREATE TABLE `parametres_etablissement` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom_etablissement` VARCHAR(100) NOT NULL,
  `adresse` TEXT,
  `telephone` VARCHAR(15),
  `email` VARCHAR(100),
  `logo_path` VARCHAR(255),
  `annee_scolaire_active_id` INT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`annee_scolaire_active_id`) REFERENCES `annee_scolaire`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================
-- PROCÉDURE : CALCUL DES RANGS
-- =========================
DELIMITER $$
CREATE PROCEDURE `calculer_rangs_bulletins` (IN `p_periode_id` INT)
BEGIN
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
DELIMITER ;

-- =========================
-- DONNÉES INITIALES
-- =========================

-- 1. Créer l'admin
INSERT INTO `personnes` (`nom`, `prenom`, `date_naissance`, `sexe`) 
VALUES ('Admin', 'Système', '1990-01-01', 'M');

INSERT INTO `utilisateurs` (`personne_id`, `email`, `password_hash`, `role`, `statut`, `validated_at`) 
VALUES (
    1, 
    'admin@ecole.mg', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin', 
    'actif',
    CURRENT_TIMESTAMP
);
-- Mot de passe: password

-- 2. Année scolaire 2024-2025
INSERT INTO `annee_scolaire` (`libelle`, `date_debut`, `date_fin`, `actif`) 
VALUES ('2024-2025', '2024-09-01', '2025-06-30', 1);

-- 3. Matières
INSERT INTO `matieres` (`code`, `nom`, `coefficient`, `categorie`) VALUES
('MATH', 'Mathématiques', 4.0, 'Sciences'),
('PC', 'Physique-Chimie', 3.0, 'Sciences'),
('SVT', 'Sciences de la Vie et de la Terre', 2.0, 'Sciences'),
('FR', 'Français', 4.0, 'Lettres'),
('ANG', 'Anglais', 2.0, 'Langues'),
('HG', 'Histoire-Géographie', 2.0, 'Sciences Humaines'),
('EPS', 'Éducation Physique et Sportive', 1.0, 'Sport'),
('INFO', 'Informatique', 2.0, 'Technologie');

-- 4. Périodes (Trimestres)
INSERT INTO `periodes` (`annee_scolaire_id`, `nom`, `type_periode`, `numero`, `date_debut`, `date_fin`) VALUES
(1, 'Trimestre 1', 'trimestre', 1, '2024-09-01', '2024-12-15'),
(1, 'Trimestre 2', 'trimestre', 2, '2025-01-06', '2025-03-31'),
(1, 'Trimestre 3', 'trimestre', 3, '2025-04-01', '2025-06-30');

-- 5. Classes
INSERT INTO `classes` (`nom`, `niveau`, `annee_scolaire_id`, `effectif_max`) VALUES
('A', '6ème', 1, 40),
('B', '6ème', 1, 40),
('A', '5ème', 1, 40),
('B', '5ème', 1, 40),
('A', '4ème', 1, 40),
('B', '4ème', 1, 40),
('A', '3ème', 1, 40),
('B', '3ème', 1, 40);

-- 6. Paramètres établissement
INSERT INTO `parametres_etablissement` (`nom_etablissement`, `adresse`, `telephone`, `email`, `annee_scolaire_active_id`) 
VALUES ('CEG FM', 'Antsiranana, Madagascar', '+261 34 00 000 00', 'contact@ceg-fm.mg', 1);