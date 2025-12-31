<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/admin/inscription.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <title>Affectation Professeur</title>
    <style>
        .message { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .succes { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .erreur { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="parent">
        <?php 
            require_once('../include/header.php'); 
            require_once "../include/db.php"; 
        ?>

<?php
$message = '';

// Traitement du formulaire si soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id                 = !empty($_POST['id']) ? (int)$_POST['id'] : null;
    $prof_id            = !empty($_POST['prof_id']) ? (int)$_POST['prof_id'] : 0;
    $classe_id          = !empty($_POST['classe_id']) ? (int)$_POST['classe_id'] : 0;
    $matiere_id         = !empty($_POST['matiere_id']) ? (int)$_POST['matiere_id'] : 0;
    $annee_scolaire_id  = !empty($_POST['annee_scolaire_id']) ? (int)$_POST['annee_scolaire_id'] : 0;

    // Validation
    if (!$prof_id || !$classe_id || !$matiere_id || !$annee_scolaire_id) {
        $message = "<div class='message erreur'>Tous les champs sont obligatoires.</div>";
    } else {
        // Vérifier doublon (sauf en modification du même enregistrement)
        $sql_check = "SELECT id FROM affectations_prof 
                      WHERE prof_id = ? 
                        AND classe_id = ? 
                        AND matiere_id = ? 
                        AND annee_scolaire_id = ?";
        $params_check = [$prof_id, $classe_id, $matiere_id, $annee_scolaire_id];

        if ($id) {
            $sql_check .= " AND id != ?";
            $params_check[] = $id;
        }

        $check = $pdo->prepare($sql_check);
        $check->execute($params_check);

        if ($check->fetch()) {
            $message = "<div class='message erreur'>Cette affectation existe déjà.</div>";
        } else {
            try {
                if ($id) {
                    // Modification
                    $sql = "UPDATE affectations_prof SET 
                                prof_id = ?, classe_id = ?, matiere_id = ?, annee_scolaire_id = ?
                            WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$prof_id, $classe_id, $matiere_id, $annee_scolaire_id, $id]);
                    $message = "<div class='message succes'>Affectation modifiée avec succès !</div>";
                } else {
                    // Insertion
                    $sql = "INSERT INTO affectations_prof 
                                (prof_id, classe_id, matiere_id, annee_scolaire_id) 
                            VALUES 
                                (?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$prof_id, $classe_id, $matiere_id, $annee_scolaire_id]);
                    $message = "<div class='message succes'>Affectation enregistrée avec succès !</div>";
                }
            } catch (PDOException $e) {
                $message = "<div class='message erreur'>Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
}

// Si modification, récupérer les données actuelles
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$prof_id_actuel = $classe_id_actuel = $matiere_id_actuel = $annee_id_actuel = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM affectations_prof WHERE id = ?");
    $stmt->execute([$id]);
    $affect = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($affect) {
        $prof_id_actuel     = $affect['prof_id'];
        $classe_id_actuel   = $affect['classe_id'];
        $matiere_id_actuel  = $affect['matiere_id'];
        $annee_id_actuel    = $affect['annee_scolaire_id'];
    } else {
        $message = "<div class='message erreur'>Affectation non trouvée.</div>";
    }
}
?>

        <div class="div3">
            <div class="page">
                <h2><?= $id ? 'Modifier' : 'Nouvelle' ?> Affectation Professeur</h2>

                <?= $message ?>

                <form method="POST">
                    <?php if ($id): ?>
                        <input type="hidden" name="id" value="<?= $id ?>">
                    <?php endif; ?>

                    <label>Professeur</label>
                    <select name="prof_id" required>
                        <option value="">-- Choisir un professeur --</option>
                        <?php
                        $req = $pdo->query("SELECT id, nom, prenom FROM professeurs ORDER BY nom, prenom");
                        while ($prof = $req->fetch(PDO::FETCH_ASSOC)) {
                            $selected = ($prof['id'] == $prof_id_actuel) ? 'selected' : '';
                            echo "<option value='{$prof['id']}' $selected>" . htmlspecialchars($prof['nom'] . " " . $prof['prenom']) . "</option>";
                        }
                        ?>
                    </select>

                    <label>Classe</label>
                    <select name="classe_id" required>
                        <option value="">-- Choisir une classe --</option>
                        <?php
                        $stmt = $pdo->query("
            SELECT c.id, c.niveau, c.section
            FROM classes c
            JOIN annee_scolaire a ON a.id = c.annee_scolaire_id
            WHERE a.actif = 1
            ORDER BY c.niveau, c.section
        ");

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $niveau = (int)$row['niveau'];
            $suffixe = ($niveau === 1) ? 'er' : 'ème';

            echo "<option value='{$row['id']}'>
                    {$row['niveau']}{$suffixe} {$row['section']}
                  </option>";
        }
                        ?>
                    </select>

                    <label>Matière</label>
                    <select name="matiere_id" required>
                        <option value="">-- Choisir une matière --</option>
                        <?php
                        $req = $pdo->query("SELECT id, nom FROM matieres ORDER BY nom");
                        while ($matiere = $req->fetch(PDO::FETCH_ASSOC)) {
                            $selected = ($matiere['id'] == $matiere_id_actuel) ? 'selected' : '';
                            echo "<option value='{$matiere['id']}' $selected>" . htmlspecialchars($matiere['nom']) . "</option>";
                        }
                        ?>
                    </select>

                    <label>Année scolaire</label>
                    <select name="annee_scolaire_id" required>
                        <option value="">-- Choisir l'année --</option>
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

                    <button type="submit">Enregistrer</button>
                    <a href="liste_affectations.php" style="margin-left: 15px; text-decoration: none; color: #555;">← Retour à la liste</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>