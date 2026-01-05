<?php
// public/index.php

session_start();

// Si connecté, rediriger vers le dashboard approprié
if (isset($_SESSION['role'])) {
    $redirects = [
        'admin' => '../app/admin/eleves/liste-eleve.php',
        'prof'  => '../app/prof/mes-classes.php',
        'eleve' => '../app/eleve/ma-classe.php'
    ];
    
    $redirect = $redirects[$_SESSION['role']] ?? './index.php';
    header("Location: $redirect");
    exit;
}

// Page d'accueil publique
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEG FM - Accueil</title>
    <link rel="icon" type="image/png" href="./assets/images/icone/CEG-fm.png">
    <link rel="stylesheet" href="./assets/icon/fontAwesome/all.min.css">
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
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 3rem;
            text-align: center;
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
        
        .logo {
            width: 120px;
            height: 120px;
            margin-bottom: 2rem;
        }
        
        h1 {
            color: #667eea;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .tagline {
            color: #6b7280;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        p {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
        }
        
        .buttons {
            display: flex;
            gap: 1rem;
            flex-direction: column;
        }
        
        .btn {
            padding: 1rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #f3f4f6;
        }
        
        .btn-test {
            border-color: #f59e0b;
            color: #f59e0b;
            font-size: 0.95rem;
        }
        
        .btn-test:hover {
            background: #fef3c7;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="./assets/images/icone/CEG-fm.png" alt="Logo CEG FM" class="logo">
        <h1>CEG FM</h1>
        <p class="tagline">Excellence • Discipline • Réussite</p>
        <p>Plateforme de gestion scolaire</p>
        
        <div class="buttons">
            <a href="../app/auth/Sign-In.php" class="btn btn-primary">
                <i class="fa-solid fa-right-to-bracket"></i>
                Se connecter
            </a>
            <a href="../app/auth/Sign-Up.php" class="btn btn-secondary">
                <i class="fa-solid fa-user-plus"></i>
                S'inscrire
            </a>
            
            <?php if ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false): ?>
                <a href="../tests/test-login.php" class="btn btn-secondary btn-test">
                    <i class="fa-solid fa-flask"></i>
                    Mode Test (dev)
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>