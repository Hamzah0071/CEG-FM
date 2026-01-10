<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/auth_check.php';
require_role('admin');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID invalide');
}

$eleve_id = (int) $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->beginTransaction();

    try {
        $pdo->prepare("
            UPDATE personnes SET
                nom = ?, prenom = ?, sexe = ?, date_naissance = ?, telephone = ?, adresse = ?
            WHERE id = (SELECT personne_id FROM eleves WHERE id = ?)
        ")->execute([
            $_POST['nom'], $_POST['prenom'], $_POST['sexe'],
            $_POST['date_naissance'], $_POST['telephone'], $_POST['adresse'],
            $eleve_id
        ]);

        $pdo->prepare("
            UPDATE eleves SET
                nom_parent = ?, telephone_parent = ?, email_parent = ?
            WHERE id = ?
        ")->execute([
            $_POST['nom_parent'], $_POST['telephone_parent'], $_POST['email_parent'],
            $eleve_id
        ]);

        $pdo->commit();
        header("Location: detail-eleve.php?id=$eleve_id");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erreur : " . $e->getMessage());
    }
}

$sql = "
SELECT 
    e.id,
    e.nom_parent,
    e.telephone_parent,
    e.email_parent,
    p.nom,
    p.prenom,
    p.sexe,
    p.date_naissance,
    p.telephone,
    p.adresse
FROM eleves e
JOIN personnes p ON e.personne_id = p.id
WHERE e.id = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$eleve_id]);
$eleve = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$eleve) {
    die('Élève introuvable');
}


?>
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
<?php require_once __DIR__ . '/../../include/header.php'; ?>
<div class="div3">
<h1>Modifier l’élève</h1>

<form method="post">
<label>Nom</label>
<input type="text" name="nom" value="<?= htmlspecialchars($eleve['nom']) ?>" required>

<label>Prénom</label>
<input type="text" name="prenom" value="<?= htmlspecialchars($eleve['prenom']) ?>" required>

<label>Sexe</label>
<select name="sexe">
    <option value="M" <?= $eleve['sexe']=='M'?'selected':'' ?>>Masculin</option>
    <option value="F" <?= $eleve['sexe']=='F'?'selected':'' ?>>Féminin</option>
</select>

<label>Date naissance</label>
<input type="date" name="date_naissance" value="<?= $eleve['date_naissance'] ?>">
<br>
<label>Téléphone</label>
<input type="text" name="telephone" value="<?= htmlspecialchars($eleve['telephone']) ?>">

<label>Adresse</label>
<input type="text" name="adresse" value="<?= htmlspecialchars($eleve['adresse']) ?>">



<label>Nom du parent</label>
<input type="text" name="nom_parent" value="<?= htmlspecialchars($eleve['nom_parent']) ?>">

<label>Téléphone parent</label>
<input type="text" name="telephone_parent" value="<?= htmlspecialchars($eleve['telephone_parent']) ?>">

<label>Email parent</label>
<input type="email" name="email_parent" value="<?= htmlspecialchars($eleve['email_parent']) ?>">

<br><br>
<button class="btn btn-success">Enregistrer</button>
<a href="detail-eleve.php?id=<?= $eleve_id ?>" class="btn btn-secondary">Annuler</a>
</form>
</div>
