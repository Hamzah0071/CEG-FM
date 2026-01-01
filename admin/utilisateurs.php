<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEG François de Mahy</title>
    <link rel="stylesheet" href="<?= $basePath ?>styles/style.css">
    <link rel="stylesheet" href="../styles/liste/classe.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
</head>
<body>
    <div class="parent">
        <?php 
            require_once('../include/header.php'); 
            require_once "../include/db.php"; 
        ?>

        <?php
require_once '../include/auth.php';
requireRole(['admin']);

require_once '../include/db.php'; // PDO = $pdo

$stmt = $pdo->query("
    SELECT id, username, role, created_at
    FROM utilisateurs
    ORDER BY created_at DESC
");
$utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../include/header.php'; ?>

<div class="div3">
    <h2>Gestion des comptes utilisateurs</h2>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom d'utilisateur</th>
                <th>Rôle</th>
                <th>Date création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>

        <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['role']) ?></td>
                <td><?= $u['created_at'] ?></td>
                <td>
                    <a href="edit-user.php?id=<?= $u['id'] ?>">Modifier</a> |
                    <a href="delete-user.php?id=<?= $u['id'] ?>"
                       onclick="return confirm('Supprimer cet utilisateur ?')">
                        Supprimer
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
</div>

</body>
</html>