-- =========================
-- SUPPRESSION DES TABLES
-- =========================
DROP TABLE IF EXISTS emploi_du_temps;
DROP TABLE IF EXISTS cahier_de_texte;
DROP TABLE IF EXISTS appel;
DROP TABLE IF EXISTS absences;
DROP TABLE IF EXISTS bulletins;
DROP TABLE IF EXISTS notes;
DROP TABLE IF EXISTS periodes;
DROP TABLE IF EXISTS affectations_prof;
DROP TABLE IF EXISTS eleves;
DROP TABLE IF EXISTS professeurs;
DROP TABLE IF EXISTS matieres;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS annee_scolaire;
DROP TABLE IF EXISTS utilisateurs;

-- =========================
-- UTILISATEURS
-- =========================
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'prof', 'eleve', 'guest') NOT NULL,
    statut ENUM('en_attente','actif','refuse') NOT NULL DEFAULT 'en_attente',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- ANNÉE SCOLAIRE
-- =========================
CREATE TABLE annee_scolaire (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(20) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    actif TINYINT(1) NOT NULL DEFAULT 1,
    CHECK (date_debut < date_fin)
);

-- =========================
-- CLASSES
-- =========================
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(20) NOT NULL,
    niveau VARCHAR(10) NOT NULL,
    annee_scolaire_id INT NOT NULL,
    UNIQUE (nom, niveau, annee_scolaire_id),
    FOREIGN KEY (annee_scolaire_id)
        REFERENCES annee_scolaire(id)
        ON DELETE RESTRICT
);

-- =========================
-- MATIÈRES
-- =========================
CREATE TABLE matieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    nom VARCHAR(50) NOT NULL,
    coefficient DECIMAL(3,1) NOT NULL DEFAULT 1
);

-- =========================
-- PROFESSEURS
-- =========================
CREATE TABLE professeurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL UNIQUE,
    matricule VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    date_naissance DATE NOT NULL,
    sexe ENUM('M','F') NOT NULL,
    telephone VARCHAR(15),
    date_recrutement DATE,
    FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs(id)
        ON DELETE CASCADE
);

-- =========================
-- ÉLÈVES
-- =========================
CREATE TABLE eleves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL UNIQUE,
    matricule VARCHAR(20) NOT NULL UNIQUE,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    date_naissance DATE NOT NULL,
    sexe ENUM('M','F') NOT NULL,
    classe_id INT NOT NULL,
    date_inscription DATE NOT NULL,
    FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs(id)
        ON DELETE CASCADE,
    FOREIGN KEY (classe_id)
        REFERENCES classes(id)
        ON DELETE RESTRICT
);

-- =========================
-- AFFECTATIONS PROF
-- =========================
CREATE TABLE affectations_prof (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prof_id INT NOT NULL,
    classe_id INT NOT NULL,
    matiere_id INT NOT NULL,
    annee_scolaire_id INT NOT NULL,
    UNIQUE (prof_id, classe_id, matiere_id, annee_scolaire_id),
    INDEX idx_prof (prof_id),
    INDEX idx_classe (classe_id),
    INDEX idx_matiere (matiere_id),
    FOREIGN KEY (prof_id) REFERENCES professeurs(id) ON DELETE CASCADE,
    FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE RESTRICT,
    FOREIGN KEY (matiere_id) REFERENCES matieres(id) ON DELETE RESTRICT,
    FOREIGN KEY (annee_scolaire_id) REFERENCES annee_scolaire(id) ON DELETE RESTRICT
);

-- =========================
-- PÉRIODES
-- =========================
CREATE TABLE periodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    annee_scolaire_id INT NOT NULL,
    nom VARCHAR(20) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    CHECK (date_debut < date_fin),
    FOREIGN KEY (annee_scolaire_id)
        REFERENCES annee_scolaire(id)
        ON DELETE CASCADE
);

-- =========================
-- NOTES
-- =========================
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    eleve_id INT NOT NULL,
    periode_id INT NOT NULL,
    affectation_id INT NOT NULL,
    type_note ENUM('devoir','examen','composition') NOT NULL,
    valeur DECIMAL(4,2) NOT NULL,
    date_saisie DATE NOT NULL,
    commentaire TEXT,
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE,
    FOREIGN KEY (periode_id) REFERENCES periodes(id) ON DELETE CASCADE,
    FOREIGN KEY (affectation_id) REFERENCES affectations_prof(id) ON DELETE CASCADE
);

-- =========================
-- BULLETINS
-- =========================
CREATE TABLE bulletins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    eleve_id INT NOT NULL,
    periode_id INT NOT NULL,
    moyenne_generale DECIMAL(4,2) NOT NULL,
    rang VARCHAR(10),
    appreciation TEXT,
    date_generation DATE NOT NULL,
    UNIQUE (eleve_id, periode_id),
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE,
    FOREIGN KEY (periode_id) REFERENCES periodes(id) ON DELETE CASCADE
);

-- =========================
-- ABSENCES
-- =========================
CREATE TABLE absences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    eleve_id INT NOT NULL,
    date_absence DATE NOT NULL,
    justifiee TINYINT(1) NOT NULL DEFAULT 0,
    motif TEXT,
    saisie_par INT NOT NULL,
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE,
    FOREIGN KEY (saisie_par) REFERENCES utilisateurs(id) ON DELETE RESTRICT
);

-- =========================
-- APPEL
-- =========================
CREATE TABLE appel (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_appel DATE NOT NULL,
    eleve_id INT NOT NULL,
    affectation_id INT NOT NULL,
    present TINYINT(1) NOT NULL DEFAULT 1,
    retard TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE (date_appel, eleve_id, affectation_id),
    FOREIGN KEY (eleve_id) REFERENCES eleves(id) ON DELETE CASCADE,
    FOREIGN KEY (affectation_id) REFERENCES affectations_prof(id) ON DELETE CASCADE
);

-- =========================
-- CAHIER DE TEXTE
-- =========================
CREATE TABLE cahier_de_texte (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_cours DATE NOT NULL,
    affectation_id INT NOT NULL,
    contenu TEXT NOT NULL,
    devoir TEXT,
    FOREIGN KEY (affectation_id)
        REFERENCES affectations_prof(id)
        ON DELETE CASCADE
);

-- =========================
-- EMPLOI DU TEMPS
-- =========================
CREATE TABLE emploi_du_temps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classe_id INT NOT NULL,
    jour ENUM('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi') NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    affectation_id INT NOT NULL,
    salle VARCHAR(20),
    UNIQUE (classe_id, jour, heure_debut),
    FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (affectation_id) REFERENCES affectations_prof(id) ON DELETE CASCADE
);
