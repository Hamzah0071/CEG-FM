<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEG François de Mahy</title>
    <link rel="stylesheet" href="../styles/liste/classe.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
</head>
<body>

<div class="parent">
    <?php 
        require_once('../include/header.php'); //chemin vers le header
        require_once "../include/db.php"; // chemin vers la conection a la
    ?>

<?php
/* =========================
   TRAITEMENT DU FORMULAIRE
   ========================= */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $niveau   = trim($_POST["niveau"] ?? '');
    $section      = trim($_POST["section"] ?? '');
    $annee_id = (int)($_POST["annee_id"] ?? 0);

    if ( $niveau === '' || $section === '' || $annee_id === 0) {
        echo "Champs manquants";
        exit;
    }

    // Vérifier année scolaire
    $check_annee = $pdo->prepare(
        "SELECT id FROM annee_scolaire WHERE id = :id"
    );
    $check_annee->execute([':id' => $annee_id]);

    if (!$check_annee->fetch()) {
        echo "Année scolaire invalide";
        exit;
    }

    // Vérifier doublon
    $check = $pdo->prepare(
        "SELECT id FROM classes 
         WHERE niveau = :niveau 
         AND section = :section 
         AND annee_scolaire_id = :annee_id"
    );
    $check->execute([
        ':niveau'   => $niveau,
        ':section'      => $section,
        ':annee_id' => $annee_id
    ]);

    if ($check->fetch()) {
        echo "Cette classe existe déjà pour cette année";
        exit;
    }

    // Insertion
    $stmt = $pdo->prepare(
        "INSERT INTO classes ( niveau, section, annee_scolaire_id)
         VALUES ( :niveau, :section, :annee_id)"
    );
    $stmt->execute([
        ':niveau'   => $niveau,
        ':section'      => $section,
        ':annee_id' => $annee_id
    ]);
}
?>

<div class="div3">
    <div class="layout-container">

        <!-- FORMULAIRE -->
        <div class="right-section">
            <div class="content-section">
                <h3>Ajouter une classe</h3>

                <form method="POST">
                    <label>niveau :</label>
                    <input type="text" name="niveau" placeholder="ex:6, 5, 4, 3…" required>

                    <label>section :</label>
                    <input type="text" name="section" placeholder="ex :A, B, C…" required>

                    <label>Année scolaire :</label>
                    <select name="annee_id" required>
                        <?php
                        $stmt = $pdo->query(
                            "SELECT id, libelle 
                             FROM annee_scolaire 
                             WHERE actif = 1 
                             ORDER BY date_debut DESC"
                        );
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$row['id']}'>{$row['libelle']}</option>";
                        }
                        ?>
                    </select>

                    <button type="submit" class="add-new">Enregistrer</button>
                </form>
            </div>
        </div>

        <!-- LISTE DES CLASSES -->
        <div class="left-section">
            <div class="content-section">
                <h3>Classes existantes</h3>

                <table class="classe-table">
                    <thead>
                        <tr>
                            <th>niveau</th>
                            <th>section</th>
                            <th>Année scolaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query(
                            "SELECT c.niveau, c.section, a.libelle
                             FROM classes c
                             JOIN annee_scolaire a 
                               ON c.annee_scolaire_id = a.id
                             ORDER BY a.date_debut DESC"
                        );

                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $niveau = (int)$row['niveau'];
                            $suffixe = ($niveau === 1) ? 'er' : 'ème';

                            echo "
                                <tr>
                                    <td>{$niveau}{$suffixe}</td>
                                    <td>{$row['section']}</td>
                                    <td>{$row['libelle']}</td>
                                </tr>
                            ";
                        }

                        ?>
                    </tbody>
                </table>

            </div>
        </div>

    </div>

    <div class="footer">
        <p>&copy; 2024 CEG François de Mahy. Tous droits réservés.</p>
    </div>
</div>

</div>
</body>
</html>
