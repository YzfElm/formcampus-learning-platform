<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();
include 'includes/header.php';

$inscriptions = $pdo->prepare(
    "SELECT i.*, f.titre, f.categorie, f.description, f.duree, f.prix, f.image, f.formateur
     FROM inscriptions i
     JOIN formations f ON i.formation_id = f.id
     WHERE i.user_id = :uid
     ORDER BY i.created_at DESC"
);
$inscriptions->execute([':uid' => $_SESSION['user_id']]);
$inscriptions = $inscriptions->fetchAll();
?>

<div class="page-header">
    <div>
        <h1>Mon Espace Apprenant</h1>
        <p style="color:var(--text-muted);margin:0;"><?= count($inscriptions) ?> formation(s) en cours</p>
    </div>
    <a href="formations.php" class="btn btn-small">+ Découvrir des formations</a>
</div>

<?php if (count($inscriptions) === 0): ?>
    <div style="text-align:center;padding:5rem 0;border:1px solid var(--border-color);border-radius:20px;">
        <p style="font-size:3rem;">📚</p>
        <p style="font-size:1.3rem;font-weight:700;color:#fff;">Aucune formation en cours</p>
        <p style="color:var(--text-muted);">Explorez notre catalogue et commencez à apprendre !</p>
        <a href="formations.php" class="btn" style="margin-top:1.5rem;">Voir les formations</a>
    </div>

<?php else: ?>
    <div style="display:flex;flex-direction:column;gap:1.5rem;">
        <?php foreach ($inscriptions as $i): ?>
        <div class="learning-card">
            <div style="display:grid;grid-template-columns:100px 1fr;gap:1.5rem;align-items:start;" class="learning-inner">

                <!-- Image -->
                <div style="height:80px;border-radius:10px;overflow:hidden;background:#111;">
                    <?php if (!empty($i['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($i['image']) ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:rgba(255,255,255,.2);background:linear-gradient(45deg,#1a1a1a,#2a2a2a);">
                            <?= substr($i['titre'],0,1) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Infos -->
                <div>
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                        <div>
                            <span class="badge" style="margin-bottom:.5rem;"><?= htmlspecialchars($i['categorie']) ?></span>
                            <h3 style="margin:0 0 .25rem;"><?= htmlspecialchars($i['titre']) ?></h3>
                            <?php if (!empty($i['formateur'])): ?>
                                <p style="font-size:.85rem;color:var(--text-muted);margin:0;">Par <?= htmlspecialchars($i['formateur']) ?></p>
                            <?php endif; ?>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-size:1.5rem;font-weight:800;color:var(--accent-color);"><?= $i['progression'] ?>%</span>
                            <p style="font-size:.75rem;color:var(--text-muted);margin:0;">complété</p>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div class="progress-wrap" style="margin:1rem 0 .75rem;">
                        <div class="progress-bar" data-value="<?= $i['progression'] ?>" style="width:0%;"></div>
                    </div>

                    <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
                        <span style="font-size:.85rem;color:var(--text-muted);">⏱ <?= htmlspecialchars($i['duree']) ?></span>
                        <span style="font-size:.85rem;color:var(--text-muted);">📅 Inscrit le <?= date('d/m/Y', strtotime($i['created_at'])) ?></span>
                        <a href="detail.php?id=<?= $i['formation_id'] ?>" style="font-size:.85rem;color:var(--accent-color);text-decoration:none;margin-left:auto;">
                            Voir la formation →
                        </a>
                    </div>

                    <!-- Mise à jour progression -->
                    <form method="post" action="update_progression.php" style="display:flex;align-items:center;gap:.75rem;margin-top:1rem;">
                        <input type="hidden" name="inscription_id" value="<?= $i['id'] ?>">
                        <input type="range" name="progression" min="0" max="100" value="<?= $i['progression'] ?>"
                               style="flex:1;width:auto;padding:0;background:none;border:none;"
                               oninput="this.nextElementSibling.textContent=this.value+'%'">
                        <span style="min-width:40px;color:#fff;font-weight:600;"><?= $i['progression'] ?>%</span>
                        <button type="submit" class="btn btn-small" style="padding:6px 14px;">Mettre à jour</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
@media(max-width:600px){.learning-inner{grid-template-columns:1fr!important;}}
</style>

<?php include 'includes/footer.php'; ?>
