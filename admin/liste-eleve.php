




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEG François de Mahy - Gestion Professeurs</title>
    <link rel="stylesheet" href="<?= $basePath ?>styles/style.css">
    <link rel="stylesheet" href="../styles/liste/classe.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
</head>
<body>
    <div class="parent">
        <!-- Inclusion du header -->
        <?php require_once('../include/header.php'); ?>

        <div class="div3">
            <?php require_once('../include/liste-des eleve-par-classe.php'); ?>

            <?php
            // Récupérer la recherche
            $selected_nom = trim($_POST['eleve'] ?? '');
            ?>

            <div class="content-section">
                <div class="table">
                    <div class="table-head">
                        <h3>Gérer les eleve</h3>
                        <div class="recherche">
                            <!-- Formulaire de recherche -->
                                <div class="form-group">
                                    <form method="POST">
                                        <label for="nom">Eleve :</label>
                                        <input type="text" id="eleve" name="eleve" 
                                            value="<?php echo htmlspecialchars($selected_nom); ?>" 
                                            placeholder="Ex: Recherche eleve...">
                                    </form>
                                </div>
                            <button class="add-new" onclick="toggleAddForm()">Ajouter</button>
                        </div>
                    </div>

                    <!-- Formulaire d'ajout (caché par défaut) -->
                    <div class="add-form" id="addForm" style="display: none;">
                        <input type="text" id="nameInput" placeholder="Nom et prénom">
                        <input type="text" id="classInput" placeholder="Classe (ex: 3ème A)">
                        <input type="date" id="dateInput" placeholder="Date de naissance">
                        <button class="add-new" id="confirmAddBtn">Confirmer</button>
                    </div>

                    <!-- Filtrer les etudiant -->
                        <?php
                        $filtered_etudiants = [];
                        foreach($etudiants as $eleve) {
                            // Vérifier si les etudiant est activée
                            if (array_key_exists('is_enabled', $eleve) && $eleve['is_enabled'] == true) {
                                // Si aucun eleve n'est sélectionné OU si l'eleve correspond
                                if (empty($selected_nom) || 
                                    (isset($eleve['nom']) && trim(strtolower($eleve['nom']))  == trim(strtolower($selected_nom))) ) {
                                    $filtered_etudiants[] = $eleve;
                                }
                            }
                        }
                        ?>

                    <form class="form-notes">
                    <table class="table-notes">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Élève</th>
                                <th>age</th>
                                <th>sexe</th>
                                <th>classe</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- Exemple -->
                            <tr>
                            <?php foreach($filtered_etudiants as $index => $eleve): ?>
                                <td><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></td>
                                <td><?php echo $eleve['nom']; ?></td>
                                <td><?php echo $eleve['age']; ?></td>
                                <td><?php echo $eleve['sexe']; ?></td>
                                <td><?php echo $eleve['classe']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
                </div>
            </div>

            <div class="footer">
                <p>© 2024 CEG François de Mahy. Tous droits réservés.</p>
            </div>
        </div>
    </div>

    
</body>
</html>