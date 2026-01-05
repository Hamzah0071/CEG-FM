<?php
// app/auth/Sign-In.php

session_start();

// Si déjà connecté, rediriger vers le dashboard approprié
if (isset($_SESSION['role'])) {
    $redirects = [
        'admin' => APP_URL . 'admin/eleves/liste-eleve.php',
        'prof'  => APP_URL . 'prof/mes-classes.php',
        'eleve' => APP_URL . 'eleve/accueil.php'
    ];
    
    $redirect = $redirects[$_SESSION['role']] ?? PUBLIC_URL . 'index.php';
    header("Location: $redirect");
    exit;
}

require_once __DIR__ . '/../config/db.php';

// Utiliser les mêmes constantes que le header
define('BASE_URL', '/social-prof-comunication/');
define('PUBLIC_URL', BASE_URL . 'public/');
define('APP_URL', BASE_URL . 'app/');

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validation de base
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        try {
            // Récupérer l'utilisateur avec ses informations
            $stmt = $pdo->prepare("
                SELECT 
                    u.id as user_id,
                    u.email,
                    u.password_hash,
                    u.role,
                    u.statut,
                    u.compte_verrouille,
                    u.verrouille_jusqu_a,
                    u.tentatives_connexion,
                    p.nom,
                    p.prenom,
                    p.photo_path,
                    CASE 
                        WHEN u.role = 'prof' THEN pr.id
                        WHEN u.role = 'eleve' THEN e.id
                        ELSE NULL
                    END as role_id,
                    CASE 
                        WHEN u.role = 'prof' THEN pr.matricule
                        WHEN u.role = 'eleve' THEN e.matricule
                        ELSE NULL
                    END as matricule
                FROM utilisateurs u
                JOIN personnes p ON u.personne_id = p.id
                LEFT JOIN professeurs pr ON u.id = pr.utilisateur_id
                LEFT JOIN eleves e ON u.id = e.utilisateur_id
                WHERE u.email = ? AND u.deleted_at IS NULL
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error = "Email ou mot de passe incorrect.";
            } 
            // Vérifier si le compte est verrouillé
            elseif ($user['compte_verrouille'] && $user['verrouille_jusqu_a'] && strtotime($user['verrouille_jusqu_a']) > time()) {
                $temps_restant = ceil((strtotime($user['verrouille_jusqu_a']) - time()) / 60);
                $error = "Compte temporairement verrouillé. Réessayez dans $temps_restant minutes.";
            }
            // Vérifier le statut du compte
            elseif ($user['statut'] === 'en_attente') {
                $error = "Votre compte est en attente de validation par l'administration.";
            }
            elseif ($user['statut'] === 'refuse') {
                $error = "Votre demande d'inscription a été refusée. Contactez l'administration.";
            }
            elseif ($user['statut'] === 'suspendu') {
                $error = "Votre compte a été suspendu. Contactez l'administration.";
            }
            elseif ($user['statut'] === 'archive') {
                $error = "Ce compte n'est plus actif.";
            }
            // Vérifier le mot de passe
            elseif (!password_verify($password, $user['password_hash'])) {
                // Incrémenter les tentatives échouées
                $new_attempts = $user['tentatives_connexion'] + 1;
                
                // Verrouiller après 5 tentatives
                if ($new_attempts >= 5) {
                    $lock_until = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                    $pdo->prepare("
                        UPDATE utilisateurs 
                        SET tentatives_connexion = ?, 
                            compte_verrouille = 1,
                            verrouille_jusqu_a = ?
                        WHERE id = ?
                    ")->execute([$new_attempts, $lock_until, $user['user_id']]);
                    
                    $error = "Trop de tentatives échouées. Compte verrouillé pendant 30 minutes.";
                } else {
                    $pdo->prepare("UPDATE utilisateurs SET tentatives_connexion = ? WHERE id = ?")
                        ->execute([$new_attempts, $user['user_id']]);
                    
                    $remaining = 5 - $new_attempts;
                    $error = "Email ou mot de passe incorrect. ($remaining tentatives restantes)";
                }
            }
            // Connexion réussie !
            else {
                // Réinitialiser les tentatives et déverrouiller
                $pdo->prepare("
                    UPDATE utilisateurs 
                    SET tentatives_connexion = 0,
                        compte_verrouille = 0,
                        verrouille_jusqu_a = NULL,
                        last_login = NOW()
                    WHERE id = ?
                ")->execute([$user['user_id']]);

                // Logger la connexion
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                $pdo->prepare("
                    INSERT INTO logs_audit (utilisateur_id, table_name, record_id, action, ip_address, user_agent)
                    VALUES (?, 'utilisateurs', ?, 'LOGIN', ?, ?)
                ")->execute([$user['user_id'], $user['user_id'], $ip, $user_agent]);

                // Créer la session
                session_regenerate_id(true); // Sécurité : nouveau ID de session
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = trim($user['prenom'] . ' ' . $user['nom']);
                $_SESSION['email'] = $user['email'];
                $_SESSION['matricule'] = $user['matricule'];
                $_SESSION['last_activity'] = time();
                
                if ($user['photo_path']) {
                    $_SESSION['profile_image'] = $user['photo_path'];
                }

                // Informations spécifiques au rôle
                if ($user['role'] === 'prof') {
                    $_SESSION['prof_id'] = $user['role_id'];
                    
                    // Récupérer les matières enseignées
                    $stmt = $pdo->prepare("
                        SELECT DISTINCT m.nom 
                        FROM affectations_prof ap
                        JOIN matieres m ON ap.matiere_id = m.id
                        WHERE ap.prof_id = ? AND ap.deleted_at IS NULL
                        LIMIT 1
                    ");
                    $stmt->execute([$user['role_id']]);
                    $matiere = $stmt->fetch(PDO::FETCH_COLUMN);
                    if ($matiere) {
                        $_SESSION['matiere'] = $matiere;
                    }
                } 
                elseif ($user['role'] === 'eleve') {
                    $_SESSION['eleve_id'] = $user['role_id'];
                    
                    // Récupérer la classe actuelle
                    $stmt = $pdo->prepare("
                        SELECT CONCAT(c.niveau, ' ', c.nom) as classe_nom
                        FROM inscriptions i
                        JOIN classes c ON i.classe_id = c.id
                        JOIN annee_scolaire a ON i.annee_scolaire_id = a.id
                        WHERE i.eleve_id = ? AND a.actif = 1 AND i.deleted_at IS NULL
                    ");
                    $stmt->execute([$user['role_id']]);
                    $classe = $stmt->fetch(PDO::FETCH_COLUMN);
                    if ($classe) {
                        $_SESSION['classe'] = $classe;
                    }
                }

                // Cookie "Se souvenir de moi"
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 jours
                    
                    // TODO : Stocker le token hashé en base avec expiration
                }

                // Redirection vers le dashboard approprié
                $redirects = [
                    'admin' => APP_URL . 'admin/eleves/liste-eleve.php',
                    'prof'  => APP_URL . 'prof/mes-classes.php',
                    'eleve' => APP_URL . 'eleve/accueil.php'
                ];
                
                $redirect = $redirects[$user['role']] ?? PUBLIC_URL . 'index.php';
                
                $_SESSION['success_message'] = "Bienvenue, " . $_SESSION['username'] . " !";
                
                header("Location: $redirect");
                exit;
            }

        } catch (Exception $e) {
            error_log("Erreur connexion: " . $e->getMessage());
            $error = "Une erreur technique est survenue. Veuillez réessayer.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | CEG FM</title>
    <link rel="icon" type="image/png" href="<?= PUBLIC_URL ?>assets/images/icone/CEG-fm.png">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>assets/styles/Sign.css">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>assets/icon/fontAwesome/all.min.css">
    <style>
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
        .alert-error {
            background-color: #fee2e2;
            border: 1px solid #f87171;
            color: #991b1b;
        }
        .alert-error i {
            color: #ef4444;
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
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .checkbox input {
            width: auto;
        }
        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .forgot-link:hover {
            text-decoration: underline;
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
        .divider {
            text-align: center;
            margin: 2rem 0 1.5rem;
            color: #6b7280;
            position: relative;
        }
        .divider::before,
        .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40%;
            height: 1px;
            background: #e5e7eb;
        }
        .divider::before {
            left: 0;
        }
        .divider::after {
            right: 0;
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
        .test-mode-link {
            text-align: center;
            margin-top: 1rem;
            padding: 1rem;
            background: #fef3c7;
            border-radius: 8px;
            border: 1px solid #fbbf24;
        }
        .test-mode-link a {
            color: #92400e;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .test-mode-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="parent">
    <div class="gauche">
        <div class="logo-container">
            <img src="<?= PUBLIC_URL ?>assets/images/icone/CEG-fm.png" class="logo" alt="Logo CEG FM">
            <h2>CEG FM</h2>
            <p class="brand-text">Excellence • Discipline • Réussite</p>
        </div>
    </div>

    <div class="droit">
        <h1>Connexion</h1>
        <p class="subtitle">Accédez à votre espace personnel</p>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    <strong><?= htmlspecialchars($error) ?></strong>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="on">
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       value="<?= htmlspecialchars($email ?? '') ?>" 
                       required 
                       placeholder="votre.email@example.com"
                       autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       placeholder="Entrez votre mot de passe"
                       autocomplete="current-password">
            </div>

            <div class="form-options">
                <label class="checkbox">
                    <input type="checkbox" name="remember">
                    <span>Se souvenir de moi</span>
                </label>
                <a href="<?= APP_URL ?>auth/forgot-password.php" class="forgot-link">
                    Mot de passe oublié ?
                </a>
            </div>

            <button type="submit" class="sign-in-btn">
                <i class="fa-solid fa-right-to-bracket"></i>
                Se connecter
            </button>
        </form>

        <div class="divider">ou</div>

        <div class="sign-up">
            Pas encore de compte ? 
            <a href="<?= APP_URL ?>auth/Sign-Up.php">S'inscrire</a>
        </div>

        <?php if ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false): ?>
            <div class="test-mode-link">
                <a href="<?= BASE_URL ?>tests/test-login.php">
                    <i class="fa-solid fa-flask"></i>
                    Mode Test - Connexion rapide (dev)
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Animation au chargement
document.addEventListener('DOMContentLoaded', function() {
    const droit = document.querySelector('.droit');
    droit.style.opacity = '0';
    droit.style.transform = 'translateY(20px)';
    
    setTimeout(function() {
        droit.style.transition = 'all 0.5s ease';
        droit.style.opacity = '1';
        droit.style.transform = 'translateY(0)';
    }, 100);
});
</script>

</body>
</html>