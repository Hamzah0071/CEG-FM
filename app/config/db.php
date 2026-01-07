<?php
$pdo = new PDO(
    "mysql:host=localhost;dbname=ceg_fm;charset=utf8",
    "root",
    "",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

// echo "Connexion à la base de données réussie.";

// quand je serais en ligne 
// $mysqlClient = new PDO(
//dns :'mysql:host=sql.hebergeur.com;dbname=mabase;charset=utf8',
// username :'pierre.durand', 
// password :'s3cr3t');
?>
<!-- 
 DSN : Data Source Name. C'est généralement le seul qui change en fonction du 
 type de base de données auquel on se connecte.
 -->