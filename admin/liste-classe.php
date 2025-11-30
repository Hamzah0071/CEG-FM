<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEG-Francoi de mahy</title>
    <link rel="stylesheet" href="<?= $basePath ?>styles/style.css">
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/liste/classe.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
</head>

<body>
    <div class="parent">
        <!-- inclusion de navigation -->
        <?php require_once('../include/header.php'); ?>

        <div class="div3">
            <?php require_once('../include/variable.php'); ?>

            <div class="content-section">
                <div class="table">

                    <div class="table-head">
                        <h3>Gere les classe</h3>
                        <div class="recherche">
                            <button class="add-new" onclick="toggleAddForm()">Ajouter</button>
                        </div>
                    </div>

                    <div class="add-form" id="addForm">
                        <input type="text" id="nameInput" placeholder="Nom">
                        <input type="text" id="initialInput" placeholder="Initial">
                        <button class="add-new" id="confirmAddBtn">Confirmer</button>
                    </div>

                    <table class="table-section">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Nom</th>
                                <th>Initiale</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody id="studentTableBody">
                            <?php foreach ($classes_existantes as $index => $classe): ?>
                                <tr>
                                    <td><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= htmlspecialchars($classe['nom']) ?></td>
                                    <td><?= htmlspecialchars($classe['initiale']) ?></td>
                                    <td>
                                        <img src="../images/icone/icons8-crayon-50.png" 
                                             alt="Modifier" 
                                             class="edit-icon" 
                                             onclick="editClass(<?= $index ?>)">
                                        
                                        <img src="../images/icone/icons8-gomme-50.png" 
                                             alt="Supprimer" 
                                             class="delete-icon" 
                                             onclick="deleteClass(<?= $index ?>)">
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($classes_existantes)): ?>
                                <tr>
                                    <td colspan="4" style="text-align:center;">Aucune classe enregistrée</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>
            </div>

            <div class="footer">
                <p>&copy; 2024 CEG François de Mahy. Tous droits réservés.</p>
            </div>
        </div>
        
    </div>

   
</body>
</html>
