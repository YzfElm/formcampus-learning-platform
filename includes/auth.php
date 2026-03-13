<?php
// includes/auth.php
// ── Fonctions d'authentification ──────────────────────────

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirige vers login.php si l'utilisateur n'est pas connecté.
 */
function require_login(): void {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Redirige vers index.php si l'utilisateur n'est pas ADMIN.
 */
function require_admin(): void {
    require_login();
    if ($_SESSION['user_role'] !== 'ADMIN') {
        header("Location: index.php");
        exit;
    }
}

/**
 * Retourne true si un utilisateur est connecté.
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Retourne true si l'utilisateur connecté est ADMIN.
 */
function is_admin(): bool {
    return is_logged_in() && $_SESSION['user_role'] === 'ADMIN';
}
?>
