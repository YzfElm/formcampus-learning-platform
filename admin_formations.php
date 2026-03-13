<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_admin();

// Supprimer (soft delete)
if (isset($_GET['action']) && $_GET['action']==='delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $titre_stmt = $pdo->prepare("SELECT titre FROM formations WHERE id=:id");
    $titre_stmt->execute([':id'=>$id]);
    $titre_del = $titre_stmt->fetchColumn();
    $pdo->prepare("UPDATE formations SET actif=0 WHERE id=:id")->execute([':id'=>$id]);
    log_action($pdo, $_SESSION['user_id'], 'FORMATION_DELETED', "Suppression de \"$titre_del\"");
    $_SESSION['flash_success'] = "Formation supprimée.";
    header("Location: admin_formations.php"); exit;
}

$formations = $pdo->query(
    "SELECT f.*, (SELECT COUNT(*) FROM inscriptions i WHERE i.formation_id=f.id) AS nb_inscrits
     FROM formations f WHERE f.actif=1 ORDER BY f.titre"
)->fetchAll();

include 'includes/header.php';
?>

<div class="admin-layout">
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="admin-content">

        <div class="page-header">
            <h1>Gestion des formations</h1>
            <a href="admin_formation_edit.php" class="btn btn-small">+ Ajouter une formation</a>
        </div>

        <div class="filter-bar">
            <input type="text" id="adminSearch" placeholder="🔍 Rechercher...">
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Image</th><th>Titre</th><th>Catégorie</th>
                        <th>Formateur</th><th>Prix</th><th>Inscrits</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($formations as $f): ?>
                    <tr>
                        <td>
                            <?php if (!empty($f['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($f['image']) ?>" style="width:50px;height:40px;object-fit:cover;border-radius:6px;" alt="">
                            <?php else: ?>
                                <div style="width:50px;height:40px;background:linear-gradient(45deg,#1a1a1a,#2a2a2a);border-radius:6px;display:flex;align-items:center;justify-content:center;font-weight:800;color:rgba(255,255,255,.2);">
                                    <?= substr($f['titre'],0,1) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:600;color:#fff;max-width:200px;">
                            <?= htmlspecialchars($f['titre']) ?>
                        </td>
                        <td><span class="badge" style="<?= ''?>"><?= htmlspecialchars($f['categorie']) ?></span></td>
                        <td><?= htmlspecialchars($f['formateur'] ?? '—') ?></td>
                        <td style="color:var(--accent-color);font-weight:600;">
                            <?= $f['prix']==0?'Gratuit':number_format($f['prix'],2,',',' ').' €' ?>
                        </td>
                        <td><?= $f['nb_inscrits'] ?></td>
                        <td style="white-space:nowrap;">
                            <a href="detail.php?id=<?= $f['id'] ?>" class="btn btn-small btn-outline" target="_blank">👁</a>
                            <a href="admin_formation_edit.php?id=<?= $f['id'] ?>" class="btn btn-small">✏️ Modifier</a>
                            <a href="admin_formations.php?action=delete&id=<?= $f['id'] ?>" class="btn btn-small btn-danger btn-delete">🗑</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
