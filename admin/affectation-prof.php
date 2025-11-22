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
            À mettre :
            Formulaire :
            Choisir prof
            Choisir matière
            Choisir classe
            Tableau des affectations actuelles
            Bouton Retirer affectation
            -->
            <div class="page">
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
            <label>Matière :</label>
            <select name="matiere_id">
                <option value="">Sélectionnez une matière</option>
                <option value="1">Math</option>
                <option value="2">SVT</option>
            </select>
        </div>

        <div class="form-group">
            <label>Classe :</label>
            <select name="classe_id">
                <option value="">Sélectionnez une classe</option>
                <option value="6eme">6ème</option>
                <option value="5eme">5ème</option>
            </select>
        </div>

        <button type="submit">Affecter</button>
    </form>

    <hr>

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