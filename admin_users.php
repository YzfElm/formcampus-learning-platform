<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_admin();

// Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $uid = (int)$_GET['id'];
    if ($_GET['action']==='suspend') {
        $pdo->prepare("UPDATE users SET statut='SUSPENDU' WHERE id=:id")->execute([':id'=>$uid]);
        log_action($pdo, $_SESSION['user_id'], 'USER_SUSPENDED', "Suspension user #$uid");
        $_SESSION['flash_success'] = "Utilisateur suspendu.";
    } elseif ($_GET['action']==='activate') {
        $pdo->prepare("UPDATE users SET statut='ACTIF' WHERE id=:id")->execute([':id'=>$uid]);
        log_action($pdo, $_SESSION['user_id'], 'USER_ACTIVATED', "Réactivation user #$uid");
        $_SESSION['flash_success'] = "Utilisateur réactivé.";
    } elseif ($_GET['action']==='delete' && $uid !== $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id=:id")->execute([':id'=>$uid]);
        log_action($pdo, $_SESSION['user_id'], 'USER_DELETED', "Suppression user #$uid");
        $_SESSION['flash_success'] = "Utilisateur supprimé.";
    }
    header("Location: admin_users.php"); exit;
}

$users = $pdo->query(
    "SELECT u.*, (SELECT COUNT(*) FROM inscriptions i WHERE i.user_id=u.id) AS nb_inscriptions
     FROM users u ORDER BY u.created_at DESC"
)->fetchAll();

include 'includes/header.php';
?>

<div class="admin-layout">
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="admin-content">

        <div class="page-header">
            <h1>Gestion des utilisateurs</h1>
            <span style="color:var(--text-muted);"><?= count($users) ?> utilisateur(s)</span>
        </div>

        <!-- Recherche live -->
        <div class="filter-bar">
            <input type="text" id="adminSearch" placeholder="🔍 Rechercher un utilisateur...">
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#</th><th>Nom</th><th>Email</th><th>Rôle</th>
                        <th>Formations</th><th>Inscrit le</th><th>Statut</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.75rem;">
                                <?php if (!empty($u['avatar'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($u['avatar']) ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" alt="">
                                <?php else: ?>
                                    <div style="width:32px;height:32px;border-radius:50%;background:var(--gradient-primary);display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;color:#fff;">
                                        <?= strtoupper(substr($u['prenom'],0,1).substr($u['nom'],0,1)) ?>
                                    </div>
                                <?php endif; ?>
                                <span><?= htmlspecialchars($u['prenom'].' '.$u['nom']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="status-badge <?= $u['role']==='ADMIN'?'role-admin':'role-user' ?>"><?= $u['role'] ?></span></td>
                        <td><?= $u['nb_inscriptions'] ?></td>
                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <span class="status-badge <?= $u['statut']==='ACTIF'?'status-actif':'status-suspendu' ?>">
                                <?= $u['statut'] ?>
                            </span>
                        </td>
                        <td style="white-space:nowrap;">
                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <?php if ($u['statut']==='ACTIF'): ?>
                                    <a href="admin_users.php?action=suspend&id=<?= $u['id'] ?>" class="btn btn-small" style="background:rgba(239,68,68,.2);color:#f87171;">Suspendre</a>
                                <?php else: ?>
                                    <a href="admin_users.php?action=activate&id=<?= $u['id'] ?>" class="btn btn-small" style="background:rgba(34,197,94,.2);color:#86efac;">Réactiver</a>
                                <?php endif; ?>
                                <a href="admin_users.php?action=delete&id=<?= $u['id'] ?>" class="btn btn-small btn-danger btn-delete" style="font-size:.8rem;">🗑</a>
                            <?php else: ?>
                                <span style="color:var(--text-muted);font-size:.8rem;">(vous)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
