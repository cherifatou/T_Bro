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

$client = null;
$search = trim($_GET['search'] ?? '');
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

$mesures_historique = [];

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE telephone = :tel LIMIT 1");
    $stmt->execute(['tel' => $search]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($client) {
        $stmt_mesures = $pdo->prepare("
            SELECT id, type_tenue, modele, date_livraison, date_prise 
            FROM mesures 
            WHERE client_id = :client_id 
            ORDER BY date_prise DESC
            LIMIT 10
        ");
        $stmt_mesures->execute(['client_id' => $client['id']]);
        $mesures_historique = $stmt_mesures->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accueil - T-Bro Gestion Atelier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <?php require 'inc/sidebar.php'; ?>

    <div class="content-with-sidebar">

        <header class="bg-gradient py-4 text-white shadow-sm" style="background: linear-gradient(90deg, #02c2fe, #0199c7);">
            <div class="container-fluid px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="mb-0 fw-bold">
                        <i class="bi bi-scissors me-2"></i>T-Bro Gestion Atelier
                    </h3>
                    <div>
                        <span class="me-3">Connecté : <?= htmlspecialchars($_SESSION['nom'] ?? 'Utilisateur') ?></span>
                        <a href="logout.php" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="container-fluid py-4 px-4">
            
            <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="card shadow border-0 rounded-4 mb-5">
                <div class="card-body p-4">
                    <form method="GET" class="d-flex gap-3 flex-wrap">
                        <div class="flex-grow-1" style="min-width: 300px;">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white"><i class="bi bi-telephone-fill text-primary"></i></span>
                                <input type="tel" name="search" class="form-control" 
                                       placeholder="Rechercher client par numéro (ex: 80469115)"
                                       value="<?= htmlspecialchars($search) ?>" autofocus>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-search"></i> Rechercher
                        </button>
                    </form>
                </div>
            </div>

            <?php if ($search !== ''): ?>
                <?php if ($client): ?>
                    <div class="card shadow border-0 rounded-4 mb-5">
                        <div class="card-header bg-primary text-white rounded-top-4 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Client : <?= htmlspecialchars($client['nom_complet'] ?? 'Inconnu') ?></h5>
                            <small><?= htmlspecialchars($client['telephone']) ?></small>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <strong>Nom complet :</strong><br>
                                    <?= htmlspecialchars($client['nom_complet']) ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Téléphone :</strong><br>
                                    <strong><?= htmlspecialchars($client['telephone']) ?></strong>
                                </div>
                                <div class="col-md-6">
                                    <strong>Email :</strong><br>
                                    <?= htmlspecialchars($client['email'] ?? '-') ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Adresse :</strong><br>
                                    <?= htmlspecialchars($client['adresse'] ?? '-') ?>
                                </div>
                                <div class="col-12">
                                    <strong>Inscrit le :</strong><br>
                                    <?= date('d/m/Y à H:i', strtotime($client['date_inscription'])) ?>
                                </div>
                            </div>

                            <h5 class="mt-4 mb-3">Historique des mesures (<?= count($mesures_historique) ?>)</h5>

                            <?php if (count($mesures_historique) > 0): ?>
                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Type tenue</th>
                                                <th>Modèle</th>
                                                <th>Livraison</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($mesures_historique as $mesure): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($mesure['date_prise'])) ?></td>
                                                <td><?= htmlspecialchars($mesure['type_tenue']) ?></td>
                                                <td><?= htmlspecialchars($mesure['modele'] ?: '-') ?></td>
                                                <td><?= $mesure['date_livraison'] ? date('d/m/Y', strtotime($mesure['date_livraison'])) : '-' ?></td>
                                                <td class="text-end">
                                                    <a href="voir_mesure.php?id=<?= $mesure['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-light text-center py-3 mb-4">
                                    Aucune mesure enregistrée pour ce client pour le moment.
                                </div>
                            <?php endif; ?>

                            <a href="ajouter_client_mesure.php?id=<?= $client['id'] ?>" class="btn btn-success btn-lg me-2">
                                <i class="bi bi-rulers me-2"></i>Ajouter une nouvelle mesure
                            </a>

                            <?php if (count($mesures_historique) > 0): ?>
                            <a href="historique_client.php?id=<?= $client['id'] ?>" class="btn btn-outline-primary">
                                Voir tout l'historique
                            </a>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info d-flex justify-content-between align-items-center rounded-4 shadow-sm p-4">
                        <div>
                            <strong>Aucun client trouvé</strong><br>
                            avec le numéro <strong><?= htmlspecialchars($search) ?></strong>.
                        </div>
                        <a href="ajouter_client_mesure.php?telephone=<?= urlencode($search) ?>" class="btn btn-success btn-lg">
                            <i class="bi bi-person-plus-fill me-2"></i>Ajouter ce client + Mesure
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <h4 class="mt-5 mb-3 fw-semibold">Liste des clients récents</h4>

            <?php
            $stmt = $pdo->query("
                SELECT 
                    c.id, c.telephone, c.nom_complet, c.prenom, c.email, c.date_inscription,
                    COUNT(m.id) AS nb_mesures
                FROM clients c
                LEFT JOIN mesures m ON m.client_id = c.id
                GROUP BY c.id
                ORDER BY c.date_inscription DESC 
                LIMIT 50
            ");
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (count($clients) > 0): ?>
            <div class="card shadow border-0 rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Téléphone</th>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Nb mesures</th>
                                <th>Inscrit le</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; foreach ($clients as $cl): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><strong><?= htmlspecialchars($cl['telephone']) ?></strong></td>
                                <td><?= htmlspecialchars($cl['nom_complet']) ?></td>
                                <td><?= htmlspecialchars($cl['email'] ?? '-') ?></td>
                                <td class="text-center"><?= $cl['nb_mesures'] ?></td>
                                <td><?= date('d/m/Y', strtotime($cl['date_inscription'])) ?></td>
                                <td class="text-end">
                                    <a href="ajouter_client_mesure.php?id=<?= $cl['id'] ?>" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-rulers"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-light text-center py-5 rounded-4 shadow-sm">
                <i class="bi bi-people display-4 text-muted mb-3 d-block"></i>
                <h5>Aucun client enregistré pour le moment</h5>
                <p>Utilisez la barre de recherche ci-dessus pour ajouter votre premier client.</p>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>