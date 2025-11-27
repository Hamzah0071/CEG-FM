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
        Contenu indispensable :
        Sélection classe + matière
        Tableau pour saisir les notes
        Bouton Enregistrer / Valider
        Sommaire des moyennes 
        -->
        <div class="page">
    <h2>Saisie des notes</h2>

    <form class="form-filtres">
        <div class="form-group">
            <label>Classe :</label>
            <select name="classe_id">
                <option value="">Sélectionner une classe</option>
                <option value="5a">5ème A</option>
                <option value="5b">5ème B</option>
            </select>
        </div>

        <div class="form-group">
            <label>Matière :</label>
            <select name="matiere_id">
                <option value="">Sélectionner une matière</option>
                <option value="math">Math</option>
                <option value="svt">SVT</option>
            </select>
        </div>

        <button type="submit">Afficher les élèves</button>
    </form>

    <hr>

    <h3>Liste des élèves</h3>

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
                    <td>Rakoto Jean</td>
                    <td><input type="number" step="0.1" name="note[1]" min="0" max="20"></td>
                </tr>

                <tr>
                    <td>Randria Fara</td>
                    <td><input type="number" step="0.1" name="note[2]" min="0" max="20"></td>
                </tr>
            </tbody>
        </table>

        <button type="submit">Enregistrer les notes</button>

    </form>
</div>

        </div>
    </div>
</body>