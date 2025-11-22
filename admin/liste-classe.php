<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEG François de Mahy</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/liste/classe.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <style>
        .add-form {
            display: none;
            margin: 15px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .add-form input {
            margin: 0 10px;
            padding: 8px;
        }
    </style>
</head>
<body>
    <div class="parent">
        <?php
        require_once('../include/header.php'); // chemin relatif selon le dossier
        ?>
        <div class="div3">
            <div class="content-section">
                <div class="table">
                    <div class="table-head">
                        <h3>Gérer les classes</h3>
                        
                        <div class="recherche">
                            <input placeholder="Chercher une classe" id="searchInput">
                            <button class="add-new" onclick="toggleAddForm()">Ajouter</button>
                        </div>
                    </div>

                    <!-- Formulaire d'ajout -->
                    <div class="add-form" id="addForm">
                        <input type="text" id="nameInput" placeholder="Nom de la classe">
                        <input type="text" id="initialInput" placeholder="Initiale">
                        <button class="add-new" id="confirmAddBtn">Confirmer</button>
                    </div>

                    <table class="table-section">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Nom de la classe</th>
                                <th>Initiale</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            <?php
                            $file = __DIR__ . '/../data/classes.csv';
                            if (file_exists($file)) {
                                $handle = fopen($file, 'r');
                                // Ignorer l'en-tête si elle existe
                                $hasHeader = false;
                                $firstRow = fgetcsv($handle);
                                if ($firstRow !== false && count($firstRow) >= 2) {
                                    // Vérifier si c'est un en-tête
                                    if (!is_numeric(trim($firstRow[0]))) {
                                        $hasHeader = true;
                                    } else {
                                        // Remettre le curseur au début
                                        fclose($handle);
                                        $handle = fopen($file, 'r');
                                    }
                                }
                                
                                $i = 1;
                                while (($data = fgetcsv($handle)) !== false) {
                                    if (count($data) < 2 || trim($data[0]) === '' || trim($data[1]) === '') {
                                        continue;
                                    }

                                    echo "<tr data-id='" . htmlspecialchars(trim($data[0])) . "'>
                                        <td>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</td>
                                        <td class='editable'>" . htmlspecialchars(trim($data[0])) . "</td>
                                        <td class='editable'>" . htmlspecialchars(trim($data[1])) . "</td>
                                        <td>
                                            <img src=\"../images/icone/icons8-crayon-50.png\" alt=\"Modifier\" class=\"edit-icon\" style=\"cursor:pointer; width:20px; margin-right:10px;\">
                                            <img src=\"../images/icone/icons8-gomme-50.png\" alt=\"Supprimer\" class=\"delete-icon\" style=\"cursor:pointer; width:20px;\">
                                        </td>
                                    </tr>";
                                    $i++;
                                }
                                fclose($handle);
                                
                                if ($i === 1) {
                                    echo "<tr><td colspan='4'>Aucune classe trouvée</td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>Le fichier de données n'existe pas</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="footer">
                <p>&copy; 2024 CEG François de Mahy. Tous droits réservés.</p>
            </div>
        </div>
    </div>

    <script src="../scripts/java.js"></script>
    <script>
        // Fonction globale pour le formulaire d'ajout
        function toggleAddForm() {
            const form = document.getElementById('addForm');
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }
    </script>
    <script src="../scripts/liste/AJ-classe.js"></script>
</body>
</html>