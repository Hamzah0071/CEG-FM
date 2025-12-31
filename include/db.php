<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=college;charset=utf8mb4",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

// echo "Connexion à la base de données réussie.";

// quand je serais en ligne 
// $mysqlClient = new PDO(
//dns :'mysql:host=sql.hebergeur.com;dbname=mabase;charset=utf8',
// username :'pierre.durand', 
// password :'s3cr3t');
?>
<!-- DSN : Data Source Name. C'est généralement le seul qui change en fonction du 
 type de base de données auquel on se connecte.
 -->
 <!-- 
$recipesStatement->execute();
$recipes = $recipesStatement->fetchAll();

"Fetch" en anglais signifie « va chercher ».
-->