<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
include 'includes/header.php';

$sql = "SELECT * FROM formations WHERE actif=1 ORDER BY RAND() LIMIT 5";
$carousel_formations = $pdo->query($sql)->fetchAll();
?>

<!-- Hero Section -->
<section class="hero">
    <h1>Form'Campus</h1>
    <p>La nouvelle ère de l'apprentissage des technologies numériques.</p>
    <a href="#carousel" class="btn btn-small" style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);">
        Débuter l'Expérience ↓
    </a>
</section>

<!-- Stats rapides -->
<?php
$nb_formations   = $pdo->query("SELECT COUNT(*) FROM formations WHERE actif=1")->fetchColumn();
$nb_users        = $pdo->query("SELECT COUNT(*) FROM users WHERE role='USER'")->fetchColumn();
$nb_inscriptions = $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn();
?>
<section style="padding:3rem 0;background:rgba(255,255,255,.02);border-top:1px solid var(--border-color);border-bottom:1px solid var(--border-color);">
    <div class="stats-grid" style="max-width:800px;margin:0 auto;padding:0 2rem;">
        <div class="stat-card">
            <h3><?= $nb_users ?></h3>
            <p>Apprenants inscrits</p>
        </div>
        <div class="stat-card">
            <h3><?= $nb_formations ?></h3>
            <p>Formations actives</p>
        </div>
        <div class="stat-card">
            <h3><?= $nb_inscriptions ?></h3>
            <p>Inscriptions totales</p>
        </div>
    </div>
</section>

<!-- 3D Carousel Section -->
<section id="carousel" style="text-align:center;position:relative;margin-top:4rem;">
    <h2 style="position:relative;z-index:2;">Nos Modules</h2>

    <?php if (count($carousel_formations) > 0): ?>
    <div class="carousel-section">
        <div class="carousel-container">
            <?php foreach ($carousel_formations as $index => $f): ?>
            <div class="carousel-item" data-index="<?= $index ?>">
                <div class="close-btn" style="display:none;">&times;</div>

                <div class="card-image">
                    <?php if (!empty($f['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($f['image']) ?>" alt="<?= htmlspecialchars($f['titre']) ?>">
                    <?php else: ?>
                        <div class="placeholder-img"><span><?= substr($f['titre'],0,1) ?></span></div>
                    <?php endif; ?>
                </div>

                <div class="content">
                    <h3><?= htmlspecialchars($f['titre']) ?></h3>
                    <div class="carousel-tags">
                        <?php foreach (explode(' ', $f['categorie']) as $t): ?>
                            <span><?= htmlspecialchars($t) ?></span>
                        <?php endforeach; ?>
                        <span>PRO</span>
                    </div>
                    <p><?= htmlspecialchars(substr($f['description'], 0, 80)) ?>...</p>
                    <button class="btn btn-small btn-explore">EXPLORER</button>
                </div>

                <div class="details-content">
                    <h2><?= htmlspecialchars($f['titre']) ?></h2>
                    <p style="font-size:1.5rem;color:var(--accent-color);font-weight:bold;">
                        <?= $f['prix'] == 0 ? 'Gratuit' : number_format($f['prix'],2,',',' ') . ' €' ?>
                    </p>
                    <p style="font-size:1.1rem;color:#ccc;margin-top:1.5rem;">
                        <?= nl2br(htmlspecialchars($f['description'])) ?>
                    </p>
                    <ul>
                        <li>Durée : <?= htmlspecialchars($f['duree']) ?></li>
                        <?php if (!empty($f['formateur'])): ?>
                            <li>Formateur : <?= htmlspecialchars($f['formateur']) ?></li>
                        <?php endif; ?>
                        <li>Certification incluse</li>
                        <li>Accès à vie</li>
                    </ul>
                    <a href="detail.php?id=<?= $f['id'] ?>" class="btn" style="margin-top:2rem;font-size:1.1rem;">
                        Voir la formation →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
        <p style="color:var(--text-muted);">Aucune formation disponible pour le moment.</p>
    <?php endif; ?>
</section>

<!-- Nos formations récentes -->
<section style="margin-top:5rem;">
    <h2 style="text-align:center;">Dernières formations</h2>
    <?php
    $recentes = $pdo->query("SELECT * FROM formations WHERE actif=1 ORDER BY created_at DESC LIMIT 3")->fetchAll();
    ?>
    <div class="formations-grid">
        <?php foreach ($recentes as $f): ?>
        <div class="formation-card" data-category="<?= htmlspecialchars($f['categorie']) ?>">
            <div class="card-image">
                <?php if (!empty($f['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($f['image']) ?>" alt="<?= htmlspecialchars($f['titre']) ?>">
                <?php else: ?>
                    <div class="placeholder-img"><span><?= substr($f['titre'],0,1) ?></span></div>
                <?php endif; ?>
            </div>
            <div class="card-content">
                <span class="badge"><?= htmlspecialchars($f['categorie']) ?></span>
                <h3><?= htmlspecialchars($f['titre']) ?></h3>
                <p class="description"><?= htmlspecialchars($f['description']) ?></p>
                <div class="meta-info">
                    <span class="price"><?= $f['prix']==0 ? 'Gratuit' : number_format($f['prix'],2,',',' ').' €' ?></span>
                    <span>⏱ <?= htmlspecialchars($f['duree']) ?></span>
                </div>
                <a href="detail.php?id=<?= $f['id'] ?>" class="btn btn-block">Voir détails</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:2rem;">
        <a href="formations.php" class="btn">Voir toutes les formations →</a>
    </div>
</section>

<!-- Social section -->
<?php
$social_links = [
    ['name'=>'Instagram','icon'=>'<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>','stats'=>'Followers: 150k','link'=>'#','btn_text'=>'Follow me','color'=>'#E1306C'],
    ['name'=>'LinkedIn','icon'=>'<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>','stats'=>'Connections: 5k+','link'=>'#','btn_text'=>'Connect','color'=>'#0077b5'],
    ['name'=>'GitHub','icon'=>'<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>','stats'=>'Stars: 12k','link'=>'#','btn_text'=>'Check Code','color'=>'#ffffff'],
];
?>
<section class="social-section">
    <div class="container">
        <h2 style="text-align:center;margin-bottom:3rem;">Connectons-nous</h2>
        <div class="social-grid">
            <?php foreach ($social_links as $s): ?>
            <div class="social-card" style="--card-hover-color:<?= $s['color'] ?>;">
                <div class="icon-wrapper" style="color:<?= $s['color'] ?>;"><?= $s['icon'] ?></div>
                <h3 class="social-name"><?= $s['name'] ?></h3>
                <p class="social-stats"><?= $s['stats'] ?></p>
                <a href="<?= $s['link'] ?>" class="btn-social">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:8px;"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                    <?= $s['btn_text'] ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
