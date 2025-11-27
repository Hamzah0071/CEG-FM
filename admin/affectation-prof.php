<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/admin/affectation-prof.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <title>Affectation</title>
</head>
<body>
   <div class="parent">
        <?php
        require_once('../include/header.php'); // chemin relatif selon le dossier
        ?>
        <div class="div3">
            <!-- 
            À mettre :
            Formulaire :
            Choisir prof
            Choisir matière
            Choisir classe
            Tableau des affectations actuelles
            Bouton Retirer affectation
            -->
            <div class="page">
                <div class="haut">
                    <h2>Affectation des professeurs</h2>

                    <form class="form-affectation">

                        <div class="form-group">
                            <label>Professeur :</label>
                            <select name="prof_id">
                                <option value="">Sélectionnez un professeur</option>
                                <!-- boucle PHP -->
                                <!-- <?php foreach($profs as $prof): ?> -->
                                <option value="1">Rabe Andrianina</option>
                                <!-- <?php endforeach; ?> -->
                            </select>
                        </div>

                        <div class="form-group">
                            <?php 
                                $matieres = [
                                    ["Malagasy"],
                                    ["Français"],
                                    ["Anglais"],
                                    ["HIST-GEO"],
                                    ["MATH"],
                                    ["PC"],
                                    ["SVT"],
                                    ["TICE"],
                                    ["EPS"],
                                ];
                            ?>
                            <label>Matière :</label>
                            <select name="matiere_id">
                                <option value="">Sélectionnez une matière</option>
                                <?php foreach ($matieres as $m) : ?>
                                <option value="matier"><?= $m[0] ?></option>

                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Classe :</label>
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
                            <select name="classe_id">
                                <option value="">Sélectionnez une classe</option>
                                <?php foreach($classes as $classe) :  ?>
                                <option value="classe"><?= $classe[0] ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select name="initiale_id">
                                <option value="">Sélectionnez une initiales</option>
                                <?php foreach($initiales as $initiale) :  ?>
                                <option value="initiale"><?= $initiale[0] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit">Affecter</button>
                    </form>
                </div>
                <hr>
                <div class="bas">
                    <h3>Affectations existantes</h3>

                    <table class="table-affectations">
                        <thead>
                            <tr>
                                <th>Professeur</th>
                                <th>Matière</th>
                                <th>Classe</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <!-- Exemple -->
                            <tr>
                                <td>Rabe Andrianina</td>
                                <td>Math</td>
                                <td>5ème A</td>
                                <td><a href="#">Retirer</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</body>
<!-- modale comme FB -->