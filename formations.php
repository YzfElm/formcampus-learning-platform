<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/rating.php';
include 'includes/header.php';

$search    = trim($_GET['search']    ?? '');
$categorie = trim($_GET['categorie'] ?? '');

$params = [];
$where  = ["f.actif = 1"];

if ($search !== '') {
    $where[]           = "(f.titre LIKE :search OR f.description LIKE :search OR f.formateur LIKE :search)";
    $params[':search'] = "%$search%";
}
if ($categorie !== '') {
    $where[]              = "f.categorie = :categorie";
    $params[':categorie'] = $categorie;
}

$whereClause = 'WHERE ' . implode(' AND ', $where);
$sql = "SELECT f.*,
            (SELECT COUNT(*) FROM inscriptions i WHERE i.formation_id = f.id) AS nb_inscrits
        FROM formations f
        $whereClause
        ORDER BY f.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$formations = $stmt->fetchAll();

// Récupérer la note de l'utilisateur connecté pour chaque formation
$user_notes = [];
if (is_logged_in()) {
    $uid = (int)$_SESSION['user_id'];
    $evalStmt = $pdo->prepare("SELECT formation_id, note FROM evaluations WHERE utilisateur_id = :uid");
    $evalStmt->execute([':uid' => $uid]);
    foreach ($evalStmt->fetchAll() as $row) {
        $user_notes[$row['formation_id']] = (int)$row['note'];
    }
}

// Catégories distinctes
$categories = $pdo->query("SELECT DISTINCT categorie FROM formations WHERE actif=1 ORDER BY categorie")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="page-header">
    <div>
        <h1>Nos Formations</h1>
        <p style="color:var(--text-muted);margin:0;"><?= count($formations) ?> formation(s) disponible(s)</p>
    </div>
    <?php if (is_logged_in()): ?>
        <a href="my_learning.php" class="btn btn-outline btn-small">📚 Mon Espace</a>
    <?php endif; ?>
</div>

<!-- Filtres -->
<form method="get" action="formations.php" class="filter-bar">
    <input type="text" name="search" placeholder="🔍 Rechercher une formation..." value="<?= htmlspecialchars($search) ?>">
    <select name="categorie" onchange="this.form.submit()">
        <option value="">Toutes les catégories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= $categorie===$cat?'selected':'' ?>>
                <?= htmlspecialchars($cat) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-small">Filtrer</button>
    <?php if ($search || $categorie): ?>
        <a href="formations.php" class="btn btn-small btn-outline">✕ Réinitialiser</a>
    <?php endif; ?>
</form>

<!-- Grille -->
<?php if (count($formations) === 0): ?>
    <div style="text-align:center;padding:4rem 0;color:var(--text-muted);">
        <p style="font-size:3rem;">🔍</p>
        <p style="font-size:1.2rem;">Aucune formation trouvée.</p>
        <a href="formations.php" class="btn btn-small btn-outline">Voir toutes les formations</a>
    </div>
<?php else: ?>
    <!-- Skeleton (affiché 0.5s) -->
    <div id="skeletonGrid" class="formations-grid">
        <?php for ($i=0;$i<3;$i++): ?>
        <div class="skeleton-card">
            <div class="skeleton skeleton-img"></div>
            <div style="padding:1.5rem;">
                <div class="skeleton skeleton-line short"></div>
                <div class="skeleton skeleton-line medium" style="margin-top:.75rem;height:18px;"></div>
                <div class="skeleton skeleton-line full" style="margin-top:.5rem;"></div>
                <div class="skeleton skeleton-line full"></div>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <div id="formationsGrid" class="formations-grid" style="display:none;">
        <?php foreach ($formations as $f): ?>
        <div class="formation-card" data-category="<?= htmlspecialchars($f['categorie']) ?>">
            <div class="card-image">
                <?php if (!empty($f['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($f['image']) ?>" alt="<?= htmlspecialchars($f['titre']) ?>" loading="lazy">
                <?php else: ?>
                    <div class="placeholder-img"><span><?= substr($f['titre'],0,1) ?></span></div>
                <?php endif; ?>
            </div>
            <div class="card-content">
                <span class="badge"><?= htmlspecialchars($f['categorie']) ?></span>
                <h3><?= htmlspecialchars($f['titre']) ?></h3>
                <?php if (!empty($f['formateur'])): ?>
                    <p style="font-size:.85rem;color:var(--text-muted);margin:0;">Par <?= htmlspecialchars($f['formateur']) ?></p>
                <?php endif; ?>
                <p class="description"><?= htmlspecialchars($f['description']) ?></p>
                <div class="meta-info">
                    <span class="price"><?= $f['prix']==0?'Gratuit':number_format($f['prix'],2,',',' ').' €' ?></span>
                    <span>⏱ <?= htmlspecialchars($f['duree']) ?></span>
                </div>
                <p style="font-size:.8rem;color:var(--text-muted);margin:0;">👥 <?= $f['nb_inscrits'] ?> inscrit(s)</p>
                <?php
                    $user_note = $user_notes[$f["id"]] ?? 0;
                    render_rating(
                        $f["id"],
                        isset($f["note_moyenne"]) ? (float)$f["note_moyenne"] : null,
                        (int)($f["nb_evaluations"] ?? 0),
                        $user_note,
                        true
                    );
                ?>
                <a href="detail.php?id=<?= $f['id'] ?>" class="btn btn-block">Voir détails</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        setTimeout(() => {
            document.getElementById('skeletonGrid').style.display = 'none';
            document.getElementById('formationsGrid').style.display = 'grid';
        }, 500);
    </script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
