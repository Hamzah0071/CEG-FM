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
    $matricule        = trim($_POST["matricule"] ?? '');
    $nom              = trim($_POST["nom"] ?? '');
    $prenom           = trim($_POST["prenom"] ?? '');
    $date_naissance   = $_POST["date_naissance"] ?? '';
    $sexe             = $_POST["sexe"] ?? '';
    $classe_id        = (int)($_POST["classe_id"] ?? 0);
    $annee_id         = (int)($_POST["annee_id"] ?? 0);
    $date_inscription = $_POST["date_inscription"] ?? '';

    if (empty($matricule) || empty($nom) || empty($prenom) || empty($date_naissance) 
        || empty($sexe) || empty($date_inscription) || $classe_id === 0 || $annee_id === 0) {
        echo "Champs manquants";
        exit;
    }

    // Vérif année
    $check = $pdo->prepare("SELECT id FROM annee_scolaire WHERE id = :id AND actif = 1");
    $check->execute([':id' => $annee_id]);
    if (!$check->fetch()) {
        echo "Année invalide";
        exit;
    }

    // Vérif doublon matricule
    $check = $pdo->prepare("SELECT id FROM eleves WHERE matricule = :matricule");
    $check->execute([':matricule' => $matricule]);
    if ($check->fetch()) {
        echo "Matricule déjà utilisé";
        exit;
    }

    // Insertion
    $stmt = $pdo->prepare("
        INSERT INTO eleves (matricule, nom, prenom, date_naissance, sexe, classe_id, date_inscription)
        VALUES (:matricule, :nom, :prenom, :date_naissance, :sexe, :classe_id, :date_inscription)
    ");
    $stmt->execute([
        ':matricule'        => $matricule,
        ':nom'              => $nom,
        ':prenom'           => $prenom,
        ':date_naissance'   => $date_naissance,
        ':sexe'             => $sexe,
        ':classe_id'        => $classe_id,
        ':date_inscription' => $date_inscription
    ]);

    echo "Élève ajouté avec succès";
}
?>
        <div class="div3">
            <div class="page">
                <h2>Inscription / Réinscription Élève</h2>

                <form id="matiereForm" method="POST" action="">
                    <label for="matricule">Matricule :</label>
                    <input type="text" id="matricule" name="matricule" required>

                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" required>

                    <label for="prenom">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" required>

                    <label for="date_naissance">Date de naissance :</label>
                    <input type="date" id="date_naissance" name="date_naissance" required>

                    <label>Sexe :</label>
                    <input type="radio" name="sexe" value="M" required> M
                    <input type="radio" name="sexe" value="F"> F

                    
                    

                    <label for="annee">Année scolaire :</label>
                    <select id="annee" name="annee_id" required>
                        <?php
                        $stmt = $pdo->query("SELECT id, libelle FROM annee_scolaire WHERE actif = 1 ORDER BY date_debut DESC");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value=\"{$row['id']}\">{$row['libelle']}</option>";
                        }
                        ?>
                    </select>

                    <label for="classe">Classe :</label>
                    <select id="classe" name="classe_id" required>
                        <option value="" disabled selected>Choisissez une classe</option>
                        <?php
                        // 
                        $stmt = $pdo->query("SELECT  `niveau` , `section` FROM `classes` WHERE 1");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $niveau = (int)$row['niveau'];
                            $suffixe = ($niveau === 1) ? 'er' : 'ème';
                            
                            echo "<option value=\"{$row['niveau']}\">{$row['niveau']}{$suffixe} {$row['section']}</option>";
                        }
                        ?>
                    </select>

                    <label for="date_inscription">Date d'inscription :</label>
                    <input type="date" id="date_inscription" name="date_inscription" required>

                    <button type="submit">Enregistrer</button>
                    <button type="button" onclick="annulerForm()">Annuler</button>
                </form>
            </div>

        </div>
    </div>
</body>
