<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - CEG FM</title>
    <link rel="stylesheet" href="../../public/assets/styles/style.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
</head>
<body>
    <div class="parent">
       <?php
        require_once __DIR__ . '/../include/auth_check.php';
        require_role('eleve');

        $pageTitle = 'Ma Classe';
        require_once __DIR__ . '/../include/header.php';
        ?>


        <div class="div3">
            <h1>MA CLASSE</h1>
            
            <div class="card">
                <div class="card-header">Test Card</div>
                <p>Si vous voyez ceci correctement, tout fonctionne ! 🎉</p>
            </div>
            
            <div class="footer">
                <p>&copy; 2024 CEG François de Mahy</p>
            </div>
        </div>
    </div>
    
    <script src="../scripts/java.js"></script>
</body>
</html>