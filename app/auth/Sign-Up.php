<?php
// app/auth/Sign-Up.php

require_once __DIR__ . '/../config/db.php';

// Détection intelligente du chemin de base
function getBasePath() {
    $currentPath = $_SERVER['SCRIPT_NAME'];
    $pathParts = explode('/', trim($currentPath, '/'));
    
    // Si on est dans app/auth/
    if (($pathParts[count($pathParts) - 2] ?? '') === 'auth') {
        return '../../public/';
    }
    return '../public/';
}

$basePath = getBasePath();

$message = '';
$errors = [];

// Initialisation des champs pour repopulation
$email = $nom = $prenom = $date_naissance = $sexe = $telephone = $adresse = $role = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Récupération et nettoyage
    $email          = trim($_POST['email'] ?? '');
    $password       = $_POST['password'] ?? '';
    $confirm_pass   = $_POST['confirm_password'] ?? '';
    $nom            = trim($_POST['nom'] ?? '');
    $prenom         = trim($_POST['prenom'] ?? '');
    $date_naissance = trim($_POST['date_naissance'] ?? '');
    $sexe           = $_POST['sexe'] ?? '';
    $telephone      = trim($_POST['telephone'] ?? '');
    $adresse        = trim($_POST['adresse'] ?? '');
    $role           = $_POST['role'] ?? '';

    // === VALIDATIONS ===
    if (empty($email))          $errors[] = "L'adresse email est obligatoire.";
    if (empty($password))       $errors[] = "Le mot de passe est obligatoire.";
    if (empty($nom))            $errors[] = "Le nom est obligatoire.";
    if (empty($prenom))         $errors[] = "Le prénom est obligatoire.";
    if (empty($date_naissance)) $errors[] = "La date de naissance est obligatoire.";
    if (empty($sexe))           $errors[] = "Le sexe est obligatoire.";
    if (empty($role))           $errors[] = "Le rôle est obligatoire.";

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide.";
    }

    if ($role && !in_array($role, ['eleve', 'prof'])) {
        $errors[] = "Rôle invalide.";
    }

    if ($password && strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }

    if ($password !== $confirm_pass) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    if ($sexe && !in_array($sexe, ['M', 'F'])) {
        $errors[] = "Sexe invalide.";
    }

    if ($telephone && !preg_match('/^[0-9\s\+\-\(\)]{10,20}$/', $telephone)) {
        $errors[] = "Format de téléphone invalide.";
    }

    // Validation date de naissance
    if ($date_naissance) {
        $date = DateTime::createFromFormat('Y-m-d', $date_naissance);
        if (!$date || $date->format('Y-m-d') !== $date_naissance) {
            $errors[] = "Format de date invalide.";
        } else {
            $age = (new DateTime())->diff($date)->y;
            if ($age < 5 || $age > 100) {
                $errors[] = "L'âge doit être compris entre 5 et 100 ans.";
            }
        }
    }

    // Traitement si pas d'erreurs
    if (empty($errors)) {
        try {
            // Vérifier si l'email existe déjà
            $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND deleted_at IS NULL");
            $check->execute([$email]);
            if ($check->fetch()) {
                $errors[] = "Cette adresse email est déjà utilisée.";
            } else {
                $pdo->beginTransaction();

                // 1. Insertion dans personnes
                $stmt = $pdo->prepare("
                    INSERT INTO personnes (nom, prenom, date_naissance, sexe, telephone, adresse)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $nom,
                    $prenom,
                    $date_naissance,
                    $sexe,
                    $telephone ?: null,
                    $adresse ?: null
                ]);
                $personne_id = $pdo->lastInsertId();

                // 2. Insertion dans utilisateurs
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    INSERT INTO utilisateurs (personne_id, email, password_hash, role, statut)
                    VALUES (?, ?, ?, ?, 'en_attente')
                ");
                $stmt->execute([$personne_id, $email, $hash, $role]);
                $utilisateur_id = $pdo->lastInsertId();

                // 3. Insertion spécifique rôle
                if ($role === 'eleve') {
                    $stmt = $pdo->prepare("
                        INSERT INTO eleves (utilisateur_id, personne_id, date_inscription)
                        VALUES (?, ?, CURDATE())
                    ");
                    $stmt->execute([$utilisateur_id, $personne_id]);
                } elseif ($role === 'prof') {
                    $stmt = $pdo->prepare("
                        INSERT INTO professeurs (utilisateur_id, personne_id)
                        VALUES (?, ?)
                    ");
                    $stmt->execute([$utilisateur_id, $personne_id]);
                }

                $pdo->commit();

                $message = "<div class='alert alert-success'>
                    <i class='fa-solid fa-circle-check'></i>
                    <div>
                        <strong>Inscription réussie !</strong>
                        <p>Votre compte a été créé et est en attente de validation par l'administration.</p>
                        <p>Vous serez notifié par email dès son activation.</p>
                    </div>
                </div>";

                // Réinitialiser le formulaire
                $email = $nom = $prenom = $date_naissance = $sexe = $telephone = $adresse = $role = '';
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = "Une erreur technique est survenue. Veuillez réessayer plus tard.";
            error_log("Erreur inscription: " . $e->getMessage());
        }
    }

    // Affichage des erreurs
    if (!empty($errors)) {
        $message = "<div class='alert alert-error'>
            <i class='fa-solid fa-circle-exclamation'></i>
            <div>
                <strong>Erreurs dans le formulaire :</strong>
                <ul>";
        foreach ($errors as $err) {
            $message .= "<li>" . htmlspecialchars($err) . "</li>";
        }
        $message .= "</ul></div></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | CEG FM</title>
    <link rel="stylesheet" href="../../public/assets/styles/Sign.css">
    <link rel="icon" type="image/png" href="<?= $basePath ?>assets/images/icone/CEG-fm.png">
    <!-- <link rel="stylesheet" href="assets/css/Sign.css"> -->
    <link rel="stylesheet" href="<?= $basePath ?>assets/icon/fontAwesome/all.min.css">
    <style>
        /* Styles pour les alertes */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }
        .alert i {
            font-size: 1.5rem;
            margin-top: 0.25rem;
        }
        .alert-success {
            background-color: #d1fae5;
            border: 1px solid #34d399;
            color: #065f46;
        }
        .alert-success i {
            color: #10b981;
        }
        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #f87171;
            color: #991b1b;
        }
        .alert-error i {
            color: #ef4444;
        }
        .alert ul {
            margin: 0.5rem 0 0 0;
            padding-left: 1.25rem;
        }
        .alert p {
            margin: 0.25rem 0;
        }
        .alert strong {
            display: block;
            margin-bottom: 0.5rem;
        }
        
        /* Amélioration du formulaire */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        .form-group label.required::after {
            content: " *";
            color: #ef4444;
        }
        .form-group small {
            color: #6b7280;
            font-size: 0.875rem;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1.5rem 0;
        }
        .checkbox input {
            width: auto;
        }
        .sign-in-btn {
            width: 100%;
            padding: 0.875rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.125rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .sign-in-btn:hover {
            background: #5a67d8;
        }
        .or {
            text-align: center;
            margin: 2rem 0 1.5rem;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
        }
        .sign-up {
            text-align: center;
            margin-top: 1.5rem;
            color: #6b7280;
        }
        .sign-up a {
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
        }
        .sign-up a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="parent">
    <div class="gauche">
        <div class="logo-container">
            <img src="<?= $basePath ?>assets/images/icone/CEG-fm.png" class="logo" alt="Logo CEG FM">
            <h2>CEG FM</h2>
            <p class="brand-text">Excellence • Discipline • Réussite</p>
        </div>
    </div>

    <div class="droit">
        <h1>Inscription</h1>
        <p class="subtitle">Créez votre compte pour rejoindre la plateforme CEG FM</p>

        <?= $message ?>

        <form method="POST" autocomplete="off" novalidate>

            <div class="or">Informations personnelles</div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nom" class="required">Nom</label>
                    <input type="text" 
                           id="nom" 
                           name="nom" 
                           value="<?= htmlspecialchars($nom) ?>" 
                           required 
                           placeholder="RAKOTONDRABE">
                </div>
                <div class="form-group">
                    <label for="prenom" class="required">Prénom(s)</label>
                    <input type="text" 
                           id="prenom" 
                           name="prenom" 
                           value="<?= htmlspecialchars($prenom) ?>" 
                           required 
                           placeholder="Jean Michel">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="date_naissance" class="required">Date de naissance</label>
                    <input type="date" 
                           id="date_naissance" 
                           name="date_naissance" 
                           value="<?= htmlspecialchars($date_naissance) ?>" 
                           required 
                           max="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label for="sexe" class="required">Sexe</label>
                    <select id="sexe" name="sexe" required>
                        <option value="" disabled <?= $sexe ? '' : 'selected' ?>>Choisir</option>
                        <option value="M" <?= $sexe === 'M' ? 'selected' : '' ?>>Masculin</option>
                        <option value="F" <?= $sexe === 'F' ? 'selected' : '' ?>>Féminin</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="telephone">Téléphone <small>(optionnel)</small></label>
                <input type="tel" 
                       id="telephone" 
                       name="telephone" 
                       value="<?= htmlspecialchars($telephone) ?>" 
                       placeholder="+261 34 00 000 00">
            </div>

            <div class="form-group">
                <label for="adresse">Adresse <small>(optionnel)</small></label>
                <textarea id="adresse" 
                          name="adresse" 
                          rows="2" 
                          placeholder="Lot IVB 123 Analakely..."><?= htmlspecialchars($adresse) ?></textarea>
            </div>

            <div class="or">Informations de connexion</div>

            <div class="form-group">
                <label for="email" class="required">Adresse email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= htmlspecialchars($email) ?>" 
                       required 
                       placeholder="jean.michel@example.com">
                <small>Utilisée pour vous connecter</small>
            </div>

            <div class="form-group">
                <label for="role" class="required">Rôle demandé</label>
                <select id="role" name="role" required>
                    <option value="" disabled <?= $role ? '' : 'selected' ?>>Choisir un rôle</option>
                    <option value="eleve" <?= $role === 'eleve' ? 'selected' : '' ?>>Élève</option>
                    <option value="prof" <?= $role === 'prof' ? 'selected' : '' ?>>Professeur</option>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password" class="required">Mot de passe</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           minlength="8" 
                           placeholder="Au moins 8 caractères">
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="required">Confirmer</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required 
                           minlength="8"
                           placeholder="Retapez le mot de passe">
                </div>
            </div>

            <label class="checkbox">
                <input type="checkbox" required>
                J'accepte les <a href="<?= $basePath ?>conditions.php" target="_blank">conditions d'utilisation</a>
            </label>

            <button type="submit" class="sign-in-btn">
                <i class="fa-solid fa-user-plus"></i>
                S'inscrire
            </button>
        </form>

        <div class="sign-up">
            Déjà inscrit ? 
            <a href="../auth/Sign-In.php">Se connecter</a>
        </div>
    </div>
</div>

<script>
// Confirmation mot de passe en temps réel
const password = document.getElementById('password');
const confirmPassword = document.getElementById('confirm_password');

function validatePassword() {
    if (confirmPassword.value && confirmPassword.value !== password.value) {
        confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
    } else {
        confirmPassword.setCustomValidity('');
    }
}

password.addEventListener('input', validatePassword);
confirmPassword.addEventListener('input', validatePassword);

// Animation au chargement
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.droit').style.opacity = '0';
    document.querySelector('.droit').style.transform = 'translateY(20px)';
    
    setTimeout(function() {
        document.querySelector('.droit').style.transition = 'all 0.5s ease';
        document.querySelector('.droit').style.opacity = '1';
        document.querySelector('.droit').style.transform = 'translateY(0)';
    }, 100);
});
</script>

</body>
</html>