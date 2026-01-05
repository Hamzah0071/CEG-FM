<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - CEG FM</title>
    <link rel="stylesheet" href="../../../public/assets/styles/style.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
</head>
<body>
    <div class="parent">
        <?php require_once('../include/header.php'); ?>

        <!-- CONTENU PRINCIPAL -->
        <div class="div3">
            <h1>Titre de la page</h1>
            
            <!-- Votre contenu ici -->
            <div class="card">
                <div class="card-header">Ma carte</div>
                <p>Contenu de la carte...</p>
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <p>&copy; <?= date('Y') ?> CEG François de Mahy. Tous droits réservés.</p>
            </div>
        </div>
    </div>
    
    <script src="../scripts/java.js"></script>
</body>
</html>