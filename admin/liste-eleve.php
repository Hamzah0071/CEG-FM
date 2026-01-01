<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEG François de Mahy - Gestion Professeurs</title>
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
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT 
        e.matricule,
        e.nom,
        e.prenom,
        e.date_naissance,
        e.sexe,
        e.date_inscription,
        c.niveau,
        c.section
    FROM eleves e
    JOIN classes c ON c.id = e.classe_id
";

$params = [];

if ($search !== '') {
    $sql .= " WHERE e.nom LIKE ? OR e.prenom LIKE ? OR e.matricule LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$sql .= " ORDER BY c.niveau, e.nom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
?>

<div class="div3">

    <h2>Liste des élèves</h2>

    <!-- Recherche -->
    <form method="GET">
        <input type="text" name="search" placeholder="Nom ou matricule"
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Rechercher</button>
    </form>

    <br>

    <!-- Tableau -->
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Classe</th>
                <th>Sexe</th>
                <th>Date d'inscription</th>
            </tr>
        </thead>
        <tbody>

        <?php if ($stmt->rowCount() === 0): ?>
            <tr>
                <td colspan="6">Aucun élève trouvé</td>
            </tr>
        <?php else: ?>
            <?php while ($e = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <?php
                    $suffixe = ((int)$e['niveau'] === 1) ? 'er' : 'ème';
                ?>
                <tr>
                    <td><?= htmlspecialchars($e['matricule']) ?></td>
                    <td><?= htmlspecialchars($e['nom']) ?></td>
                    <td><?= htmlspecialchars($e['prenom']) ?></td>
                    <td><?= $e['niveau'] . $suffixe . ' ' . $e['section'] ?></td>
                    <td><?= $e['sexe'] ?></td>
                    <td><?= date('d/m/Y', strtotime($e['date_inscription'])) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>

        </tbody>
    </table>

    <div class="footer">
        <p>&copy; 2024 CEG François de Mahy. Tous droits réservés.</p>
    </div>

</div>

    </div>

    
</body>
</html>