<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <link rel="stylesheet" href="<?= $basePath ?>styles/style.css">
    <title>certificat scolarite</title>
</head>
<body>
   <div class="parent">
        <?php
        require_once('../include/header.php'); // chemin relatif selon le dossier
        ?>
        <div class="div3">
            <!-- 
            Structure simple :
            Liste des élèves = recherche a vec barre *
            Bouton Générer certificat 
            Aperçu PDF (facultatif)
            Historique des certificats générés
            -->
            
            <div class="container">

                <h2 class="titre">CERTIFICAT DE SCOLARITÉ</h2>

                <p><strong>Établissement scolaire :</strong> _______________________________________________</p>
                <p><strong>Adresse de l’établissement :</strong> _______________________________________________</p>

                <br>

                <p class="section-title">Informations sur l’élève :</p>

                <p><strong>Nom :</strong> _______________________________________________</p>
                <p><strong>Prénom :</strong> _______________________________________________</p>
                <p><strong>Date de naissance :</strong> _______________________________________________</p>
                <p><strong>Classe fréquentée :</strong> _______________________________________________</p>
                <p><strong>Année scolaire :</strong> _______________________________________________</p>

                <br>

                <p class="section-title">Objet du certificat :</p>
                <p class="texte">
                    Ce certificat est délivré à l'élève pour justifier de sa scolarité au sein de l’établissement mentionné ci-dessus,
                    dans le cadre de l’année scolaire indiquée. Il peut être utilisé à toutes fins légales notamment pour les
                    démarches administratives.
                </p>

                <br>

                <p class="section-title">Engagement de l’établissement :</p>
                <p class="texte">
                    L’établissement atteste que l’élève est régulièrement inscrit et qu’il suit les cours correspondant à la classe
                    indiquée. Le présent certificat est délivré sans date, conformément aux usages et réglementations en vigueur.
                </p>

                <br><br>

                <div class="signatures">
                    <div class="bloc-sign">
                        <p><strong>Le Directeur / La Directrice</strong></p>
                        <p>Signature : _________________________</p>
                    </div>

                    <div class="bloc-sign">
                        <p><strong>Le Responsable de l’élève</strong></p>
                        <p>Signature : _________________________</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>