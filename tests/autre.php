<?php
require_once('../include/db.php');
require_once('../include/auth.php');
requireRole(['admin']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Test - CEG FM</title>
</head>
<body>
    <div class="parent">
        <?php require_once('../include/header.php'); ?>

        <div class="div3">
            <h1>Page de test</h1>
            
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