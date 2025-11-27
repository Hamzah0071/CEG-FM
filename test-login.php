<?php
// test-login.php
session_start();

if (isset($_GET['as'])) {
    $role = $_GET['as'];

    if ($role === 'admin') {
        $_SESSION['role'] = 'admin';
        $_SESSION['username'] = 'Administrateur';
        $_SESSION['user_id'] = 1;
    }
    elseif ($role === 'prof') {
        $_SESSION['role'] = 'prof';
        $_SESSION['username'] = 'Mr Ibrahim';
        $_SESSION['matier'] = 'MATH';
        $_SESSION['classe'] = '6ème A';
        $_SESSION['user_id'] = 99;
    }
    elseif ($role === 'eleve') {
        $_SESSION['role'] = 'eleve';
        $_SESSION['username'] = 'B.M Hamzah';
        $_SESSION['classe'] = '6ème A';
        $_SESSION['user_id'] = 150;
    }
    elseif ($role === 'deconnect') {
        session_destroy();
        echo "Déconnecté !";
        exit;
    }

    header("Location: index.php");
    exit;
}
?>

<h2>Test de connexion rapide</h2>
<p>Clique sur ton rôle :</p>
<ul>
    <li><a href="?as=admin"><strong>Se connecter en Admin</strong></a></li>
    <li><a href="?as=prof">Se connecter en Professeur</a></li>
    <li><a href="?as=eleve">Se connecter en Élève</a></li>
    <li><a href="?as=deconnect">Se déconnecter</a></li>
</ul>
<p>Après connexion, va à <a href="index.php">l'accueil</a> pour voir le menu adapté.</p>