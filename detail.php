<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/rating.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: formations.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM formations WHERE id=:id AND actif=1");
$stmt->execute([':id'=>$id]);
$formation = $stmt->fetch();
if (!$formation) { header("Location: formations.php"); exit; }

// Vérifier si déjà inscrit
$already_enrolled = false;
if (is_logged_in()) {
    $stmt2 = $pdo->prepare("SELECT id FROM inscriptions WHERE user_id=:uid AND formation_id=:fid");
    $stmt2->execute([':uid'=>$_SESSION['user_id'],':fid'=>$id]);
    $already_enrolled = (bool)$stmt2->fetchColumn();
}

// Inscription via bouton
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['enroll'])) {
    if (!is_logged_in()) {
        header("Location: login.php"); exit;
    }
    if (!$already_enrolled) {
        $stmt3 = $pdo->prepare("INSERT INTO inscriptions (user_id, formation_id) VALUES (:uid,:fid)");
        $stmt3->execute([':uid'=>$_SESSION['user_id'],':fid'=>$id]);
        notifier($pdo, $_SESSION['user_id'], "Inscription confirmée ! 🎉", "Vous êtes inscrit à \"{$formation['titre']}\". Bonne formation !");
        log_action($pdo, $_SESSION['user_id'], 'USER_ENROLLED', "Inscription à \"{$formation['titre']}\"");
        $_SESSION['flash_success'] = "Inscription réussie à \"" . $formation['titre'] . "\" !";
        header("Location: my_learning.php"); exit;
    }
}

$nb_inscrits = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE formation_id=:fid");
$nb_inscrits->execute([':fid'=>$id]);
$nb_inscrits = $nb_inscrits->fetchColumn();

// Note de l'utilisateur sur cette formation
$user_note_detail = 0;
if (is_logged_in()) {
    $evalUser = $pdo->prepare("SELECT note FROM evaluations WHERE formation_id = :fid AND utilisateur_id = :uid");
    $evalUser->execute([':fid' => $id, ':uid' => (int)$_SESSION['user_id']]);
    $user_note_detail = (int)($evalUser->fetchColumn() ?: 0);
}

include 'includes/header.php';
?>

<div style="max-width:900px;margin:0 auto;">
    <a href="formations.php" style="color:var(--text-muted);text-decoration:none;font-size:.9rem;">← Retour aux formations</a>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:2rem;margin-top:2rem;" class="detail-grid">

        <!-- Contenu principal -->
        <div>
            <span class="badge" style="margin-bottom:1rem;"><?= htmlspecialchars($formation['categorie']) ?></span>
            <h1 style="font-size:2.5rem;margin:.5rem 0;"><?= htmlspecialchars($formation['titre']) ?></h1>
            <?php if (!empty($formation['formateur'])): ?>
                <p style="color:var(--text-muted);margin:.5rem 0;">👤 Par <?= htmlspecialchars($formation['formateur']) ?></p>
            <?php endif; ?>
            <p style="color:var(--text-muted);font-size:.85rem;">👥 <?= $nb_inscrits ?> inscrit(s)</p>
            <?php render_rating(
                $formation["id"],
                isset($formation["note_moyenne"]) ? (float)$formation["note_moyenne"] : null,
                (int)($formation["nb_evaluations"] ?? 0),
                $user_note_detail,
                true
            ); ?>

            <?php if (!empty($formation['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($formation['image']) ?>" alt="<?= htmlspecialchars($formation['titre']) ?>"
                     style="width:100%;border-radius:16px;margin:1.5rem 0;max-height:350px;object-fit:cover;">
            <?php endif; ?>

            <h2 style="font-size:1.4rem;margin-top:2rem;">Description</h2>
            <p style="color:var(--text-muted);line-height:1.8;"><?= nl2br(htmlspecialchars($formation['description'])) ?></p>

            <h2 style="font-size:1.4rem;margin-top:2rem;">Ce que vous apprendrez</h2>
            <ul style="color:var(--text-muted);list-style:none;padding:0;display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
                <li>✅ Certification incluse</li>
                <li>✅ Accès à vie</li>
                <li>✅ Support du formateur</li>
                <li>✅ Projets pratiques</li>
            </ul>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="stat-card" style="text-align:left;position:sticky;top:100px;">
                <p style="font-size:2.2rem;font-weight:800;color:var(--accent-color);margin:0;">
                    <?= $formation['prix']==0?'Gratuit':number_format($formation['prix'],2,',',' ').' €' ?>
                </p>
                <div style="color:var(--text-muted);font-size:.9rem;margin:1rem 0;display:flex;flex-direction:column;gap:.5rem;">
                    <span>⏱ Durée : <strong style="color:#fff;"><?= htmlspecialchars($formation['duree']) ?></strong></span>
                    <span>📂 Catégorie : <strong style="color:#fff;"><?= htmlspecialchars($formation['categorie']) ?></strong></span>
                    <span>♾️ Accès à vie</span>
                    <span>🏆 Certification incluse</span>
                </div>

                <?php if ($already_enrolled): ?>
                    <div class="alert alert-success" style="margin-bottom:1rem;">✅ Vous êtes déjà inscrit</div>
                    <a href="my_learning.php" class="btn btn-block">Accéder à mon espace →</a>
                <?php elseif (is_logged_in()): ?>
                    <form method="post">
                        <input type="hidden" name="enroll" value="1">
                        <button type="submit" class="btn btn-block">S'inscrire maintenant</button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="btn btn-block">Se connecter pour s'inscrire</a>
                    <p style="text-align:center;font-size:.8rem;color:var(--text-muted);margin-top:.75rem;">
                        Pas de compte ? <a href="register.php" style="color:var(--accent-color);">Créer un compte</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
@media(max-width:700px){
    .detail-grid{grid-template-columns:1fr!important;}
}
</style>

<?php include 'includes/footer.php'; ?>
