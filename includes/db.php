<?php
// includes/db.php
$host     = 'localhost';
$dbname   = 'formcampus';
$username = 'root';
$password = ''; // XAMPP : vide par défaut

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("
    <div style='font-family:sans-serif;padding:40px;background:#1a0000;color:#ff6b6b;border-radius:12px;margin:40px auto;max-width:600px;border:1px solid #ff4444;'>
        <h2>❌ Erreur de connexion à la base de données</h2>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
        <p style='color:#aaa;font-size:0.9rem;'>Vérifiez que XAMPP est lancé et que la base <strong>formcampus</strong> existe.<br>
        Importez le fichier <code>sql/database.sql</code> via phpMyAdmin.</p>
    </div>");
}

// ── Helpers globaux ────────────────────────────────────────

/**
 * Enregistre une action dans les logs.
 */
function log_action(PDO $pdo, ?int $user_id, string $action, string $details = ''): void {
    $stmt = $pdo->prepare(
        "INSERT INTO activity_logs (user_id, action, details) VALUES (:uid, :action, :details)"
    );
    $stmt->execute([':uid' => $user_id, ':action' => $action, ':details' => $details]);
}

/**
 * Crée une notification pour un utilisateur.
 */
function notifier(PDO $pdo, int $user_id, string $titre, string $message): void {
    $stmt = $pdo->prepare(
        "INSERT INTO notifications (user_id, titre, message) VALUES (:uid, :titre, :msg)"
    );
    $stmt->execute([':uid' => $user_id, ':titre' => $titre, ':msg' => $message]);
}

/**
 * Retourne le nombre de notifications non lues d'un utilisateur.
 */
function count_unread_notifications(PDO $pdo, int $user_id): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=:uid AND lu=0");
    $stmt->execute([':uid' => $user_id]);
    return (int)$stmt->fetchColumn();
}
?>
