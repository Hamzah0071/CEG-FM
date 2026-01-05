<?php
/**
 * Fichier de test de connexion rapide
 * Emplacement : tests/test-login.php
 * 
 * ⚠️ ATTENTION : Supprimer ce fichier en production !
 */

session_start();

$basePath = '../public/';

$message = '';
$currentRole = $_SESSION['role'] ?? 'aucun';
$currentUser = $_SESSION['username'] ?? 'Non connecté';

// Gestion des actions
if (isset($_GET['as'])) {
    $action = $_GET['as'];

    if ($action === 'deconnect') {
        session_unset();
        session_destroy();
        session_start();
        $message = "✓ Déconnexion réussie !";
        $currentRole = 'aucun';
        $currentUser = 'Non connecté';
    } 
    elseif (in_array($action, ['admin', 'prof', 'eleve'])) {
        session_unset();

        $_SESSION['role'] = $action;
        $_SESSION['user_id'] = [
            'admin' => 1,
            'prof'  => 99,
            'eleve' => 150
        ][$action];

        switch ($action) {
            case 'admin':
                $_SESSION['username'] = 'Administrateur Principal';
                $_SESSION['email'] = 'admin@ceg-fm.edu';
                break;
                
            case 'prof':
                $_SESSION['username'] = 'M. Ibrahim RAKOTO';
                $_SESSION['email'] = 'ibrahim.rakoto@ceg-fm.edu';
                $_SESSION['prof_id'] = 5;
                $_SESSION['matiere'] = 'Mathématiques';
                $_SESSION['classe'] = '6ème A';
                break;
                
            case 'eleve':
                $_SESSION['username'] = 'B.M Hamzah ANDRIANINA';
                $_SESSION['email'] = 'hamzah.andrianina@eleve.ceg-fm.edu';
                $_SESSION['eleve_id'] = 42;
                $_SESSION['classe'] = '6ème A';
                $_SESSION['numero_matricule'] = '2024-6A-042';
                break;
        }

        $_SESSION['last_activity'] = time();
        $_SESSION['is_test_account'] = true;

        $message = "✓ Connecté en tant que <strong>" . ucfirst($action) . "</strong> !";
        
        // Redirection vers les pages existantes
        $redirects = [
            'admin' => '../app/admin/eleves/liste-eleve.php',
            'prof'  => '../app/prof/mes-classes.php',
            'eleve' => '../app/eleve/ma-classe.php'
        ];
        
        header("Location: {$redirects[$action]}");
        exit;
    } 
    else {
        $message = "⚠ Rôle invalide : " . htmlspecialchars($action);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login Rapide | CEG FM</title>
    <link rel="icon" type="image/png" href="<?= $basePath ?>assets/images/icone/CEG-fm.png">
    <link rel="stylesheet" href="<?= $basePath ?>assets/icon/fontAwesome/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 2.5rem;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .header img {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
        }
        
        .header h1 {
            color: #667eea;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            color: #6b7280;
            font-size: 0.95rem;
        }
        
        .warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
        }
        
        .warning i {
            color: #f59e0b;
            font-size: 1.25rem;
            margin-top: 0.125rem;
        }
        
        .warning-content {
            flex: 1;
        }
        
        .warning strong {
            display: block;
            color: #92400e;
            margin-bottom: 0.25rem;
        }
        
        .warning p {
            color: #78350f;
            font-size: 0.9rem;
            margin: 0;
        }
        
        .message {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            color: #065f46;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .message i {
            color: #10b981;
            font-size: 1.25rem;
        }
        
        .current-session {
            background: #f3f4f6;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .current-session h3 {
            color: #374151;
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .session-info {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .session-badge {
            background: white;
            padding: 0.5rem 0.875rem;
            border-radius: 6px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .session-badge i {
            color: #667eea;
        }
        
        .role-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .role-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }
        
        .role-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
        }
        
        .role-card i {
            font-size: 2.5rem;
        }
        
        .role-card strong {
            font-size: 1.125rem;
        }
        
        .role-card.admin {
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        }
        
        .role-card.prof {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        }
        
        .role-card.eleve {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }
        
        .logout-btn {
            display: block;
            width: 100%;
            background: #ef4444;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: background 0.2s;
            cursor: pointer;
        }
        
        .logout-btn:hover {
            background: #dc2626;
        }
        
        .logout-btn i {
            margin-right: 0.5rem;
        }
        
        .footer {
            margin-top: 2rem;
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="<?= $basePath ?>assets/images/icone/CEG-fm.png" alt="Logo CEG FM">
        <h1>Test Login Rapide</h1>
        <p>Environnement de développement</p>
    </div>

    <div class="warning">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div class="warning-content">
            <strong>Mode Développement Uniquement</strong>
            <p>Ce fichier doit être supprimé avant la mise en production. Il permet de tester les différents rôles sans authentification réelle.</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="message">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= $message ?></span>
        </div>
    <?php endif; ?>

    <div class="current-session">
        <h3>
            <i class="fa-solid fa-user-circle"></i>
            Session actuelle
        </h3>
        <div class="session-info">
            <div class="session-badge">
                <i class="fa-solid fa-shield-halved"></i>
                <span><strong>Rôle:</strong> <?= htmlspecialchars(ucfirst($currentRole)) ?></span>
            </div>
            <div class="session-badge">
                <i class="fa-solid fa-user"></i>
                <span><strong>Utilisateur:</strong> <?= htmlspecialchars($currentUser) ?></span>
            </div>
        </div>
    </div>

    <p style="text-align: center; color: #6b7280; margin-bottom: 1.5rem; font-size: 0.95rem;">
        Cliquez sur un rôle pour simuler une connexion :
    </p>

    <div class="role-grid">
        <a href="?as=admin" class="role-card admin">
            <i class="fa-solid fa-user-tie"></i>
            <strong>Admin</strong>
        </a>
        
        <a href="?as=prof" class="role-card prof">
            <i class="fa-solid fa-chalkboard-user"></i>
            <strong>Professeur</strong>
        </a>
        
        <a href="?as=eleve" class="role-card eleve">
            <i class="fa-solid fa-graduation-cap"></i>
            <strong>Élève</strong>
        </a>
    </div>

    <?php if ($currentRole !== 'aucun'): ?>
        <a href="?as=deconnect" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i>
            Se déconnecter
        </a>
    <?php endif; ?>

    <div class="footer">
        <a href="<?= $basePath ?>index.php">
            <i class="fa-solid fa-house"></i>
            Retour à l'accueil
        </a>
    </div>
</div>

</body>
</html>