<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/admin/inscription.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <link rel="stylesheet" href="<?= $basePath ?>styles/style.css">
    <title>inscription eleve</title>
</head>
<body>
   <div class="parent">
        <?php 
            require_once('../include/header.php'); 
            require_once "../include/db.php"; 
        ?>
<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $matricule = trim($_POST['matricule'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $classe_id = (int)($_POST['classe_id'] ?? 0);
    $date_inscription = $_POST['date_inscription'] ?? '';

    if (
        !$matricule || !$nom || !$prenom ||
        !$date_naissance || !$sexe ||
        !$classe_id || !$date_inscription
    ) {
        exit("Champs manquants");
    }

    // Vérif doublon matricule
    $check = $pdo->prepare("SELECT id FROM eleves WHERE matricule = ?");
    $check->execute([$matricule]);
    if ($check->fetch()) {
        exit("Matricule déjà utilisé");
    }

    // =============================
    // 1️⃣ CRÉATION UTILISATEUR
    // =============================
    $username = strtolower($prenom . '.' . $nom);
    $password = password_hash("123456", PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (username, password_hash, role)
        VALUES (?, ?, 'eleve')
    ");
    $stmt->execute([$username, $password]);

    $utilisateur_id = $pdo->lastInsertId();

    // =============================
    // 2️⃣ CRÉATION ÉLÈVE (CORRIGÉ)
    // =============================
    $stmt = $pdo->prepare("
        INSERT INTO eleves
        (utilisateur_id, matricule, nom, prenom, date_naissance, sexe, classe_id, date_inscription)
        VALUES
        (:utilisateur_id, :matricule, :nom, :prenom, :date_naissance, :sexe, :classe_id, :date_inscription)
    ");

    $stmt->execute([
        ':utilisateur_id'   => $utilisateur_id,
        ':matricule'        => $matricule,
        ':nom'              => $nom,
        ':prenom'           => $prenom,
        ':date_naissance'   => $date_naissance,
        ':sexe'             => $sexe,
        ':classe_id'        => $classe_id,
        ':date_inscription' => $date_inscription
    ]);

    echo "Élève inscrit avec succès";
}
?>



        <div class="div3">
            <div class="page">
                <h2>Inscription / Réinscription Élève</h2>

<form method="POST">

    <label>Matricule</label>
    <input type="text" name="matricule" required>

    <label>Nom</label>
    <input type="text" name="nom" required>

    <label>Prénom</label>
    <input type="text" name="prenom" required>

    <label>Date de naissance</label>
    <input type="date" name="date_naissance" required>

    <label>Sexe</label>
    <label><input type="radio" name="sexe" value="M" required> M</label>
    <label><input type="radio" name="sexe" value="F"> F</label>

    <label>Classe</label>
    <select name="classe_id" required>
        <option value="" disabled selected>-- Choisir --</option>
        <?php
        $stmt = $pdo->query("
            SELECT c.id, c.niveau, c.section
            FROM classes c
            JOIN annee_scolaire a ON a.id = c.annee_scolaire_id
            WHERE a.actif = 1
            ORDER BY c.niveau, c.section
        ");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $niveau = (int)$row['niveau'];
            $suffixe = ($niveau === 1) ? 'er' : 'ème';

            echo "<option value='{$row['id']}'>
                    {$row['niveau']}{$suffixe} {$row['section']}
                  </option>";
        }
        ?>
    </select>

    <label>Date d'inscription</label>
    <input type="date" name="date_inscription" required>

    <button type="submit">Enregistrer</button>
</form>


            </div>

        </div>
    </div>
</body>
