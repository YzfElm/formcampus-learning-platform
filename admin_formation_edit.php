<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_admin();

$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $id > 0;
$f       = ['titre'=>'','categorie'=>'','description'=>'','duree'=>'','prix'=>0,'formateur'=>'','image'=>''];
$page_title = $is_edit ? "Modifier la formation" : "Ajouter une formation";

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM formations WHERE id=:id");
    $stmt->execute([':id'=>$id]);
    $row = $stmt->fetch();
    if ($row) $f = $row;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $f['titre']       = trim($_POST['titre']     ?? '');
    $f['categorie']   = trim($_POST['categorie'] ?? '');
    $f['description'] = trim($_POST['description']?? '');
    $f['duree']       = trim($_POST['duree']     ?? '');
    $f['prix']        = (float)($_POST['prix']   ?? 0);
    $f['formateur']   = trim($_POST['formateur'] ?? '');

    // Upload image
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error']===UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $newname = uniqid().'_'.basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/'.$newname)) {
                $f['image'] = $newname;
            }
        }
    }

    if ($is_edit) {
        $sql = "UPDATE formations SET titre=:titre,categorie=:categorie,description=:description,
                duree=:duree,prix=:prix,formateur=:formateur,image=:image WHERE id=:id";
        $params = [':titre'=>$f['titre'],':categorie'=>$f['categorie'],':description'=>$f['description'],
                   ':duree'=>$f['duree'],':prix'=>$f['prix'],':formateur'=>$f['formateur'],
                   ':image'=>$f['image'],':id'=>$id];
        log_action($pdo, $_SESSION['user_id'], 'FORMATION_UPDATED', "Modification de \"{$f['titre']}\"");
    } else {
        $sql = "INSERT INTO formations (titre,categorie,description,duree,prix,formateur,image) VALUES (:titre,:categorie,:description,:duree,:prix,:formateur,:image)";
        $params = [':titre'=>$f['titre'],':categorie'=>$f['categorie'],':description'=>$f['description'],
                   ':duree'=>$f['duree'],':prix'=>$f['prix'],':formateur'=>$f['formateur'],':image'=>$f['image']];
        log_action($pdo, $_SESSION['user_id'], 'FORMATION_CREATED', "Création de \"{$f['titre']}\"");
    }

    $pdo->prepare($sql)->execute($params);
    $_SESSION['flash_success'] = $is_edit ? "Formation mise à jour." : "Formation créée avec succès.";
    header("Location: admin_formations.php"); exit;
}

$categories_list = ['Informatique','Marketing','Langues','Design','Management','Finance','Autre'];
include 'includes/header.php';
?>

<div class="admin-layout">
    <?php include 'includes/admin_sidebar.php'; ?>
    <div class="admin-content">

        <div class="page-header">
            <h1><?= $page_title ?></h1>
            <a href="admin_formations.php" class="btn btn-small btn-outline">← Retour</a>
        </div>

        <div style="max-width:700px;">
            <form method="post" action="admin_formation_edit.php<?= $is_edit?'?id='.$id:'' ?>" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Titre *</label>
                    <input type="text" name="titre" required value="<?= htmlspecialchars($f['titre']) ?>" placeholder="Ex : Développement Web Fullstack">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label>Catégorie *</label>
                        <select name="categorie" required>
                            <?php foreach ($categories_list as $cat): ?>
                                <option value="<?= $cat ?>" <?= $f['categorie']===$cat?'selected':'' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Durée</label>
                        <input type="text" name="duree" value="<?= htmlspecialchars($f['duree']) ?>" placeholder="Ex : 3 mois">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label>Prix (€) <small style="color:var(--text-muted);">0 = Gratuit</small></label>
                        <input type="number" step="0.01" min="0" name="prix" required value="<?= htmlspecialchars($f['prix']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Formateur</label>
                        <input type="text" name="formateur" value="<?= htmlspecialchars($f['formateur']??'') ?>" placeholder="Nom du formateur">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="5" placeholder="Décrivez cette formation..."><?= htmlspecialchars($f['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Image de la formation</label>
                    <?php if (!empty($f['image'])): ?>
                        <div style="margin-bottom:.75rem;">
                            <img src="uploads/<?= htmlspecialchars($f['image']) ?>" style="max-height:120px;border-radius:10px;" alt="Image actuelle">
                            <p style="color:var(--text-muted);font-size:.8rem;margin:.5rem 0 0;">Image actuelle. Uploader une nouvelle image pour la remplacer.</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                    <button type="submit" class="btn">
                        <?= $is_edit ? '💾 Enregistrer les modifications' : '+ Créer la formation' ?>
                    </button>
                    <a href="admin_formations.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
