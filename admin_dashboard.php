<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_admin();
include 'includes/header.php';

$total_users       = $pdo->query("SELECT COUNT(*) FROM users WHERE role='USER'")->fetchColumn();
$total_formations  = $pdo->query("SELECT COUNT(*) FROM formations WHERE actif=1")->fetchColumn();
$total_inscriptions= $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn();
$taux              = $total_users > 0 ? round(($total_inscriptions / $total_users) * 100) : 0;

$recent_inscriptions = $pdo->query(
    "SELECT i.*, CONCAT(u.prenom,' ',u.nom) AS user_name, f.titre AS formation_titre
     FROM inscriptions i
     JOIN users u ON u.id=i.user_id
     JOIN formations f ON f.id=i.formation_id
     ORDER BY i.created_at DESC LIMIT 5"
)->fetchAll();

$recent_logs = $pdo->query(
    "SELECT l.*, CONCAT(u.prenom,' ',u.nom) AS user_name
     FROM activity_logs l
     LEFT JOIN users u ON u.id=l.user_id
     ORDER BY l.created_at DESC LIMIT 5"
)->fetchAll();
?>

<div class="admin-layout">

    <?php include 'includes/admin_sidebar.php'; ?>

    <div class="admin-content">
        <div class="page-header">
            <h1>Tableau de bord</h1>
            <span style="color:var(--text-muted);font-size:.9rem;">📅 <?= date('d/m/Y H:i') ?></span>
        </div>

        <!-- Stats -->
        <div class="stats-grid" style="margin-bottom:2.5rem;">
            <div class="stat-card">
                <h3><?= $total_users ?></h3>
                <p>👥 Utilisateurs</p>
            </div>
            <div class="stat-card">
                <h3><?= $total_formations ?></h3>
                <p>📚 Formations actives</p>
            </div>
            <div class="stat-card">
                <h3><?= $total_inscriptions ?></h3>
                <p>✅ Inscriptions totales</p>
            </div>
            <div class="stat-card">
                <h3><?= $taux ?>%</h3>
                <p>📈 Taux d'inscription</p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;" class="dash-grid">

            <!-- Dernières inscriptions -->
            <div>
                <h2 style="font-size:1.2rem;margin-bottom:1rem;">Dernières inscriptions</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Date</th><th>Apprenant</th><th>Formation</th></tr></thead>
                        <tbody>
                            <?php foreach ($recent_inscriptions as $i): ?>
                            <tr>
                                <td><?= date('d/m H:i', strtotime($i['created_at'])) ?></td>
                                <td><?= htmlspecialchars($i['user_name']) ?></td>
                                <td><?= htmlspecialchars(substr($i['formation_titre'],0,25)) ?>...</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="admin_inscriptions.php" class="btn btn-small btn-outline" style="margin-top:1rem;display:inline-block;">Voir tout →</a>
            </div>

            <!-- Logs récents -->
            <div>
                <h2 style="font-size:1.2rem;margin-bottom:1rem;">Activité récente</h2>
                <div style="display:flex;flex-direction:column;gap:.75rem;">
                    <?php foreach ($recent_logs as $log): ?>
                    <div style="display:flex;gap:.75rem;align-items:flex-start;padding:.75rem;background:var(--card-bg);border:1px solid var(--border-color);border-radius:10px;">
                        <span style="width:8px;height:8px;border-radius:50%;background:var(--accent-color);margin-top:5px;flex-shrink:0;"></span>
                        <div>
                            <p style="margin:0;font-size:.85rem;font-weight:600;color:var(--accent-color);"><?= htmlspecialchars($log['action']) ?></p>
                            <p style="margin:.2rem 0 0;font-size:.8rem;color:var(--text-muted);">
                                <?= htmlspecialchars($log['details'] ?? '') ?>
                                <?= $log['user_name'] ? '— '.$log['user_name'] : '' ?>
                            </p>
                            <p style="margin:.2rem 0 0;font-size:.75rem;color:var(--text-muted);"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="admin_logs.php" class="btn btn-small btn-outline" style="margin-top:1rem;display:inline-block;">Voir tout →</a>
            </div>
        </div>
    </div>
</div>

<style>@media(max-width:700px){.dash-grid{grid-template-columns:1fr!important;}}</style>

<?php include 'includes/footer.php'; ?>
