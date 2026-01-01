<?php
function requireRole($roles)
{
    if (!isset($_SESSION['role'])) {
        header('Location: ../auth/login.php');
        exit;
    }

    if (!in_array($_SESSION['role'], (array)$roles)) {
        exit("Accès refusé");
    }
}
