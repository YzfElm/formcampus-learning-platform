<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

// Marquer une notification comme lue (AJAX)
if (isset($_GET['mark_read'])) {
    $nid = (int)$_GET['mark_read'];
    $pdo->prepare("UPDATE notifications SET lu=1 WHERE id=:id AND user_id=:uid")->execute([':id'=>$nid,':uid'=>$user_id]);
    exit;
}

// Tout marquer comme lu
if (isset($_POST['mark_all_read'])) {
    $pdo->prepare("UPDATE notifications SET lu=1 WHERE user_id=:uid")->execute([':uid'=>$user_id]);
    $_SESSION['flash_success'] = "Toutes les notifications ont été marquées comme lues.";
    header("Location: notifications.php"); exit;
}

$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=:uid ORDER BY created_at DESC");
$notifs->execute([':uid'=>$user_id]);
$notifs = $notifs->fetchAll();

$unread_count = count(array_filter($notifs, fn($n) => !$n['lu']));

include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Notifications</h1>
        <p style="color:var(--text-muted);margin:0;"><?= $unread_count ?> non lue(s)</p>
    </div>
    <?php if ($unread_count > 0): ?>
        <form method="post">
            <button type="submit" name="mark_all_read" class="btn btn-small btn-outline">
                ✅ Tout marquer comme lu
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (count($notifs) === 0): ?>
    <div style="text-align:center;padding:4rem;border:1px solid var(--border-color);border-radius:16px;">
        <p style="font-size:3rem;">🔔</p>
        <p style="color:var(--text-muted);">Aucune notification pour le moment.</p>
    </div>
<?php else: ?>
    <div style="display:flex;flex-direction:column;gap:.75rem;">
        <?php foreach ($notifs as $n): ?>
        <div class="notif-item <?= !$n['lu']?'unread':'' ?>" data-id="<?= $n['id'] ?>">
            <div class="notif-dot" style="<?= $n['lu']?'background:var(--text-muted)':'' ?>"></div>
            <div style="flex:1;">
                <p style="margin:0;font-weight:600;color:#fff;"><?= htmlspecialchars($n['titre']) ?></p>
                <p style="margin:.25rem 0 0;color:var(--text-muted);font-size:.9rem;"><?= htmlspecialchars($n['message']) ?></p>
                <p style="margin:.5rem 0 0;font-size:.75rem;color:var(--text-muted);">
                    <?= date('d/m/Y à H:i', strtotime($n['created_at'])) ?>
                </p>
            </div>
            <?php if (!$n['lu']): ?>
                <span class="status-badge status-actif" style="font-size:.65rem;">Nouveau</span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
