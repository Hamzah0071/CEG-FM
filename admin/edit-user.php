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
            // include '../include/header.php';
            // require_once "../include/db.php"; 
        ?>
<?php
require_once '../include/auth.php';
requireRole(['admin']);
require_once '../include/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ID utilisateur invalide');
}

/* =========================
   RÉCUPÉRATION UTILISATEUR
========================= */
$stmt = $pdo->prepare("
    SELECT id, username, role
    FROM utilisateurs
    WHERE id = ?
");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Utilisateur introuvable');
}

/* =========================
   TRAITEMENT FORMULAIRE
========================= */
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $role = $_POST['role'] ?? '';

    if ($username === '' || !in_array($role, ['admin', 'prof', 'eleve'])) {
        $erreur = 'Champs invalides';
    } else {
        $stmt = $pdo->prepare("
            UPDATE utilisateurs
            SET username = ?, role = ?
            WHERE id = ?
        ");
        $stmt->execute([$username, $role, $id]);

        // ✅ REDIRECTION AVANT TOUT HTML
        header('Location: utilisateurs.php');
        exit;
    }
}
?>

<?php include '../include/header.php'; ?>

<div class="div3">
    <h2>Modifier utilisateur</h2>

    <?php if ($erreur): ?>
        <p style="color:red"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nom d'utilisateur</label><br>
        <input type="text" name="username"
               value="<?= htmlspecialchars($user['username']) ?>" required><br><br>

        <label>Rôle</label><br>
        <select name="role" required>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="prof" <?= $user['role'] === 'prof' ? 'selected' : '' ?>>Professeur</option>
            <option value="eleve" <?= $user['role'] === 'eleve' ? 'selected' : '' ?>>Élève</option>
        </select><br><br>

        <button type="submit">💾 Enregistrer</button>
        <a href="utilisateurs.php">❌ Annuler</a>
    </form>
</div>

</body>
</html>
