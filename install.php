<?php
session_start();
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

$log = [];

if ($db_ok) {
    // 1. Supprimer les doublons éventuels (garder uniquement id=1 pour admin, id=2 pour user)
    try {
        $pdo->exec("DELETE FROM users WHERE email='admin@formcampus.com' AND id > 1");
        $pdo->exec("DELETE FROM users WHERE email='demo@formcampus.com' AND id > 2");
        $log[] = ['info', "🧹 Doublons supprimés (si existants)"];
    } catch(Exception $e) {}

    // 2. Générer les vrais hash bcrypt via PHP de XAMPP
    $comptes = [
        ['email'=>'admin@formcampus.com', 'password'=>'admin123', 'nom'=>'Campus',      'prenom'=>'Admin',        'role'=>'ADMIN'],
        ['email'=>'demo@formcampus.com',  'password'=>'user123',  'nom'=>'Utilisateur',  'prenom'=>'Demo',          'role'=>'USER'],
    ];

    foreach ($comptes as $c) {
        $hash = password_hash($c['password'], PASSWORD_DEFAULT);

        $chk = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $chk->execute([':email' => $c['email']]);
        $id = $chk->fetchColumn();

        if ($id) {
            $pdo->prepare("UPDATE users SET password=:hash, nom=:nom, prenom=:prenom, role=:role, statut='ACTIF' WHERE email=:email")
                ->execute([':hash'=>$hash, ':nom'=>$c['nom'], ':prenom'=>$c['prenom'], ':role'=>$c['role'], ':email'=>$c['email']]);
            $log[] = ['ok', "✅ Mot de passe mis à jour : <strong>{$c['email']}</strong> → <code>{$c['password']}</code>"];
        } else {
            $pdo->prepare("INSERT INTO users (nom,prenom,email,password,role) VALUES (:nom,:prenom,:email,:hash,:role)")
                ->execute([':nom'=>$c['nom'], ':prenom'=>$c['prenom'], ':email'=>$c['email'], ':hash'=>$hash, ':role'=>$c['role']]);
            $log[] = ['ok', "✅ Compte créé : <strong>{$c['email']}</strong> → <code>{$c['password']}</code>"];
        }

        // Vérification immédiate
        $chk2 = $pdo->prepare("SELECT password FROM users WHERE email=:email");
        $chk2->execute([':email'=>$c['email']]);
        $stored = $chk2->fetchColumn();
        $verify_ok = password_verify($c['password'], $stored);
        $log[] = [$verify_ok?'ok':'err', $verify_ok ? "🔐 Vérification password_verify() : <strong style='color:#86efac;'>OK ✓</strong>" : "❌ Vérification ÉCHOUÉE — problème PHP"];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Installation FormCampus</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:'Segoe UI',sans-serif;background:#050505;color:#e2e8f0;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:2rem}
.box{background:#121212;border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:2.5rem;max-width:580px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.6)}
h1{margin:0 0 .5rem;font-size:1.8rem;background:linear-gradient(135deg,#7c3aed,#06b6d4);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
.sub{color:#666;margin-bottom:2rem}
.msg{padding:12px 16px;border-radius:10px;margin-bottom:.6rem;font-size:.92rem;line-height:1.6}
.ok  {background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#86efac}
.err {background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.info{background:rgba(6,182,212,.1);border:1px solid rgba(6,182,212,.3);color:#67e8f9}
code{background:rgba(255,255,255,.1);padding:2px 7px;border-radius:4px;font-family:monospace;color:#c084fc}
.result{margin-top:1.5rem;background:rgba(6,182,212,.07);border:1px solid rgba(6,182,212,.25);border-radius:14px;padding:1.5rem}
.result h3{margin:0 0 1rem;color:#06b6d4;font-size:1.1rem}
table{width:100%;border-collapse:collapse;font-size:.9rem}
th,td{padding:9px 12px;text-align:left;border-bottom:1px solid rgba(255,255,255,.07)}
th{color:#888;font-weight:500;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px}
.btn{display:inline-block;margin-top:1.5rem;background:linear-gradient(135deg,#7c3aed,#06b6d4);color:#fff;padding:13px 30px;border-radius:50px;text-decoration:none;font-weight:700;font-size:1rem}
.warn{margin-top:1.25rem;background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.25);border-radius:10px;padding:1rem 1.25rem;color:#fbbf24;font-size:.85rem}
</style>
</head>
<body>
<div class="box">
    <h1>🎓 FormCampus — Installation</h1>
    <p class="sub">Génération des comptes de connexion</p>

    <?php if (!$db_ok): ?>
        <div class="msg err">❌ Connexion DB impossible : <?= htmlspecialchars($db_error) ?></div>
    <?php else: ?>

    <?php foreach ($log as [$type, $msg]): ?>
        <div class="msg <?= $type ?>"><?= $msg ?></div>
    <?php endforeach; ?>

    <div class="result">
        <h3>🔑 Comptes prêts</h3>
        <table>
            <tr><th>Rôle</th><th>Email</th><th>Mot de passe</th></tr>
            <tr><td><strong style="color:#a855f7">ADMIN</strong></td><td>admin@formcampus.com</td><td><code>admin123</code></td></tr>
            <tr><td><strong style="color:#60a5fa">USER</strong></td> <td>demo@formcampus.com</td> <td><code>user123</code></td></tr>
        </table>
    </div>

    <a href="login.php" class="btn">→ Se connecter maintenant</a>

    <div class="warn">
        ⚠️ Supprimez <code>install.php</code> après utilisation.
    </div>

    <?php endif; ?>
</div>
</body>
</html>
