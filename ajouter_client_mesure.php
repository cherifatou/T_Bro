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

$telephone = $_GET['telephone'] ?? '';
$client_id = $_GET['id'] ?? null;
$client = null;
$error = '';

if ($client_id) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
    $stmt->execute(['id' => $client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($telephone) {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE telephone = :tel");
    $stmt->execute(['tel' => $telephone]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_complet    = trim($_POST['nom_complet'] ?? '');
    $prenom         = trim($_POST['prenom'] ?? '');
    $telephone_post = trim($_POST['telephone'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $sexe           = $_POST['sexe'] ?? '';
    $adresse        = trim($_POST['adresse'] ?? '');

    $type_tenue     = $_POST['type_tenue'] ?? '';
    $modele         = trim($_POST['modele'] ?? '');
    $tissu_par      = $_POST['tissu_par'] ?? '';
    $date_livraison = $_POST['date_livraison'] ?? null;

    $mesures        = $_POST['mesures'] ?? [];
    $preferences    = $_POST['preferences'] ?? [];
    $observations   = trim($_POST['observations'] ?? '');

    try {
        $pdo->beginTransaction();

        if (!$client_id && !$client) {
            // Nouveau client
            $stmt = $pdo->prepare("
                INSERT INTO clients 
                (nom_complet, prenom, telephone, email, sexe, adresse, date_inscription) 
                VALUES (:nom, :prenom, :tel, :email, :sexe, :adresse, NOW())
            ");
            $stmt->execute([
                'nom'    => $nom_complet,
                'prenom' => $prenom,
                'tel'    => $telephone_post,
                'email'  => $email,
                'sexe'   => $sexe,
                'adresse'=> $adresse
            ]);
            $client_id = $pdo->lastInsertId();
        } elseif ($client) {
            $client_id = $client['id'];
        }

        // Enregistrer la mesure
        $stmt = $pdo->prepare("
            INSERT INTO mesures 
            (client_id, type_tenue, modele, tissu_par, date_livraison, mesures_json, preferences_json, observations, date_prise) 
            VALUES (:client_id, :type, :modele, :tissu, :date_liv, :mesures, :pref, :obs, NOW())
        ");
        $stmt->execute([
            'client_id'  => $client_id,
            'type'       => $type_tenue,
            'modele'     => $modele,
            'tissu'      => $tissu_par,
            'date_liv'   => $date_livraison ?: null,
            'mesures'    => json_encode($mesures, JSON_UNESCAPED_UNICODE),
            'pref'       => json_encode($preferences, JSON_UNESCAPED_UNICODE),
            'obs'        => $observations
        ]);

        $pdo->commit();

        $_SESSION['message'] = "Mesure enregistrée avec succès !";
        header("Location: index.php?search=" . urlencode($telephone_post ?: $client['telephone']));
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $client ? 'Nouvelle mesure' : 'Nouveau client + Mesure' ?> - T-Bro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

    <?php require 'inc/sidebar.php'; ?>

    <div class="content-with-sidebar">
        <main class="container-fluid py-4 px-4">
            <h2 class="mb-4"><?= $client ? 'Ajouter une mesure pour ' . htmlspecialchars($client['nom_complet']) : 'Nouveau client + Mesure' ?></h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="card shadow border-0 rounded-4 p-4">
                <!-- CLIENT -->
                <h4 class="mb-3">Client</h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                        <input type="text" name="nom_complet" class="form-control" required 
                               value="<?= htmlspecialchars($client['nom_complet'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" 
                               value="<?= htmlspecialchars($client['prenom'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Téléphone <span class="text-danger">*</span></label>
                        <input type="tel" name="telephone" class="form-control" required 
                               value="<?= htmlspecialchars($client['telephone'] ?? $telephone) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" 
                               value="<?= htmlspecialchars($client['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sexe <span class="text-danger">*</span></label>
                        <select name="sexe" class="form-select" required>
                            <option value="">Choisir</option>
                            <option value="Homme"  <?= ($client['sexe']??'')=='Homme'?'selected':'' ?>>Homme</option>
                            <option value="Femme"  <?= ($client['sexe']??'')=='Femme'?'selected':'' ?>>Femme</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="adresse" class="form-control" 
                               value="<?= htmlspecialchars($client['adresse'] ?? '') ?>">
                    </div>
                </div>
                <hr class="my-4">
                <h4 class="mb-3">Tenue</h4>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Type de tenue <span class="text-danger">*</span></label>
                        <select name="type_tenue" id="type_tenue" class="form-select" required>
                            <option value="">Choisir</option>
                            <option value="Costume">Costume</option>
                            <option value="Robe">Robe</option>
                            <option value="Tenue traditionnelle">Tenue traditionnelle</option>
                            <option value="Pantalon seul">Pantalon seul</option>
                            <option value="Chemise">Chemise</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Modèle / Référence</label>
                        <input type="text" name="modele" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tissu fourni par</label>
                        <select name="tissu_par" class="form-select">
                            <option value="Client">Client</option>
                            <option value="Atelier">Atelier</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date livraison prévue</label>
                        <input type="date" name="date_livraison" class="form-control">
                    </div>
                </div>

              
                <hr class="my-4">
                <h4 class="mb-3">Mesures</h4>

                <div id="mesures_haut" class="row g-3 d-none">
                    <h5>Haut du corps</h5>
                    <div class="col-md-4"><label>Tour cou</label><input type="number" step="0.1" name="mesures[tour_cou]" class="form-control"></div>
                    <div class="col-md-4"><label>Carrure</label><input type="number" step="0.1" name="mesures[carrure]" class="form-control"></div>
                    <div class="col-md-4"><label>Épaules</label><input type="number" step="0.1" name="mesures[epaules]" class="form-control"></div>
                    <div class="col-md-4"><label>Poitrine</label><input type="number" step="0.1" name="mesures[poitrine]" class="form-control"></div>
                    <div class="col-md-4"><label>Taille</label><input type="number" step="0.1" name="mesures[taille]" class="form-control"></div>
                    <div class="col-md-4"><label>Long. buste</label><input type="number" step="0.1" name="mesures[long_buste]" class="form-control"></div>
                    <div class="col-md-4"><label>Long. manche</label><input type="number" step="0.1" name="mesures[long_manche]" class="form-control"></div>
                    <div class="col-md-4"><label>Tour bras</label><input type="number" step="0.1" name="mesures[tour_bras]" class="form-control"></div>
                    <div class="col-md-4"><label>Tour poignet</label><input type="number" step="0.1" name="mesures[tour_poignet]" class="form-control"></div>
                </div>

                <div id="mesures_bas" class="row g-3 d-none">
                    <h5>Bas du corps</h5>
                    <div class="col-md-4"><label>Tour taille</label><input type="number" step="0.1" name="mesures[tour_taille]" class="form-control"></div>
                    <div class="col-md-4"><label>Tour hanches</label><input type="number" step="0.1" name="mesures[tour_hanches]" class="form-control"></div>
                    <div class="col-md-4"><label>Long. pantalon</label><input type="number" step="0.1" name="mesures[long_pantalon]" class="form-control"></div>
                    <div class="col-md-4"><label>Entrejambe</label><input type="number" step="0.1" name="mesures[entrejambe]" class="form-control"></div>
                    <div class="col-md-4"><label>Tour cuisse</label><input type="number" step="0.1" name="mesures[tour_cuisse]" class="form-control"></div>
                    <div class="col-md-4"><label>Tour bas jambe</label><input type="number" step="0.1" name="mesures[tour_bas]" class="form-control"></div>
                </div>

                <div id="mesures_femme" class="row g-3 d-none">
                    <h5>Mesures spécifiques femme</h5>
                    <div class="col-md-4"><label>Hauteur poitrine</label><input type="number" step="0.1" name="mesures[hauteur_poitrine]" class="form-control"></div>
                    <div class="col-md-4"><label>Écart poitrine</label><input type="number" step="0.1" name="mesures[ecart_poitrine]" class="form-control"></div>
                    <div class="col-md-4"><label>Longueur dos</label><input type="number" step="0.1" name="mesures[long_dos]" class="form-control"></div>
                    <div class="col-md-4"><label>Largeur dos</label><input type="number" step="0.1" name="mesures[largeur_dos]" class="form-control"></div>
                </div>

               
                <hr class="my-4">
                <h4 class="mb-3">Préférences client</h4>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Coupe souhaitée</label>
                        <select name="preferences[coupe]" class="form-select">
                            <option value="Serrée">Serrée</option>
                            <option value="Normale" selected>Normale</option>
                            <option value="Ample">Ample</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type de col</label>
                        <input type="text" name="preferences[col]" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type de manche</label>
                        <input type="text" name="preferences[manche]" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Aisance souhaitée (cm)</label>
                        <input type="number" step="0.5" name="preferences[aisance]" class="form-control" placeholder="ex: 4">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Commentaires / Souhaits</label>
                        <textarea name="preferences[commentaires]" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                
                <hr class="my-4">
                <h4 class="mb-3">Observations tailleur</h4>
                <textarea name="observations" class="form-control" rows="4" placeholder="Morphologie particulière, ajustements recommandés, notes internes..."></textarea>

                <div class="mt-5 d-flex gap-3 justify-content-end">
                    <a href="index.php" class="btn btn-outline-secondary px-4">
                       <i class="bi bi-arrow-left me-1"></i> Annuler
                     </a>
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="bi bi-save me-2"></i> Enregistrer
                            </button>
                 </div>
            </form>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const typeSelect = document.getElementById('type_tenue');
        const hautDiv    = document.getElementById('mesures_haut');
        const basDiv     = document.getElementById('mesures_bas');
        const femmeDiv   = document.getElementById('mesures_femme');

        function updateMesures() {
            const val = typeSelect.value;
            hautDiv.classList.add('d-none');
            basDiv.classList.add('d-none');
            femmeDiv.classList.add('d-none');

            if (val === 'Costume' || val === 'Chemise' || val === 'Tenue traditionnelle') {
                hautDiv.classList.remove('d-none');
            }
            if (val === 'Costume' || val === 'Pantalon seul' || val === 'Tenue traditionnelle') {
                basDiv.classList.remove('d-none');
            }
            if (val === 'Robe') {
                hautDiv.classList.remove('d-none');
                basDiv.classList.remove('d-none');
                femmeDiv.classList.remove('d-none');
            }
        }

        typeSelect.addEventListener('change', updateMesures);
        if (typeSelect.value) updateMesures();
    </script>
</body>
</html>