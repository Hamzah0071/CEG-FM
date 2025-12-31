<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/Sign.css">
    <link rel="icon" type="image/png" href="../images/icone/CEG-fm.png">
    <title>CEG-François de Mahy - Inscription</title>
    <style>
        .message { padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center; font-weight: bold; }
        .succes { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .erreur { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <?php require_once "../include/db.php"; ?>

<?php
$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    // Validation des champs
    if ($username === '' || $password === '' || $role === '') {
        $message = "<div class='message erreur'>Tous les champs sont obligatoires.</div>";
    }
    elseif (!in_array($role, ['eleve', 'prof'])) {
        $message = "<div class='message erreur'>Rôle invalide.</div>";
    }
    elseif (strlen($password) < 6) {
        $message = "<div class='message erreur'>Le mot de passe doit contenir au moins 6 caractères.</div>";
    }
    else {
        // Vérifier si le nom d'utilisateur existe déjà
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE username = ?");
        $check->execute([$username]);

        if ($check->fetch()) {
            $message = "<div class='message erreur'>Ce nom d'utilisateur est déjà utilisé.</div>";
        } else {
            try {
                // Hashage du mot de passe
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Insertion de l'utilisateur en attente
                $stmt = $pdo->prepare("
                    INSERT INTO utilisateurs (username, password_hash, role, statut)
                    VALUES (?, ?, ?, 'en_attente')
                ");

                $stmt->execute([$username, $hash, $role]);

                $message = "<div class='message succes'>
                    Compte créé avec succès !<br>
                    Votre demande est en attente de validation par l'administration.
                </div>";
            } catch (PDOException $e) {
                $message = "<div class='message erreur'>
                    Erreur lors de la création du compte. Veuillez réessayer.
                </div>";
            }
        }
    }
}
?>

    <div class="parent">
        <div class="droit">
            <a href="../index.php" class="back-link">
                <img src="../images/icone/D-arrier.png" alt="Retour">
                <span>Retour au tableau de bord</span>
            </a>
            <h1>Inscription</h1>
            <p class="subtitle">Créez votre compte pour accéder à la plateforme</p>
            <div class="social-login">
                <button class="btn">Se connecter avec Google</button>
                <button class="btn">Se connecter avec Facebook</button>
            </div>
            <div class="or">ou</div>

            <?= $message ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($username ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="role">Rôle demandé</label>
                    <select id="role" name="role" required>
                        <option value="" disabled <?= empty($role) ? 'selected' : '' ?>>Choisir un rôle</option>
                        <option value="eleve" <?= ($role ?? '') === 'eleve' ? 'selected' : '' ?>>Élève</option>
                        <option value="prof" <?= ($role ?? '') === 'prof' ? 'selected' : '' ?>>Professeur</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <label>
                    <input type="checkbox" required>
                    J’accepte les conditions d'utilisation
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