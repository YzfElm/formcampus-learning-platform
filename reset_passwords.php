<?php
// reset_passwords.php
// INSTRUCTIONS : 
// 1. Ouvrez http://localhost/formcampus/reset_passwords.php
// 2. Ce script corrige automatiquement les mots de passe
// 3. SUPPRIMEZ ce fichier après utilisation !

require_once 'includes/db.php';

$updates = [
    ['email' => 'admin@formcampus.com', 'password' => 'admin123'],
    ['email' => 'demo@formcampus.com',  'password' => 'user123'],
];

$done = [];
foreach ($updates as $u) {
    $hash = password_hash($u['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = :hash WHERE email = :email");
    $stmt->execute([':hash' => $hash, ':email' => $u['email']]);
    if ($stmt->rowCount() > 0) {
        $done[] = "✅ {$u['email']} → mot de passe '{$u['password']}' mis à jour";
    } else {
        // Insérer si n'existe pas
        $role = strpos($u['email'], 'admin') !== false ? 'ADMIN' : 'USER';
        $ins = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, role) VALUES (:nom, :prenom, :email, :hash, :role)");
        $ins->execute([
            ':nom'    => $role === 'ADMIN' ? 'Campus' : 'Demo',
            ':prenom' => $role === 'ADMIN' ? 'Admin' : 'Utilisateur',
            ':email'  => $u['email'],
            ':hash'   => $hash,
            ':role'   => $role,
        ]);
        $done[] = "✅ {$u['email']} → compte créé avec le mot de passe '{$u['password']}'";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Reset mots de passe — FormCampus</title>
<style>
body { font-family: sans-serif; background: #0f0f0f; color: #e2e8f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
.box { background: #1e1e2e; border: 1px solid #2d2d3d; border-radius: 16px; padding: 2rem; max-width: 500px; width: 100%; }
h1 { color: #a5b4fc; margin-top: 0; }
.ok { color: #86efac; padding: 10px; background: rgba(34,197,94,.1); border-radius: 8px; margin: 8px 0; }
.warn { color: #fbbf24; margin-top: 1.5rem; font-size: .9rem; background: rgba(251,191,36,.1); padding: 10px; border-radius: 8px; }
a { display: inline-block; margin-top: 1.5rem; background: linear-gradient(135deg,#6366f1,#8b5cf6); color: #fff; padding: 12px 28px; border-radius: 50px; text-decoration: none; font-weight: 600; }
</style>
</head>
<body>
<div class="box">
    <h1>🔑 Réinitialisation des mots de passe</h1>
    <?php foreach ($done as $msg): ?>
        <div class="ok"><?= $msg ?></div>
    <?php endforeach; ?>
    <div class="warn">
        ⚠️ <strong>Supprimez ce fichier</strong> après utilisation pour des raisons de sécurité :<br>
        <code>formcampus/reset_passwords.php</code>
    </div>
    <a href="login.php">→ Aller à la page de connexion</a>
</div>
</body>
</html>
