<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription Élève - CEG FM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
</head>
<body>
<div class="parent">

<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/auth_check.php';
require_role('admin');

$pageTitle = "Inscription d'un élève";
require_once __DIR__ . '/../../include/header.php';

/* ===============================
   RÉCUPÉRER ANNÉE SCOLAIRE ACTIVE
================================ */
$anneeStmt = $pdo->query("SELECT id FROM annee_scolaire WHERE actif = 1 LIMIT 1");
$annee = $anneeStmt->fetch(PDO::FETCH_ASSOC);

if (!$annee) {
    die( " Aucune année scolaire active");
}
$annee_id = $annee['id'];

/* ===============================
   RÉCUPÉRER LES CLASSES
================================ */
$classesStmt = $pdo->prepare("
    SELECT id, CONCAT(niveau, ' ', nom) AS libelle
    FROM classes
    WHERE annee_scolaire_id = ?
    ORDER BY niveau, nom
");
$classesStmt->execute([$annee_id]);
$classes = $classesStmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   TRAITEMENT FORMULAIRE
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // 1. PERSONNE
        $stmt = $pdo->prepare("
            INSERT INTO personnes (nom, prenom, date_naissance, sexe, telephone, adresse)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['nom'],
            $_POST['prenom'],
            $_POST['date_naissance'],
            $_POST['sexe'],
            $_POST['telephone'],
            $_POST['adresse']
        ]);
        $personne_id = $pdo->lastInsertId();

        // 2. UTILISATEUR (élève)
        $email = strtolower($_POST['prenom'] . '.' . $_POST['nom']) . '@eleve.ceg-fm.mg';
        $stmt = $pdo->prepare("
            INSERT INTO utilisateurs (personne_id, email, password_hash, role, statut)
            VALUES (?, ?, ?, 'eleve', 'actif')
        ");
        $stmt->execute([
            $personne_id,
            $email,
            password_hash('password', PASSWORD_DEFAULT)
        ]);
        $utilisateur_id = $pdo->lastInsertId();

        // 3. ÉLÈVE
        $stmt = $pdo->prepare("
            INSERT INTO eleves (utilisateur_id, personne_id, matricule, date_inscription,
                                nom_parent, telephone_parent, email_parent)
            VALUES (?, ?, ?, CURDATE(), ?, ?, ?)
        ");
        $stmt->execute([
            $utilisateur_id,
            $personne_id,
            $_POST['matricule'],
            $_POST['nom_parent'],
            $_POST['telephone_parent'],
            $_POST['email_parent']
        ]);
        $eleve_id = $pdo->lastInsertId();

        // 4. INSCRIPTION
        $stmt = $pdo->prepare("
            INSERT INTO inscriptions (eleve_id, classe_id, annee_scolaire_id, date_inscription)
            VALUES (?, ?, ?, CURDATE())
        ");
        $stmt->execute([
            $eleve_id,
            $_POST['classe_id'],
            $annee_id
        ]);

        $pdo->commit();
        header("Location: liste-eleves.php?success=1");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<div class="div3">
    <h1>
        <i class="fa-solid fa-plus"></i> 
        Inscription d’un élève
    </h1>

    <form method="POST" class="card p-4">

        <h3>Informations élève</h3>

        <input name="matricule" class="form-control mb-2" placeholder="Matricule" required>
        <input name="nom" class="form-control mb-2" placeholder="Nom" required>
        <input name="prenom" class="form-control mb-2" placeholder="Prénom" required>

        <label>Date de naissance</label>
        <input type="date" name="date_naissance" class="form-control mb-2" required>

        <select name="sexe" class="form-control mb-2" required>
            <option value="">Sexe</option>
            <option value="M">Masculin</option>
            <option value="F">Féminin</option>
        </select>

        <input name="telephone" class="form-control mb-2" placeholder="Téléphone">
        <textarea name="adresse" class="form-control mb-3" placeholder="Adresse"></textarea>

        <h3>Classe</h3>
        <select name="classe_id" class="form-control mb-3" required>
            <option value="">-- Choisir une classe --</option>
            <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['libelle']) ?></option>
            <?php endforeach; ?>
        </select>

        <h3>Parent / Tuteur</h3>
        <input name="nom_parent" class="form-control mb-2" placeholder="Nom du parent">
        <input name="telephone_parent" class="form-control mb-2" placeholder="Téléphone du parent">
        <input name="email_parent" class="form-control mb-3" placeholder="Email du parent">

        <button class="btn btn-primary">
            <i class="fa-solid fa-check"></i>
             Inscrire l’élève
        </button>

    </form>
</div>

</div>
</body>
</html>
