<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
include 'includes/header.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nom          = trim($_POST['nom']          ?? '');
    $prenom       = trim($_POST['prenom']       ?? '');
    $email        = trim($_POST['email']        ?? '');
    $tel          = trim($_POST['tel']          ?? '');
    $id_formation = (int)($_POST['id_formation'] ?? 0);
    $commentaire  = trim($_POST['commentaire']  ?? '');

    if (empty($nom) || empty($prenom) || empty($email) || !$id_formation) {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "L'adresse email n'est pas valide.";
        $message_type = 'error';
    } else {
        try {
            // Créer un compte USER si pas existant, sinon récupérer l'ID
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email=:email");
            $stmt->execute([':email'=>$email]);
            $existing = $stmt->fetchColumn();

            if ($existing) {
                $user_id = $existing;
            } else {
                // Créer un compte avec mot de passe temporaire
                $temp_pass = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
                $ins = $pdo->prepare("INSERT INTO users (nom,prenom,email,password,role) VALUES(:nom,:prenom,:email,:pw,'USER')");
                $ins->execute([':nom'=>$nom,':prenom'=>$prenom,':email'=>$email,':pw'=>$temp_pass]);
                $user_id = $pdo->lastInsertId();
                notifier($pdo, $user_id, "Compte créé automatiquement", "Un compte a été créé pour vous. Connectez-vous via login.php pour définir votre mot de passe.");
            }

            // Vérifier doublon inscription
            $chk = $pdo->prepare("SELECT id FROM inscriptions WHERE user_id=:uid AND formation_id=:fid");
            $chk->execute([':uid'=>$user_id,':fid'=>$id_formation]);
            if ($chk->fetchColumn()) {
                $message = "Vous êtes déjà inscrit à cette formation.";
                $message_type = 'error';
            } else {
                $ins2 = $pdo->prepare("INSERT INTO inscriptions (user_id,formation_id,commentaire) VALUES(:uid,:fid,:com)");
                $ins2->execute([':uid'=>$user_id,':fid'=>$id_formation,':com'=>$commentaire]);

                // Notif
                $f = $pdo->prepare("SELECT titre FROM formations WHERE id=:id");
                $f->execute([':id'=>$id_formation]);
                $titre_f = $f->fetchColumn();
                notifier($pdo, $user_id, "Inscription confirmée ! 🎉", "Vous êtes inscrit à \"$titre_f\". Bienvenue !");
                log_action($pdo, $user_id, 'PUBLIC_ENROLLMENT', "Inscription publique à \"$titre_f\"");

                $message = "Votre inscription a bien été enregistrée ! Consultez votre email pour vous connecter.";
                $message_type = 'success';
            }
        } catch (PDOException $e) {
            $message = "Erreur : " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

$formations = $pdo->query("SELECT id, titre, prix FROM formations WHERE actif=1 ORDER BY titre")->fetchAll();
$selected_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<div style="max-width:700px;margin:0 auto;">
    <h1>Formulaire d'inscription</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>">
            <?= $message_type==='success' ? '✅' : '❌' ?> <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form id="inscriptionForm" method="post" action="inscription_publique.php">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom']??'') ?>">
            </div>
            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($_POST['prenom']??'') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required placeholder="vous@exemple.com" value="<?= htmlspecialchars($_POST['email']??'') ?>">
        </div>
        <div class="form-group">
            <label for="tel">Téléphone</label>
            <input type="tel" id="tel" name="tel" placeholder="+212 6XX XX XX XX" value="<?= htmlspecialchars($_POST['tel']??'') ?>">
        </div>
        <div class="form-group">
            <label for="id_formation">Formation choisie *</label>
            <select id="id_formation" name="id_formation" required>
                <option value="">-- Sélectionnez une formation --</option>
                <?php foreach ($formations as $f): ?>
                    <option value="<?= $f['id'] ?>" <?= ($selected_id===$f['id']||($_POST['id_formation']??0)==$f['id'])?'selected':'' ?>>
                        <?= htmlspecialchars($f['titre']) ?> (<?= $f['prix']==0?'Gratuit':number_format($f['prix'],2,',',' ').' €' ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="commentaire">Commentaire / Motivation</label>
            <textarea id="commentaire" name="commentaire" rows="4" placeholder="Décrivez votre motivation..."><?= htmlspecialchars($_POST['commentaire']??'') ?></textarea>
        </div>
        <button type="submit" class="btn btn-block">Envoyer mon inscription</button>
    </form>

    <p style="text-align:center;margin-top:1.5rem;color:var(--text-muted);font-size:.9rem;">
        Déjà un compte ?
        <a href="login.php" style="color:var(--accent-color);">Se connecter</a>
    </p>
</div>

<?php include 'includes/footer.php'; ?>
