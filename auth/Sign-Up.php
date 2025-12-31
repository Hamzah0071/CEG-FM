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

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    if ($username === '' || $password === '' || $role === '') {
        exit("Champs manquants");
    }

    // Vérifier si username existe
    $check = $pdo->prepare(
        "SELECT id FROM utilisateurs WHERE username = ?"
    );
    $check->execute([$username]);

    if ($check->fetch()) {
        exit("Nom d'utilisateur déjà utilisé");
    }

    // Hash du mot de passe
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertion utilisateur (EN ATTENTE)
    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (username, password_hash, role, statut)
        VALUES (?, ?, ?, 'en_attente')
    ");

    $stmt->execute([
        $username,
        $hash,
        $role
    ]);

    echo "Compte créé. En attente de validation par l'administration.";
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

            <form method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="role">Rôle demandé</label>
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Choisir un rôle</option>
                        <option value="eleve">Élève</option>
                        <option value="prof">Professeur</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <label>
                    <input type="checkbox" required>
                    J’accepte les conditions
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