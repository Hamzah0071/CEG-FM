<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireRole(array $roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
        die('Accès refusé');
    }
}
