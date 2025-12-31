<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/Sign.css">
    <link rel="icon" type="image/png" href="icone/CEG-fm.png">
    <title>CEG-François de Mahy</title>
</head>
<body>
    <?php require_once "../include/db.php"; ?>
    <!-- ======= -->
<?php
session_start();


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';

    if ($username === '' || $password === '') {
        die("Champs manquants");
    }

    $stmt = $pdo->prepare("
        SELECT id, username, password, role
        FROM utilisateurs
        WHERE username = :username
        LIMIT 1
    ");
    $stmt->execute([':username' => $username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Utilisateur introuvable");
    }

    if (!password_verify($password, $user['password'])) {
        die("Mot de passe incorrect");
    }

    // Connexion OK
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    header("Location: ../index.php");
    exit;
}
?>



    <div class="parent">
        <div class="droit">
            <a href="../index.php" class="back-link">
                <img src="../images/icone/D-arrier.png" alt="Retour">
                <span>Retour au tableau de bord</span>
            </a>
            <h1>Connexion</h1>
            <!-- pour l'administrateur -->
            <p class="subtitle">Entrez votre email et mot de passe pour vous connecter</p>
            <div class="social-login">
                <button class="btn">Se connecter avec Google</button>
                <button class="btn">Se connecter avec Facebook</button>
            </div>
            <div class="or">ou</div>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Nom d’utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="sign-in-btn">Se connecter</button>
            </form>

            <div class="outils">
                <label>
                    <input type="checkbox">
                    Rester connecté
                </label>
                <a href="#" class="forgot-password">Mot de passe oublié ?</a>
            </div>
            <div class="sign-up">
                Vous n'avez pas de compte ?
                <a href="./Sign-Up.php" class="sign-up-link">S'inscrire</a>
            </div>
        </div>
        <div class="gauche">
            <div class="logo-container">
                <img src="../images/icone/CEG-fm.png" alt="Logo CEG FM" class="logo">
                <h2>CEG FM</h2>
                <p class="brand-text">Votre plateforme de confiance</p>
            </div>
        </div>
    </div>
</body>
</html>