<?php
// app/include/auth_check.php
// Protège les pages qui nécessitent une connexion

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    // Sauvegarder l'URL demandée pour redirection après connexion
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    header("Location: ../auth/Sign-In.php");
    exit;
}

// Vérifier l'activité (timeout de session après 30 min d'inactivité)
$timeout = 1800; // 30 minutes en secondes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: ../auth/Sign-In.php?timeout=1");
    exit;
}

$_SESSION['last_activity'] = time();

/**
 * Vérifier si l'utilisateur a le rôle requis
 * @param string|array $required_roles Rôle(s) requis
 * @return bool
 */
function check_role($required_roles) {
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }
    
    return in_array($_SESSION['role'], $required_roles);
}

/**
 * Rediriger si l'utilisateur n'a pas le bon rôle
 * @param string|array $required_roles Rôle(s) requis
 */
function require_role($required_roles) {
    if (!check_role($required_roles)) {
        header("HTTP/1.0 403 Forbidden");
        die("Accès interdit. Vous n'avez pas les permissions nécessaires.");
    }
}