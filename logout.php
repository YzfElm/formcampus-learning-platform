<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
if (is_logged_in()) {
    log_action($pdo, $_SESSION['user_id'], 'USER_LOGOUT', 'Déconnexion');
}
session_destroy();
header("Location: login.php");
exit;
?>
