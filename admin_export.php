<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_admin();

$format       = $_GET['format']       ?? 'csv';
$formation_id = isset($_GET['formation_id']) ? (int)$_GET['formation_id'] : 0;

$params = [];
$where  = [];
if ($formation_id > 0) {
    $where[]       = "i.formation_id = :fid";
    $params[':fid'] = $formation_id;
}
$whereClause = $where ? 'WHERE '.implode(' AND ',$where) : '';

$sql = "SELECT i.created_at, u.nom, u.prenom, u.email,
               f.titre AS formation, f.categorie, i.progression, i.commentaire
        FROM inscriptions i
        JOIN users u ON u.id=i.user_id
        JOIN formations f ON f.id=i.formation_id
        $whereClause
        ORDER BY i.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$date = date('Y-m-d');

// ── Export CSV ─────────────────────────────────────────────
if ($format === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=\"inscriptions_$date.csv\"");

    $out = fopen('php://output', 'w');
    // BOM pour Excel
    fputs($out, "\xEF\xBB\xBF");
    fputcsv($out, ['Date','Nom','Prénom','Email','Formation','Catégorie','Progression (%)','Commentaire'], ';');
    foreach ($data as $row) {
        fputcsv($out, [
            date('d/m/Y', strtotime($row['created_at'])),
            $row['nom'], $row['prenom'], $row['email'],
            $row['formation'], $row['categorie'],
            $row['progression'], $row['commentaire'] ?? ''
        ], ';');
    }
    fclose($out);
    exit;
}

// ── Export PDF (HTML → navigateur) ────────────────────────
if ($format === 'pdf') {
    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Inscriptions FormCampus — <?= $date ?></title>
<style>
    * { box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #222; margin: 20px; }
    h1   { font-size: 20px; margin-bottom: 4px; }
    p    { color: #666; margin-bottom: 16px; }
    table{ width: 100%; border-collapse: collapse; }
    th   { background: #4f46e5; color: #fff; padding: 8px; text-align: left; font-size: 11px; }
    td   { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; }
    tr:nth-child(even) td { background: #f9f8ff; }
    .prog { display: inline-block; background: #e0e7ff; color: #4f46e5; padding: 2px 7px; border-radius: 10px; font-weight: 700; }
    @media print { button { display: none; } }
</style>
</head>
<body>
<h1>📚 FormCampus — Liste des inscriptions</h1>
<p>Exporté le <?= date('d/m/Y à H:i') ?> · <?= count($data) ?> inscription(s)</p>
<button onclick="window.print()" style="margin-bottom:16px;padding:8px 20px;background:#4f46e5;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;">
    🖨️ Imprimer / Sauvegarder en PDF
</button>
<table>
    <thead>
        <tr>
            <th>Date</th><th>Nom</th><th>Prénom</th><th>Email</th>
            <th>Formation</th><th>Catégorie</th><th>Progression</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $row): ?>
        <tr>
            <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
            <td><?= htmlspecialchars($row['nom']) ?></td>
            <td><?= htmlspecialchars($row['prenom']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['formation']) ?></td>
            <td><?= htmlspecialchars($row['categorie']) ?></td>
            <td><span class="prog"><?= $row['progression'] ?>%</span></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
<?php
    exit;
}

header("Location: admin_inscriptions.php");
exit;
?>
