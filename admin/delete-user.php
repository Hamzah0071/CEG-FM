<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= $basePath ?>styles/style.css">
    <link rel="stylesheet" href="../styles/admin/bulletin.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <title>Transfert élève</title>
</head>
<body>
   <div class="parent">
        <?php
require_once '../include/auth.php';
requireRole(['admin']);
require_once '../include/db.php';

/* =========================
   VALIDATION ID
========================= */
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('ID utilisateur invalide');
}

/* =========================
   EMPÊCHER AUTO-SUPPRESSION
========================= */
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
    die('Vous ne pouvez pas supprimer votre propre compte');
}

/* =========================
   VÉRIFIER EXISTENCE
========================= */
$stmt = $pdo->prepare("
    SELECT id
    FROM utilisateurs
    WHERE id = ?
");
$stmt->execute([$id]);

if (!$stmt->fetch()) {
    die('Utilisateur introuvable');
}

/* =========================
   SUPPRESSION
========================= */
$stmt = $pdo->prepare("
    DELETE FROM utilisateurs
    WHERE id = ?
");
$stmt->execute([$id]);

/* =========================
   REDIRECTION
========================= */
header('Location: utilisateurs.php');
exit;
        ?>