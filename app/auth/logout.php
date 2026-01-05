<?php
// app/auth/logout.php

session_start();

require_once __DIR__ . '/../config/db.php';

// Logger la déconnexion
if (isset($_SESSION['user_id'])) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $pdo->prepare("
            INSERT INTO logs_audit (utilisateur_id, table_name, record_id, action, ip_address, user_agent)
            VALUES (?, 'utilisateurs', ?, 'LOGOUT', ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $ip, $user_agent]);
    } catch (Exception $e) {
        error_log("Erreur logout: " . $e->getMessage());
    }
}

// Détruire la session
session_unset();
session_destroy();

// Supprimer le cookie "remember me" si présent
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Rediriger vers la page de connexion
header("Location: Sign-In.php");
exit;