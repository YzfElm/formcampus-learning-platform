<?php
// rate_formation.php — Endpoint AJAX pour soumettre une note (1-5 étoiles)
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

// Seuls les utilisateurs connectés peuvent noter
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour noter une formation.']);
    exit;
}

// Lecture et validation des données POST
$formation_id = isset($_POST['formation_id']) ? (int)$_POST['formation_id'] : 0;
$note         = isset($_POST['note'])         ? (int)$_POST['note']         : 0;
$user_id      = (int)$_SESSION['user_id'];

if ($formation_id <= 0 || $note < 1 || $note > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

// Vérifier que la formation existe
$check = $pdo->prepare("SELECT id FROM formations WHERE id = :id AND actif = 1");
$check->execute([':id' => $formation_id]);
if (!$check->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Formation introuvable.']);
    exit;
}

// INSERT ou UPDATE (un seul vote par utilisateur par formation)
$stmt = $pdo->prepare("
    INSERT INTO evaluations (formation_id, utilisateur_id, note)
    VALUES (:fid, :uid, :note)
    ON DUPLICATE KEY UPDATE note = :note2
");
$stmt->execute([
    ':fid'   => $formation_id,
    ':uid'   => $user_id,
    ':note'  => $note,
    ':note2' => $note,
]);

// Récupérer la nouvelle moyenne et le nombre de votes
$stats = $pdo->prepare("
    SELECT note_moyenne, nb_evaluations
    FROM formations
    WHERE id = :id
");
$stats->execute([':id' => $formation_id]);
$row = $stats->fetch();

echo json_encode([
    'success'        => true,
    'message'        => 'Votre note a été enregistrée.',
    'note_moyenne'   => $row ? round((float)$row['note_moyenne'], 1) : $note,
    'nb_evaluations' => $row ? (int)$row['nb_evaluations'] : 1,
    'user_note'      => $note,
]);
