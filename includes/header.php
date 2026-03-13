<?php
// includes/header.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

// Nombre de notifs non lues
$unread_notifs = 0;
if (is_logged_in()) {
    $unread_notifs = count_unread_notifications($pdo, $_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form'Campus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <nav>
        <a href="index.php" class="logo">Form'Campus</a>

        <!-- Hamburger (mobile) -->
        <button class="nav-toggle" id="navToggle" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>

        <ul id="navMenu">
            <li><a href="index.php"       class="<?= ($current_page==='index.php')      ? 'active':'' ?>">Accueil</a></li>
            <li><a href="formations.php"  class="<?= in_array($current_page,['formations.php','detail.php']) ? 'active':'' ?>">Formations</a></li>

            <?php if (is_logged_in()): ?>
                <li><a href="my_learning.php" class="<?= ($current_page==='my_learning.php') ? 'active':'' ?>">Mon Espace</a></li>

                <!-- Notifications -->
                <li style="position:relative;">
                    <a href="notifications.php" class="notif-link <?= ($current_page==='notifications.php') ? 'active':'' ?>">
                        🔔
                        <?php if ($unread_notifs > 0): ?>
                            <span class="notif-badge"><?= $unread_notifs ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Dropdown utilisateur -->
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn <?= (strpos($current_page,'admin')!==false || $current_page==='profile.php') ? 'active':'' ?>">
                        👤 <?= htmlspecialchars($_SESSION['user_prenom']) ?> ▾
                    </a>
                    <div class="dropdown-content">
                        <a href="profile.php">Mon Profil</a>
                        <?php if (is_admin()): ?>
                            <a href="admin_dashboard.php">Dashboard Admin</a>
                            <a href="admin_formations.php">Formations</a>
                            <a href="admin_inscriptions.php">Inscriptions</a>
                            <a href="admin_users.php">Utilisateurs</a>
                            <a href="admin_logs.php">Logs</a>
                        <?php endif; ?>
                        <a href="logout.php" style="color:#ff6b6b;">Déconnexion</a>
                    </div>
                </li>

            <?php else: ?>
                <li><a href="inscription_publique.php" class="<?= ($current_page==='inscription_publique.php') ? 'active':'' ?>">S'inscrire</a></li>
                <li><a href="login.php" class="btn btn-small <?= ($current_page==='login.php') ? 'active':'' ?>">Connexion</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<!-- Toast container -->
<div id="toastContainer"></div>

<?php
// Afficher les messages flash (succès / erreur)
if (isset($_SESSION['flash_success'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => showToast('success', <?= json_encode($_SESSION['flash_success']) ?>));
    </script>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => showToast('error', <?= json_encode($_SESSION['flash_error']) ?>));
    </script>
<?php unset($_SESSION['flash_error']); endif; ?>

<main class="container">
