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
            <!-- importation des variable des eleve -->
            <?php require_once('../include/liste-des eleve-par-classe.php'); ?>

            <!-- Formulaire de recherche -->
            <div class="form-group">
                <form method="POST">
                    <label for="nom">Eleve :</label>
                    <input type="text" id="eleve" name="eleve" 
                        value="<?php echo htmlspecialchars($selected_nom); ?>" 
                        placeholder="Ex: Recherche eleve...">
                    <button type="submit">Filtrer</button>
                    <button type="button" onclick="window.location.href='?'">Voir tout</button>
                </form>
            </div>

           <!-- Filtrer les etudiant -->
            <?php
            $filtered_etudiants = [];
            foreach($etudiants as $eleve) {
                // Vérifier si la recette est activée
                if (array_key_exists('is_enabled', $eleve) && $eleve['is_enabled'] == true) {
                    // Si aucun eleve n'est sélectionné OU si l'eleve correspond
                    if (empty($selected_nom) || 
                        (isset($eleve['nom']) && trim(strtolower($eleve['nom']))  == trim(strtolower($selected_nom))) ) {
                        echo($selected_nom);
                        $filtered_etudiants[] = $eleve;
                    }
                }
            }
            
            ?>

            <!-- voire si il existe ou pas  -->
            <h2>
                <?php if (empty($selected_nom)): ?>
                        Toutes les information de l'eleve : (<?php echo count($filtered_etudiants); ?>)
                <?php else: ?>
                        Son nom: "<?php echo htmlspecialchars($selected_nom); ?>"
                <?php endif; ?>
            </h2>

            <!-- Affiche un etudiant différent selon le cas -->
            <?php if (empty($filtered_etudiants)): ?>
                <p class="no-etudiant">
                    <?php if (empty($selected_nom)): ?>
                        Aucune etudiant est disponible pour le moment.
                    <?php else: ?>
                        Aucune info trouvée pour l'Eleve "<?php echo htmlspecialchars($selected_nom); ?>".
                    <?php endif; ?>
                </p>

            <?php else: ?>


                <!-- boucle du tableaux -->
                <form class="form-notes">
                    <table class="table-notes">
                        <thead>
                            <tr>
                                <th>Élève</th>
                                <th>age</th>
                                <th>sexe</th>
                                <th>classe</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- Exemple -->
                            <tr>
                            <?php foreach($filtered_etudiants as $eleve): ?>
                                <td><?php echo $eleve['nom']; ?></td>
                                <td><?php echo $eleve['age']; ?></td>
                                <td><?php echo $eleve['sexe']; ?></td>
                                <td><?php echo $eleve['classe']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>

            <?php endif; ?>

                <div class="footer">
                    <p>&copy; 2024 CEG François de Mahy. Tous droits réservés.</p>
                </div>
        </div>

            
    </div>

    <script src="../scripts/java.js"></script>
    <!-- <script src="../scripts/liste/AJ-peson.js" type="module"></script> -->
</body>
</html>