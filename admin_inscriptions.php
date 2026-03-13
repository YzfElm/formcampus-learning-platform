<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_admin();

$formation_id = isset($_GET['formation_id']) ? (int)$_GET['formation_id'] : 0;

$params = [];
$where  = [];
if ($formation_id > 0) {
    $where[]               = "i.formation_id = :fid";
    $params[':fid'] = $formation_id;
}
$whereClause = $where ? 'WHERE '.implode(' AND ',$where) : '';

$sql = "SELECT i.*, CONCAT(u.prenom,' ',u.nom) AS user_name, u.email AS user_email,
               f.titre AS formation_titre, f.categorie
        FROM inscriptions i
        JOIN users u ON u.id = i.user_id
        JOIN formations f ON f.id = i.formation_id
        $whereClause
        ORDER BY i.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$inscriptions = $stmt->fetchAll();

$formations = $pdo->query("SELECT id, titre FROM formations WHERE actif=1 ORDER BY titre")->fetchAll();

include 'includes/header.php';
?>

<div class="admin-layout">
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="admin-content">

        <div class="page-header">
            <h1>Suivi des inscriptions</h1>
            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                <a href="admin_export.php?format=csv<?= $formation_id?'&formation_id='.$formation_id:'' ?>" class="btn btn-small btn-outline" id="exportCsvBtn">
                    📥 Export CSV
                </a>
                <a href="admin_export.php?format=pdf<?= $formation_id?'&formation_id='.$formation_id:'' ?>" class="btn btn-small">
                    📄 Export PDF
                </a>
            </div>
        </div>

        <!-- Filtre par formation -->
        <form method="get" action="admin_inscriptions.php" class="filter-bar">
            <select name="formation_id" onchange="this.form.submit()">
                <option value="0">Toutes les formations</option>
                <?php foreach ($formations as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= $formation_id===$f['id']?'selected':'' ?>>
                        <?= htmlspecialchars($f['titre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" id="adminSearch" placeholder="🔍 Rechercher dans les résultats...">
        </form>

        <p style="color:var(--text-muted);margin-bottom:1rem;"><?= count($inscriptions) ?> inscription(s)</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th><th>Apprenant</th><th>Email</th>
                        <th>Formation</th><th>Progression</th><th>Commentaire</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inscriptions as $i): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($i['created_at'])) ?></td>
                        <td style="font-weight:600;color:#fff;"><?= htmlspecialchars($i['user_name']) ?></td>
                        <td><?= htmlspecialchars($i['user_email']) ?></td>
                        <td>
                            <div>
                                <span style="color:#fff;font-weight:500;"><?= htmlspecialchars($i['formation_titre']) ?></span><br>
                                <span class="badge" style="font-size:.65rem;"><?= htmlspecialchars($i['categorie']) ?></span>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.5rem;min-width:120px;">
                                <div class="progress-wrap" style="flex:1;">
                                    <div class="progress-bar" data-value="<?= $i['progression'] ?>" style="width:0%;"></div>
                                </div>
                                <span style="font-size:.8rem;color:#fff;white-space:nowrap;"><?= $i['progression'] ?>%</span>
                            </div>
                        </td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;">
                            <?= htmlspecialchars($i['commentaire'] ?? '—') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
