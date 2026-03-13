<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];
$error = $success = '';

// Charger le profil actuel
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=:id");
$stmt->execute([':id'=>$user_id]);
$user = $stmt->fetch();

// ── Mise à jour du profil ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_profile'])) {
    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email']  ?? '');

    if (empty($nom) || empty($prenom) || empty($email)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } else {
        // Vérifier doublon email (autre utilisateur)
        $chk = $pdo->prepare("SELECT id FROM users WHERE email=:e AND id!=:id");
        $chk->execute([':e'=>$email,':id'=>$user_id]);
        if ($chk->fetchColumn()) {
            $error = "Cet email est déjà utilisé.";
        } else {
            // Upload avatar
            $avatar = $user['avatar'];
            if (!empty($_FILES['avatar']['name'])) {
                $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                    $new_name = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], 'uploads/' . $new_name)) {
                        $avatar = $new_name;
                    }
                }
            }

            $upd = $pdo->prepare("UPDATE users SET nom=:nom,prenom=:prenom,email=:email,avatar=:avatar WHERE id=:id");
            $upd->execute([':nom'=>$nom,':prenom'=>$prenom,':email'=>$email,':avatar'=>$avatar,':id'=>$user_id]);

            // Mettre à jour la session
            $_SESSION['user_nom']    = $nom;
            $_SESSION['user_prenom'] = $prenom;
            $_SESSION['user_email']  = $email;
            $_SESSION['user_avatar'] = $avatar;

            log_action($pdo, $user_id, 'PROFILE_UPDATED', 'Mise à jour du profil');
            $success = "Profil mis à jour avec succès.";
            // Recharger
            $stmt->execute([':id'=>$user_id]);
            $user = $stmt->fetch();
        }
    }
}

// ── Changement de mot de passe ─────────────────────────────
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!password_verify($current, $user['password'])) {
        $error = "Mot de passe actuel incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
    } elseif ($new !== $confirm) {
        $error = "Les nouveaux mots de passe ne correspondent pas.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password=:p WHERE id=:id")->execute([':p'=>$hashed,':id'=>$user_id]);
        log_action($pdo, $user_id, 'PASSWORD_CHANGED', '');
        $success = "Mot de passe modifié avec succès.";
    }
}

include 'includes/header.php';
?>

<h1>Mon Profil</h1>

<?php if ($error):   ?><div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>

<div class="profile-grid">

    <!-- Colonne gauche : avatar -->
    <div class="stat-card" style="text-align:center;">
        <?php if (!empty($user['avatar'])): ?>
            <img id="avatarPreview" src="uploads/<?= htmlspecialchars($user['avatar']) ?>" class="avatar-preview" alt="Avatar">
        <?php else: ?>
            <div class="avatar-placeholder" id="avatarPlaceholder">
                <?= strtoupper(substr($user['prenom'],0,1) . substr($user['nom'],0,1)) ?>
            </div>
            <img id="avatarPreview" src="" class="avatar-preview" alt="Avatar" style="display:none;">
        <?php endif; ?>

        <h3 style="margin:.75rem 0 .25rem;"><?= htmlspecialchars($user['prenom'].' '.$user['nom']) ?></h3>
        <span class="status-badge <?= $user['role']==='ADMIN'?'role-admin':'role-user' ?>"><?= $user['role'] ?></span>
        <p style="color:var(--text-muted);font-size:.85rem;margin-top:.75rem;">
            Membre depuis le <?= date('d/m/Y', strtotime($user['created_at'])) ?>
        </p>

        <?php
        $nb_formations = $pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE user_id=:id");
        $nb_formations->execute([':id'=>$user_id]);
        ?>
        <p style="color:var(--accent-color);font-weight:700;font-size:1.5rem;margin:.5rem 0;"><?= $nb_formations->fetchColumn() ?></p>
        <p style="color:var(--text-muted);font-size:.85rem;margin:0;">formations suivies</p>
    </div>

    <!-- Colonne droite : formulaires -->
    <div style="display:flex;flex-direction:column;gap:2rem;">

        <!-- Infos personnelles -->
        <div class="stat-card" style="text-align:left;">
            <h2 style="margin-top:0;font-size:1.3rem;">Informations personnelles</h2>
            <form method="post" enctype="multipart/form-data">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label>Prénom</label>
                        <input type="text" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Nom</label>
                        <input type="text" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Photo de profil</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*">
                </div>
                <button type="submit" name="update_profile" class="btn">Enregistrer</button>
            </form>
        </div>

        <!-- Mot de passe -->
        <div class="stat-card" style="text-align:left;">
            <h2 style="margin-top:0;font-size:1.3rem;">Changer le mot de passe</h2>
            <form method="post">
                <div class="form-group">
                    <label>Mot de passe actuel</label>
                    <input type="password" name="current_password" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="new_password" required placeholder="Min. 6 caractères">
                </div>
                <div class="form-group">
                    <label>Confirmer le nouveau mot de passe</label>
                    <input type="password" name="confirm_password" required placeholder="••••••••">
                </div>
                <button type="submit" name="change_password" class="btn">Modifier le mot de passe</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
