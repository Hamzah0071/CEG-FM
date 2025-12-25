<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/Sign.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <title>CEG-François de Mahy</title>
</head>
<body>
   <?php require_once "../include/db.php"; ?>
<!-- ====== -->
<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["name"] ?? '');
    $email    = trim($_POST["email"] ?? '');
    $role     = $_POST["role"] ?? '';
    $password = $_POST["password"] ?? '';

    if ($username === '' || $email === '' || $role === '' || $password === '') {
        echo "Champs manquants";
        exit;
    }

    // Vérifier si l'email existe déjà
    $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email");
    $check->execute([':email' => $email]);

    if ($check->fetch()) {
        echo "Cet email est déjà utilisé";
        exit;
    }

    // Hash du mot de passe
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertion
    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (username, email, password, role)
        VALUES (:username, :email, :password, :role)
    ");

    $stmt->execute([
        ':username' => $username,
        ':email'    => $email,
        ':password' => $hash,
        ':role'     => $role
    ]);

    echo "Inscription OK";
}
?>



    <div class="parent">
        <div class="droit">
            <a href="../index.php" class="back-link">
                <img src="../images/icone/D-arrier.png" alt="Retour">
                <span>Retour au tableau de bord</span>
            </a>
            <h1>Inscription</h1>
            <p class="subtitle">Entrez votre email et mot de passe pour vous connecter</p>
            <div class="social-login">
                <button class="btn">Se connecter avec Google</button>
                <button class="btn">Se connecter avec Facebook</button>
            </div>
            <div class="or">ou</div>
            <form method="POST" action="">

                <div class="form-group">
                    <label for="name">Nom et prénom</label>
                    <input type="text" id="name" name="name" placeholder="Entrez votre nom" required>
                </div>

                <div class="form-group">
                    <label for="role">Rôle</label>
                    <select id="role" name="role" required>
                        <option value="">-- Choisir un rôle --</option>
                        <option value="admin">Admin</option>
                        <option value="eleve">eleve</option>
                        <option value="prof">Prof</option>
                    </select>
                </div>



                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Entrez votre email" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                </div>
                <label>
                    <input type="checkbox">
                    En créant un compte, vous acceptez les Conditions Générales d'Utilisation et notre Politique de Confidentialité
                </label>
                <button type="submit" class="sign-in-btn">S'inscrire</button>
            </form>
            <div class="sign-up">
                Vous avez déjà un compte ?
                <a href="./Sign-In.php" class="sign-up-link">Connexion</a>
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