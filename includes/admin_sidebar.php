<?php
// includes/admin_sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="admin-sidebar">
    <h3>Administration</h3>
    <a href="admin_dashboard.php"    class="<?= $current_page==='admin_dashboard.php'   ?'active':'' ?>">📊 Dashboard</a>
    <a href="admin_users.php"        class="<?= $current_page==='admin_users.php'        ?'active':'' ?>">👥 Utilisateurs</a>
    <a href="admin_formations.php"   class="<?= $current_page==='admin_formations.php'  ?'active':'' ?>">📚 Formations</a>
    <a href="admin_inscriptions.php" class="<?= $current_page==='admin_inscriptions.php'?'active':'' ?>">✅ Inscriptions</a>
    <a href="admin_logs.php"         class="<?= $current_page==='admin_logs.php'        ?'active':'' ?>">📋 Logs</a>
    <hr style="border-color:var(--border-color);margin:1rem 0;">
    <a href="index.php" style="color:var(--text-muted);">← Retour au site</a>
</div>
