<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (is_logged_in()) { header("Location: index.php"); exit; }

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom'] ?? '');
    $prenom   = trim($_POST['prenom'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier doublon email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetchColumn()) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (nom, prenom, email, password, role) VALUES (:nom,:prenom,:email,:password,'USER')"
            );
            $stmt->execute([':nom'=>$nom,':prenom'=>$prenom,':email'=>$email,':password'=>$hashed]);
            $new_id = $pdo->lastInsertId();

            notifier($pdo, $new_id, "Bienvenue sur Form'Campus ! 🎉", "Votre compte a bien été créé. Explorez nos formations !");
            log_action($pdo, $new_id, 'USER_REGISTERED', "Inscription de $prenom $nom");

            $_SESSION['flash_success'] = "Compte créé ! Vous pouvez maintenant vous connecter.";
            header("Location: login.php"); exit;
        }
    }
}
include 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-box">
        <div class="auth-logo">🎓 Form'Campus</div>
        <h2>Créer un compte</h2>

        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="register.php">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($_POST['prenom']??'') ?>">
                </div>
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom']??'') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="vous@exemple.com" value="<?= htmlspecialchars($_POST['email']??'') ?>">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe (min. 6 caractères)</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-block" style="margin-top:1rem;">Créer mon compte</button>
        </form>

        <p style="text-align:center;margin-top:1.5rem;color:var(--text-muted);font-size:.9rem;">
            Déjà un compte ?
            <a href="login.php" style="color:var(--accent-color);">Se connecter</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
