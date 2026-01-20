<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'inc/_global/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = new PDO(
    "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
    $config['db_user'],
    $config['db_pass']
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$mesure_id = $_GET['id'] ?? null;
$mesure = null;
$client = null;

if ($mesure_id) {
    $stmt = $pdo->prepare("
        SELECT m.*, c.nom_complet, c.telephone, c.sexe 
        FROM mesures m
        INNER JOIN clients c ON c.id = m.client_id
        WHERE m.id = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $mesure_id]);
    $mesure = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($mesure) {
        $mesure['mesures'] = json_decode($mesure['mesures_json'] ?? '{}', true);
        $mesure['preferences'] = json_decode($mesure['preferences_json'] ?? '{}', true);
    }
}

if (!$mesure) {
    $_SESSION['message'] = "Mesure introuvable.";
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Détails mesure #<?= $mesure_id ?> - T-Bro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <?php require 'inc/sidebar.php'; ?>

    <div class="content-with-sidebar">

        <main class="container-fluid py-4 px-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Mesure #<?= $mesure_id ?> – <?= htmlspecialchars($mesure['nom_complet']) ?></h2>
                <a href="index.php?search=<?= urlencode($mesure['telephone']) ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Retour
                </a>
            </div>

            <div class="card shadow border-0 rounded-4 mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><strong>Client :</strong> <?= htmlspecialchars($mesure['nom_complet']) ?></div>
                        <div class="col-md-4"><strong>Téléphone :</strong> <?= htmlspecialchars($mesure['telephone']) ?></div>
                        <div class="col-md-4"><strong>Date prise :</strong> <?= date('d/m/Y H:i', strtotime($mesure['date_prise'])) ?></div>
                        <div class="col-md-4"><strong>Type tenue :</strong> <?= htmlspecialchars($mesure['type_tenue']) ?></div>
                        <div class="col-md-4"><strong>Modèle :</strong> <?= htmlspecialchars($mesure['modele'] ?: '-') ?></div>
                        <div class="col-md-4"><strong>Tissu fourni par :</strong> <?= htmlspecialchars($mesure['tissu_par']) ?></div>
                        <div class="col-md-4"><strong>Date livraison prévue :</strong> <?= $mesure['date_livraison'] ? date('d/m/Y', strtotime($mesure['date_livraison'])) : '-' ?></div>
                    </div>
                </div>
            </div>

            <!-- MESURES -->
            <div class="card shadow border-0 rounded-4 mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Mesures prises</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($mesure['mesures'])): ?>
                        <div class="row g-3">
                            <?php foreach ($mesure['mesures'] as $key => $value): ?>
                                <div class="col-md-4 col-lg-3">
                                    <div class="border rounded p-3 bg-light">
                                        <strong><?= str_replace('_', ' ', ucfirst($key)) ?> :</strong><br>
                                        <span class="fs-5"><?= htmlspecialchars($value) ?> cm</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aucune mesure numérique enregistrée.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PREFERENCES -->
            <div class="card shadow border-0 rounded-4 mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Préférences du client</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($mesure['preferences'])): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($mesure['preferences'] as $k => $v): ?>
                                <li class="list-group-item">
                                    <strong><?= str_replace('_', ' ', ucfirst($k)) ?> :</strong> 
                                    <?= htmlspecialchars($v ?: '-') ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Aucune préférence indiquée.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- OBSERVATIONS -->
            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Observations du tailleur</h5>
                </div>
                <div class="card-body">
                    <pre class="mb-0 bg-light p-3 rounded" style="white-space: pre-wrap;"><?= htmlspecialchars($mesure['observations'] ?: 'Aucune observation.') ?></pre>
                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>