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

$client_id = $_GET['id'] ?? null;
$client = null;
$mesures = [];

if ($client_id) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
    $stmt->execute(['id' => $client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($client) {
        $stmt_mesures = $pdo->prepare("
            SELECT id, type_tenue, modele, date_livraison, date_prise 
            FROM mesures 
            WHERE client_id = :client_id 
            ORDER BY date_prise DESC
        ");
        $stmt_mesures->execute(['client_id' => $client_id]);
        $mesures = $stmt_mesures->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (!$client) {
    $_SESSION['message'] = "Client introuvable.";
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historique – <?= htmlspecialchars($client['nom_complet']) ?> - T-Bro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <?php require 'inc/sidebar.php'; ?>

    <div class="content-with-sidebar">
        <main class="container-fluid py-4 px-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Historique des mesures – <?= htmlspecialchars($client['nom_complet']) ?></h2>
                <div>
                    <a href="ajouter_client_mesure.php?id=<?= $client['id'] ?>" class="btn btn-success me-2">
                        <i class="bi bi-rulers me-2"></i>Nouvelle mesure
                    </a>
                    <a href="index.php?search=<?= urlencode($client['telephone']) ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>

            <div class="card shadow border-0 rounded-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Toutes les mesures (<?= count($mesures) ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($mesures) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date mesure</th>
                                        <th>Type tenue</th>
                                        <th>Modèle</th>
                                        <th>Date livraison prévue</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($mesures as $m): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($m['date_prise'])) ?></td>
                                        <td><?= htmlspecialchars($m['type_tenue']) ?></td>
                                        <td><?= htmlspecialchars($m['modele'] ?: '-') ?></td>
                                        <td><?= $m['date_livraison'] ? date('d/m/Y', strtotime($m['date_livraison'])) : '-' ?></td>
                                        <td class="text-end">
                                            <a href="voir_mesure.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Détails
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light text-center py-5 m-0">
                            <i class="bi bi-journal-text display-4 text-muted mb-3 d-block"></i>
                            <h5>Aucune mesure enregistrée pour ce client</h5>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>