<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../include/auth_check.php';
require_role('admin');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID élève invalide');
}

$eleve_id = (int) $_GET['id'];

$sql = "
SELECT 
    e.id AS eleve_id,
    e.matricule,
    e.nom_parent,
    e.telephone_parent,
    e.email_parent,
    e.date_inscription,

    p.nom,
    p.prenom,
    p.sexe,
    p.date_naissance,
    p.telephone,
    p.adresse,

    u.email,
    u.statut AS statut_compte,

    i.statut AS statut_inscription,
    CONCAT(c.niveau, ' ', c.nom) AS classe,
    a.libelle AS annee_scolaire
FROM eleves e
JOIN personnes p ON e.personne_id = p.id
JOIN utilisateurs u ON e.utilisateur_id = u.id
LEFT JOIN inscriptions i 
    ON e.id = i.eleve_id
   AND i.deleted_at IS NULL
   AND i.annee_scolaire_id = (
        SELECT id FROM annee_scolaire WHERE actif = 1 LIMIT 1
   )
LEFT JOIN classes c ON i.classe_id = c.id
LEFT JOIN annee_scolaire a ON i.annee_scolaire_id = a.id
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
<h1>Détails de l’élève</h1>

<table class="table table-bordered">
<tr><th>Matricule</th><td><?= htmlspecialchars($eleve['matricule']) ?></td></tr>
<tr><th>Nom</th><td><?= htmlspecialchars($eleve['nom']) ?></td></tr>
<tr><th>Prénom</th><td><?= htmlspecialchars($eleve['prenom']) ?></td></tr>
<tr><th>Sexe</th><td><?= $eleve['sexe'] ?></td></tr>
<tr><th>Date naissance</th><td><?= date('d/m/Y', strtotime($eleve['date_naissance'])) ?></td></tr>
<tr><th>Classe</th><td><?= $eleve['classe'] ?? 'Non assignée' ?></td></tr>
<tr><th>Année scolaire</th><td><?= $eleve['annee_scolaire'] ?></td></tr>
<tr><th>Statut inscription</th><td><?= $eleve['statut_inscription'] ?></td></tr>
<tr><th>Statut compte</th><td><?= $eleve['statut_compte'] ?></td></tr>
<tr><th>Parent</th><td><?= $eleve['nom_parent'] ?></td></tr>
<tr><th>Téléphone parent</th><td><?= $eleve['telephone_parent'] ?></td></tr>
<tr><th>Email parent</th><td><?= $eleve['email_parent'] ?></td></tr>
</table>

<a href="modifier-eleve.php?id=<?= $eleve_id ?>" class="btn btn-warning">
    Modifier l’élève
</a>
</div>
