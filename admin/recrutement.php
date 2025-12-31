<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/admin/inscription.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <link rel="stylesheet" href="<?= $basePath ?>styles/style.css">
    <title>Recrutement</title>
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

    $matricule = trim($_POST['matricule'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $telephone = (int)($_POST['telephone'] ?? 0);
    $date_recrutement = $_POST['date_recrutement'] ?? '';

    if (
        !$matricule || !$nom || !$prenom ||
        !$date_naissance || !$sexe ||
        !$telephone || !$date_recrutement
    ) {
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
            }   else {
                    try {
                        // =============================
                        // 1️⃣ CRÉATION UTILISATEUR
                        // =============================
                        $username = strtolower($prenom . '.' . $nom);
                        $password = password_hash("", PASSWORD_DEFAULT);

                        $stmt = $pdo->prepare("
                            INSERT INTO utilisateurs (username, password_hash, role)
                            VALUES (?, ?, 'professeurs')
                        ");
                        $stmt->execute([$username, $password]);

                        $utilisateur_id = $pdo->lastInsertId();

                        // =============================
                        // 2️⃣ CRÉATION PROF
                        // =============================
                        $stmt = $pdo->prepare("
                            INSERT INTO professeurs
                            (utilisateur_id, matricule, nom, prenom, date_naissance, sexe, telephone, date_recrutement)
                            VALUES
                            (:utilisateur_id, :matricule, :nom, :prenom, :date_naissance, :sexe, :telephone, :date_recrutement)
                        ");

                        $stmt->execute([
                            ':utilisateur_id'   => $utilisateur_id,
                            ':matricule'        => $matricule,
                            ':nom'              => $nom,
                            ':prenom'           => $prenom,
                            ':date_naissance'   => $date_naissance,
                            ':sexe'             => $sexe,
                            ':telephone'        => $telephone,
                            ':date_recrutement' => $date_recrutement
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
                <h2>Recrutement</h2>

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

                <label>Telephone</label>
                <input type="number" name="telephone" required>

                <label>Date de recrutement</label>
                <input type="date" name="date_recrutement" required>

                <button type="submit">Enregistrer</button>
                <a href="../admin/liste-eleve.php" style="margin-left: 15px; text-decoration: none; color: #555;">← Retour à la liste</a>
            </form>

            </div>

        </div>
    </div>
</body>
</html>