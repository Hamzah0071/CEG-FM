<!-- vérifie les sessions / rôles --><?php
// auth.php
session_start();

/**
 * Vérifie que l'utilisateur est connecté
 */
if (!isset($_SESSION['user_id'])) {
    header("Location: /auth/Sign-In.php");
    exit;
}

/**
 * Vérifie le rôle si demandé
 * Utilisation :
 *   require 'auth.php'; // juste connecté
 *   require 'auth.php'; checkRole('admin');
 */
function checkRole(string $role): void
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        http_response_code(403);
        die("Accès refusé");
    }
}
