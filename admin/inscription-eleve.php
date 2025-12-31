<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/admin/inscription.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <title>Inscription Élève</title>
</head>
<body>
    <div class="parent">
        <?php 
            require_once('../include/header.php'); 
            require_once "../include/db.php"; 
        ?>

<?php
$message = '';

// === TRAITEMENT DU FORMULAIRE ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $matricule         = trim($_POST['matricule'] ?? '');
    $nom               = trim($_POST['nom'] ?? '');
    $prenom            = trim($_POST['prenom'] ?? '');
    $date_naissance    = $_POST['date_naissance'] ?? '';
    $sexe              = $_POST['sexe'] ?? '';
    $classe_id         = !empty($_POST['classe_id']) ? (int)$_POST['classe_id'] : 0;
    $date_inscription  = $_POST['date_inscription'] ?? '';

    // Validation des champs obligatoires
    if (!$matricule || !$nom || !$prenom || !$date_naissance || !$sexe || !$classe_id || !$date_inscription) {
        $message = "<div class='message erreur'>Tous les champs sont obligatoires.</div>";
    } 
    elseif (!in_array($sexe, ['M', 'F'])) {
        $message = "<div class='message erreur'>Sexe invalide.</div>";
    }
    else {
        // Vérification doublon matricule
        $check = $pdo->prepare("SELECT id FROM eleves WHERE matricule = ?");
        $check->execute([$matricule]);
        if ($check->fetch()) {
            $message = "<div class='message erreur'>Ce matricule est déjà utilisé.</div>";
        } else {
            try {
                // 1. Création de l'utilisateur (compte élève)
                $username = strtolower($prenom . '.' . $nom);
                $password_hash = password_hash("", PASSWORD_DEFAULT); // mot de passe par défaut

                $stmt = $pdo->prepare("INSERT INTO utilisateurs (username, password_hash, role) VALUES (?, ?, 'eleve')");
                $stmt->execute([$username, $password_hash]);
                $utilisateur_id = $pdo->lastInsertId();

                // 2. Création de l'élève
                $stmt = $pdo->prepare("
                    INSERT INTO eleves 
                    (utilisateur_id, matricule, nom, prenom, date_naissance, sexe, classe_id, date_inscription)
                    VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $utilisateur_id,
                    $matricule,
                    $nom,
                    $prenom,
                    $date_naissance,
                    $sexe,
                    $classe_id,
                    $date_inscription
                ]);

                $message = "<div class='message succes'>Élève inscrit avec succès !</div>";

            } catch (PDOException $e) {
                $message = "<div class='message erreur'>Erreur lors de l'inscription : " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
}
?>

        <div class="div3">
            <div class="page">
                <h2>Inscription / Réinscription Élève</h2>

                <?= $message ?>

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
                    <div >
                        <label><input type="radio" name="sexe" value="M" required> Masculin</label>
                        <label><input type="radio" name="sexe" value="F"> Féminin</label>
                    </div>

                    <label>Classe</label>
                    <select name="classe_id" required>
                        <option value="">-- Choisir une classe --</option>
                        <?php
                        $stmt = $pdo->query("
                            SELECT c.id, c.niveau, c.section
                            FROM classes c
                            JOIN annee_scolaire a ON a.id = c.annee_scolaire_id
                            WHERE a.actif = 1
                            ORDER BY c.niveau DESC, c.section
                        ");

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $niveau = (int)$row['niveau'];
                            $suffixe = ($niveau === 1) ? 'ère' : 'ème'; // ou 'er' si tu préfères "1ère"
                            $affichage = $niveau . $suffixe . " " . $row['section'];
                            echo "<option value='{$row['id']}'>" . htmlspecialchars($affichage) . "</option>";
                        }
                        ?>
                    </select>

                    <label>Date d'inscription</label>
                    <input type="date" name="date_inscription" value="<?= date('Y-m-d') ?>" required>

                    <button type="submit">Enregistrer l'élève</button>
                    <a href="../admin/liste-eleve.php" style="margin-left: 15px; text-decoration: none; color: #555;">← Retour à la liste</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>