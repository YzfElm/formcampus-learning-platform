<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_admin();

$logs = $pdo->query(
    "SELECT l.*, CONCAT(u.prenom,' ',u.nom) AS user_name, u.email AS user_email
     FROM activity_logs l
     LEFT JOIN users u ON u.id=l.user_id
     ORDER BY l.created_at DESC
     LIMIT 200"
)->fetchAll();

include 'includes/header.php';
?>

<div class="admin-layout">
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="admin-content">

        <div class="page-header">
            <h1>Logs d'activité</h1>
            <span style="color:var(--text-muted);">200 dernières actions</span>
        </div>

        <div class="filter-bar">
            <input type="text" id="adminSearch" placeholder="🔍 Rechercher dans les logs...">
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Date</th><th>Action</th><th>Utilisateur</th><th>Détails</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td style="white-space:nowrap;"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></td>
                        <td>
                            <span style="font-family:monospace;font-size:.8rem;color:var(--accent-color);background:rgba(6,182,212,.1);padding:3px 8px;border-radius:4px;">
                                <?= htmlspecialchars($log['action']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($log['user_name']): ?>
                                <span style="color:#fff;"><?= htmlspecialchars($log['user_name']) ?></span>
                                <br><span style="font-size:.75rem;color:var(--text-muted);"><?= htmlspecialchars($log['user_email']??'') ?></span>
                            <?php else: ?>
                                <span style="color:var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="max-width:300px;font-size:.85rem;"><?= htmlspecialchars($log['details']??'') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
