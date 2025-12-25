<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEG-Francoi de mahy</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="icon" type="image/png" href="./images/icone/CEG-fm.png">
</head>
<body>
    <div class="parent">

        <!-- Page d’accueil publique -->
        <?php require_once(__DIR__ . '/../include/header.php'); 
        
        require_once "../include/auth.php";
        checkRole('prof');
        ?>
    
        <div class="div3">
            
        </div>
    </div>
    <script src="../scripts/java.js"></script>
    <script src="../scripts/calendrier/index.js" type="module"></script>
</body>
</html>