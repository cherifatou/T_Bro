<?php
require 'inc/_global/config.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$telephone = trim($_GET['telephone'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');

    if ($nom && $telephone) {
        $stmt = $pdo->prepare("INSERT INTO clients (telephone, nom_complet, prenom, email, adresse) 
                               VALUES (:tel, :nom, :prenom, :email, :adresse)");
        $stmt->execute([
            'tel' => $telephone,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'adresse' => $adresse
        ]);
        $_SESSION['message'] = "Client ajouté avec succès !";
        header("Location: main_home.php?search=" . urlencode($telephone));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Ajouter client - T-Bro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Ajouter un nouveau client</h4>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Téléphone *</label>
                    <input type="tel" class="form-control form-control-lg" name="telephone" value="<?= htmlspecialchars($telephone) ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nom complet *</label>
                    <input type="text" class="form-control form-control-lg" name="nom" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Prénom</label>
                    <input type="text" class="form-control" name="prenom">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email">
                </div>
                <div class="mb-4">
                    <label class="form-label">Adresse</label>
                    <textarea class="form-control" name="adresse" rows="3"></textarea>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg">Enregistrer le client</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>