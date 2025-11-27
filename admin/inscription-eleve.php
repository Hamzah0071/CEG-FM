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
   <div class="parent">
        <?php
        require_once('../include/header.php'); // chemin relatif selon le dossier
        ?>
        <div class="div3">
            <!-- ======
            Contenu essentiel :
            Formulaire complet :
            Nom, prénoms, sexe
            Date de naissance
            Classe d’affectation
            Tuteur + contact
            Photo (facultatif)
            Bouton Valider
            Message de confirmation
            -->
            <div class="page">
                <h2>Inscription / Réinscription Élève</h2>

                <form class="form-inscription">

                    <h3>Informations personnelles</h3>

                    <div class="form-group">
                        <label>Nom :</label>
                        <input type="text" name="nom" required>
                    </div>

                    <div class="form-group">
                        <label>Prénom :</label>
                        <input type="text" name="prenom" required>
                    </div>

                    <div class="form-group">
                        <label>Date de naissance :</label>
                        <input type="date" name="date_naissance" required>
                    </div>

                    <div class="form-group">
                        <label>Sexe :</label>
                        <select name="sexe">
                            <option value="">Choisir</option>
                            <option value="M">Masculin</option>
                            <option value="F">Féminin</option>
                        </select>
                    </div>

                    <hr>

                    <h3>Informations scolaires</h3>

                    <div class="form-group">
                        <label>Classe :</label>
                        <select name="classe_id">
                            <option value="">Choisir une classe</option>
                            <option value="6eme">6ème</option>
                            <option value="5eme">5ème</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Année scolaire :</label>
                        <input type="text" name="annee" placeholder="2024-2025">
                    </div>

                    <hr>

                    <h3>Responsable</h3>

                    <div class="form-group">
                        <label>Nom du responsable :</label>
                        <input type="text" name="responsable">
                    </div>

                    <div class="form-group">
                        <label>Contact :</label>
                        <input type="text" name="contact">
                    </div>

                    <button type="submit">Valider l'inscription</button>
                </form>
            </div>

        </div>
    </div>
</body>