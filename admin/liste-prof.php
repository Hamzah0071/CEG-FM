<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEG François de Mahy - Gestion Professeurs</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/liste/classe.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
</head>
<body>
    <div class="parent">
        <!-- Inclusion du header -->
        <?php require_once('../include/header.php'); ?>

        <div class="div3">
            <?php require_once('../include/liste-des-prof.php'); ?>

            <?php
            // Récupérer la recherche
            $selected_nom = trim($_POST['professeurs'] ?? '');
            ?>

            <div class="content-section">
                <div class="table">
                    <div class="table-head">
                        <h3>Gérer les professeurs</h3>
                        <div class="recherche">
                            <!-- Formulaire de recherche -->
                            <form method="POST" class="form-group">
                                <input type="text" 
                                       name="professeurs" 
                                       value="<?= htmlspecialchars($selected_nom) ?>" 
                                       placeholder="Rechercher un professeur...">
                                <button type="submit">Filtrer</button>
                            </form>
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

                    <?php
                    // Filtrage des professeurs
                    $filtered_professeurs = [];
                    foreach ($professeurs as $professeur) {
                        // Vérifier que le professeur est activé
                        if (!empty($professeur['is_enabled'])) {
                            $nomComplet = $professeur['nom'] ?? '';

                            // Recherche dans le nom complet (insensible à la casse)
                            if (empty($selected_nom) || 
                                stripos($nomComplet, $selected_nom) !== false) {
                                $filtered_professeurs[] = $professeur;
                            }
                        }
                    }
                    ?>

                    <table class="table-section">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Nom et prénom</th>
                                <th>Classes</th>
                                <th>Date de naissance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentTableBody">
                            <?php if (empty($filtered_professeurs)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #999;">
                                        Aucun professeur trouvé.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($filtered_professeurs as $index => $professeur): ?>
                                    <tr>
                                        <td><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></td>
                                        <td><?= htmlspecialchars($professeur['nom'] ?? 'Inconnu') ?></td>
                                        <td>
                                            <?= htmlspecialchars(implode(' • ', $professeur['classes'] ?? [])) ?>
                                        </td>
                                        <td><?= htmlspecialchars($professeur['date_naissance'] ?? 'Non renseignée') ?></td>
                                        <td class="actions">
                                            <img src="../images/icone/icons8-crayon-50.png" alt="Modifier" class="edit-icon" title="Modifier">
                                            <img src="../images/icone/icons8-gomme-50.png" alt="Supprimer" class="delete-icon" title="Supprimer">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="footer">
                <p>© 2024 CEG François de Mahy. Tous droits réservés.</p>
            </div>
        </div>
    </div>

    <script src="../scripts/java.js"></script>
</body>
</html>