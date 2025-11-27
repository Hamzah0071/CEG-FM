<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/admin/bulletin.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <title>Document</title>
</head>
<body>
   <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
   <div class="parent">
        <?php
        require_once('../include/header.php'); // chemin relatif selon le dossier
        ?>
        <div class="div3">
            <!-- 
            Sélecteur classe → matière
            Tableau élèves / note
            Bouton Enregistrer
            -->
            <div class="page">
                <h2>Saisie des notes</h2>

                <form class="form-filtres">
                    <div class="form-group">
                        <?php 
                            $classes = [
                                    ["6 éme"],
                                    ["5 éme"],
                                    ["4 éme"],
                                    ["3 éme"]
                            ];
                            $initiales = [
                                    ['A'],
                                    ['B'],
                                    ['C'],
                                    ['D'],
                                    ['E'],
                                    ['F']
                            ]
                        ?>
                        <label>Classe :</label>
                        <select name="classe_id">
                            <option value="">Sélectionner une classe</option>
                            <?php foreach($classes as $classe):  ?>
                            <option value="classe"><?= $classe[0] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <div class="form-group">
                        <label>Initial :</label>
                        <select name="matiere_id">
                            <option value="">Sélectionner une initiale</option>
                            <?php foreach($initiales as $initiale):  ?>
                            <option value="initiale"><?= $initiale[0] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit">Afficher les élèves</button>
                </form>
<br>
                <hr>
<br>
                <h3>Liste des élèves</h3>
                <?php $etudiant = [
                    [
                        'nom' => 'BOUCHIRANY Misizara',
                        'age' => '15',
                        'année' => '10-01-10',
                        'is_enabled' => true,
                    ],
                    [
                        'nom' => 'ADIL Nasser',
                        'age' => '17',
                        'année' => '12-01-07',
                        'is_enabled' => true
                    ],
                    [
                        'nom' => 'ABDOULEN Khaed',
                        'age' => '20',
                        'année' => '02-11-07',
                        'is_enabled' => true,
                    ],
                    [
                        'nom' => 'Hamzah Misizara',
                        'age' => '18',
                        'année' => '12-01-07',
                        'is_enabled' => true,
                    ],
                    [
                        'nom' => "HAZ'MAH",
                        'age' => '20',
                        'année' => '12-07-05',
                        'is_enabled' => true,
                    ],
                    [
                        'nom' => 'HAMZAH',
                        'age' => '15',
                        'année' => '12-07-05',
                        'is_enabled' => true,
                    ],
                    ];
                    $selected_nom = $_POST['nom'] ?? '';
                    ?>

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

<?php
    // Filtrer les etudiant
    $filtered_etudiant = [];
    foreach($etudiant as $eleve) {
        // Vérifier si la recette est activée
        if (array_key_exists('is_enabled', $eleve) && $eleve['is_enabled'] == true) {
            // Si aucun auteur n'est sélectionné OU si l'auteur correspond
            if (empty($selected_nom) || 
                (isset($eleve['nom']) && strtolower($eleve['nom']) == strtolower($selected_nom))) {
                $filtered_etudiant[] = $eleve;
            }
        }
    }
?>

<h2>
    <?php if (empty($selected_nom)): ?>
            Toutes les information de l'eleve : (<?php echo count($filtered_etudiant); ?>)
    <?php else: ?>
            Son nom: "<?php echo htmlspecialchars($selected_nom); ?>"
    <?php endif; ?>
</h2>

<!-- Affiche un etudiant différent selon le cas -->
<?php if (empty($filtered_etudiant)): ?>
    <p class="no-etudiant">
        <?php if (empty($selected_nom)): ?>
            Aucune etudiant est disponible pour le moment.
        <?php else: ?>
            Aucune info trouvée pour l'Eleve "<?php echo htmlspecialchars($selected_nom); ?>".
        <?php endif; ?>
    </p>

<?php else: ?>
        <?php foreach($filtered_etudiant as $eleve): ?>
            <div class="eleve">
                <h3><?php echo $eleve['nom']; ?></h3>
                <p><strong>Age :</strong> <?php echo $eleve['age']; ?></p>
                <p><strong>date de naissance :</strong> <?php echo $eleve['année']; ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>


                <form class="form-notes">

                    <table class="table-notes">
                        <thead>
                            <tr>
                                <th>Élève</th>
                                <th>Note</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- Exemple -->
                            <tr>
                            <?php foreach($filtered_etudiant as $eleve): ?>
                                <td><?php echo $eleve['nom']; ?></td>
                                <td><input type="number" step="0.1" name="note[1]" min="0" max="20"></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <button type="submit">Enregistrer les notes</button>

                </form>
            </div>
        </div>