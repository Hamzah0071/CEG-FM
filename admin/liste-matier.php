<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEG-Francoi de mahy</title>
    
    <link rel="stylesheet" href="../styles/liste/classe.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
</head>
<body>
    <div class="parent">
        <!-- inclusion de navigation -->
        <?php 
            require_once('../include/header.php'); 
            require_once "../include/db.php"; 
        ?>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code        = strtoupper(trim($_POST["code"] ?? ''));
    $nom         = trim($_POST["nom"] ?? '');
    $coefficient = (float)($_POST["coefficient"] ?? 0);

    if ($code === '' || $nom === '' || $coefficient <= 0) {
        echo "Champs manquants";
        exit;
    }

    // Vérifier doublon par CODE (clé unique)
    $check = $pdo->prepare("SELECT id FROM matieres WHERE CODE = :code");
    $check->execute([':code' => $code]);
    if ($check->fetch()) {
        echo "Ce code matière existe déjà";
        exit;
    }

    // Insertion
    $stmt = $pdo->prepare("
        INSERT INTO matieres (CODE, nom, coefficient)
        VALUES (:code, :nom, :coefficient)
    ");
    $stmt->execute([
        ':code'        => $code,
        ':nom'         => $nom,
        ':coefficient' => $coefficient
    ]);
}
?>
        <div class="div3">
            <div class="layout-container">
                <!-- Partie droite : Formulaire -->
                <div class="right-section">
                    <div class="content-section">
                        <h3>Ajouter </h3>
                        <form id="matiereForm" method="POST" action="">
                            <label for="code">Code :</label>
                            <input type="text" id="code" name="code" placeholder="S001 ou M755" required>

                            <label for="nom">Nom :</label>
                            <input type="text" id="nom" name="nom" placeholder="ex: Mathématiques" required>

                            <label for="coefficient">Coefficient :</label>
                            <input type="number" step="1" min="3" id="coefficient" name="coefficient" placeholder="ex: 3" required>

                            <button type="submit" class="add-new">Enregistrer</button>
                            <button type="button" class="add-new" onclick="annulerForm()">Annuler</button>
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
                                    <th>Code</th>
                                    <th>Nom</th>
                                    <th>Coefficient</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query(
                                    "SELECT `CODE`, `nom`, `coefficient` FROM `matieres` WHERE 1;
                                    FROM matieres
                                    JOIN coefficient 
                                    ON nom = id
                                    ORDER BY nom DESC"
                                );

                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "
                                        <tr>
                                            <td>{$row['CODE']}</td>
                                            <td>{$row['nom']}</td>
                                            <td>{$row['coefficient']}</td>
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