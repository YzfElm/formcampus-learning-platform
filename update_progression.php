<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inscription_id = (int)($_POST['inscription_id'] ?? 0);
    $progression    = max(0, min(100, (int)($_POST['progression'] ?? 0)));

    // Vérifier que l'inscription appartient bien à l'utilisateur
    $stmt = $pdo->prepare("SELECT id FROM inscriptions WHERE id=:id AND user_id=:uid");
    $stmt->execute([':id'=>$inscription_id, ':uid'=>$_SESSION['user_id']]);
    if ($stmt->fetchColumn()) {
        $upd = $pdo->prepare("UPDATE inscriptions SET progression=:p WHERE id=:id");
        $upd->execute([':p'=>$progression, ':id'=>$inscription_id]);
        if ($progression === 100) {
            notifier($pdo, $_SESSION['user_id'], "Formation complétée ! 🏆", "Félicitations, vous avez terminé cette formation à 100% !");
        }
        $_SESSION['flash_success'] = "Progression mise à jour : $progression%";
    }
}

header("Location: my_learning.php");
exit;
?>
