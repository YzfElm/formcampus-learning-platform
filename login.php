<?php
// login.php — Version autonome, sans includes qui pourraient échouer
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['user_role'] === 'ADMIN' ? "admin_dashboard.php" : "index.php"));
    exit;
}

$host   = 'localhost';
$dbname = 'formcampus';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db_ok = true;
} catch (PDOException $e) {
    $db_ok = false;
    $db_error = $e->getMessage();
}

$error = '';
$debug = '';

if ($db_ok && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "Aucun compte trouve avec cet email.";
        } elseif ($user['statut'] === 'SUSPENDU') {
            $error = "Ce compte est suspendu.";
        } elseif (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['user_email']  = $user['email'];
            $_SESSION['user_nom']    = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_role']   = $user['role'];
            $_SESSION['user_avatar'] = $user['avatar'] ?? '';
            try {
                $pdo->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (:uid, 'USER_LOGIN', 'Connexion reussie')")
                    ->execute([':uid' => $user['id']]);
            } catch(Exception $e) {}
            header("Location: " . ($user['role'] === 'ADMIN' ? "admin_dashboard.php" : "index.php"));
            exit;
        } else {
            $error = "Mot de passe incorrect.";
            $debug = "Hash (debut): " . substr($user['password'], 0, 7);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Form'Campus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header><nav><a href="index.php" class="logo">Form'Campus</a></nav></header>
<main class="container">
<div class="auth-wrapper">
<div class="auth-box">
    <div class="auth-logo">🎓 Form'Campus</div>
    <h2>Connexion</h2>

    <?php if (!$db_ok): ?>
        <div class="alert alert-error">❌ Erreur DB : <?= htmlspecialchars($db_error) ?></div>
    <?php else: ?>

    <?php if ($error): ?>
        <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($debug): ?>
        <div class="alert alert-info" style="font-size:.8rem;font-family:monospace;">
            🔍 <?= htmlspecialchars($debug) ?> — 
            Si pas <code>$2y$10</code>, ouvrez <a href="install.php" style="color:#06b6d4;">install.php</a>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="vous@exemple.com" required autofocus>
        </div>
        <div class="form-group" style="margin-top:1rem;">
            <label>Mot de passe</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-block" style="margin-top:1.5rem;">Se connecter</button>
    </form>

    <p style="text-align:center;margin-top:1.5rem;color:var(--text-muted);font-size:.9rem;">
        Pas encore de compte ? <a href="register.php" style="color:var(--accent-color);">Créer un compte</a>
    </p>

   

    <?php endif; ?>
</div>
</div>
</main>
<footer><div style="max-width:1200px;margin:0 auto;padding:0 2rem;text-align:center;">
    <p style="color:var(--text-muted);margin:0;">© <?= date('Y') ?> Form'Campus</p>
</div></footer>
</body>
</html>
