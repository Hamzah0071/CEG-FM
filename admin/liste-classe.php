<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEG-Francoi de mahy</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/liste/classe.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
</head>
<body>
    <div class="parent">
        <!-- inclusion de navigation -->
        <?php require_once('../include/header.php'); ?>

        <div class="div3">
        <div class="content-section">
            <div class="table">
                <div class="table-head">
                    <h3>gere les professeurs</h3>
                    <div class="recherche">
                        <input placeholder="Chercher un professeur">
                        <button class="add-new" onclick="toggleAddForm()">Ajouter</button>
                    </div>
                </div>

                <div class="add-form" id="addForm">
                    <input type="text" id="nameInput" placeholder="Nom">
                    <input type="text" id="initialInput" placeholder="Inigtial">
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
                        <tr>
                            <td>01</td>
                            <td>6ème</td>
                            <td> A</td>

                            <td>
                                <img src="../images/icone/icons8-crayon-50.png" alt="Modifier" class="edit-icon">
                                <img src="../images/icone/icons8-gomme-50.png" alt="Supprimer" class="delete-icon">
                            </td>
                        </tr>
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
    <!-- <script src="../scripts/liste/AJ-classe.js" type="module"></script> -->
</body>
</html>