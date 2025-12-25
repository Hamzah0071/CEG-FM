<?php
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=collègues;charset=utf8mb4",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}
// echo "Connexion à la base de données réussie.";