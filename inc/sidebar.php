<!-- inc/sidebar.php -->
<nav id="sidebar" class="sidebar bg-white text-dark position-fixed h-100 overflow-auto shadow" style="width: 260px; z-index: 1000;">
    <div class="sidebar-header p-4 border-bottom border-light">
        <h4 class="mb-0 text-center fw-bold text-dark">
            T-Bro Couture
        </h4>
        <small class="d-block text-center text-secondary mt-1">Gestion Atelier</small>
    </div>

    <div class="sidebar-body p-3">
        <ul class="nav flex-column">
            <!-- Accueil -->
            <li class="nav-item mb-2">
                <a href="main_home.php" class="nav-link text-dark <?= basename($_SERVER['PHP_SELF']) === 'main_home.php' ? 'active' : '' ?>">
                    <i class="bi bi-house-door-fill me-3"></i> Dashboard
                </a>
            </li>

            <!-- Clients -->
            <li class="nav-item mb-2">
                <a href="#clientsSubmenu" data-bs-toggle="collapse" class="nav-link text-dark dropdown-toggle <?= str_starts_with(basename($_SERVER['PHP_SELF']), 'client') ? 'active' : '' ?>">
                    <i class="bi bi-people-fill me-3"></i> Clients
                </a>
                <ul class="collapse list-unstyled <?= str_starts_with(basename($_SERVER['PHP_SELF']), 'client') ? 'show' : '' ?>" id="clientsSubmenu">
                    <li>
                        <a href="clients_list.php" class="nav-link text-dark ps-5 <?= basename($_SERVER['PHP_SELF']) === 'clients_list.php' ? 'active' : '' ?>">
                            <i class="bi bi-list-ul me-2"></i> Tous les clients
                        </a>
                    </li>
                    <li>
                        <a href="clients_vip.php" class="nav-link text-dark ps-5 <?= basename($_SERVER['PHP_SELF']) === 'clients_vip.php' ? 'active' : '' ?>">
                            <i class="bi bi-star-fill me-2 text-warning"></i> Clients VIP
                        </a>
                    </li>
                    <li>
                        <a href="client_ajouter.php" class="nav-link text-dark ps-5 <?= basename($_SERVER['PHP_SELF']) === 'client_ajouter.php' ? 'active' : '' ?>">
                            <i class="bi bi-person-plus-fill me-2"></i> Ajouter client
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Commandes -->
            <li class="nav-item mb-2">
                <a href="commandes.php" class="nav-link text-dark <?= basename($_SERVER['PHP_SELF']) === 'commandes.php' ? 'active' : '' ?>">
                    <i class="bi bi-bag-fill me-3"></i> Commandes
                </a>
            </li>

            <!-- Factures -->
            <li class="nav-item mb-2">
                <a href="factures.php" class="nav-link text-dark <?= basename($_SERVER['PHP_SELF']) === 'factures.php' ? 'active' : '' ?>">
                    <i class="bi bi-receipt me-3"></i> Factures
                </a>
            </li>

            <!-- Statistiques -->
            <li class="nav-item mt-4 border-top border-light pt-3">
                <a href="stats.php" class="nav-link text-dark <?= basename($_SERVER['PHP_SELF']) === 'stats.php' ? 'active' : '' ?>">
                    <i class="bi bi-graph-up me-3"></i> Statistiques
                </a>
            </li>

            <!-- Déconnexion (en bas) -->
            <li class="nav-item mt-auto pt-4">
                <a href="logout.php" class="nav-link text-danger">
                    <i class="bi bi-box-arrow-right me-3"></i> Déconnexion
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
    .sidebar {
        top: 0;
        left: 0;
        background-color: #ffffff;
        border-right: 1px solid #e0e0e0;
        transition: all 0.3s;
    }

    .sidebar .nav-link {
        border-radius: 8px;
        padding: 0.75rem 1.25rem;
        transition: all 0.25s;
        color: #000000;
    }

    .sidebar .nav-link:hover {
        background-color: #f0f8ff;
        color: #000000;
    }

    .sidebar .nav-link.active,
    .sidebar .nav-link:active {
        background-color: #02c2fe !important;
        color: white !important;
        font-weight: 500;
    }

    .sidebar .nav-link i {
        width: 24px;
        text-align: center;
    }

    .sidebar .dropdown-toggle::after {
        color: #666;
    }

    .content-with-sidebar {
        margin-left: 260px;
        transition: margin-left 0.3s;
        background-color: #f8f9fa;
        min-height: 100vh;
    }

    @media (max-width: 992px) {
        .sidebar {
            width: 0;
            overflow: hidden;
            padding: 0;
        }
        .content-with-sidebar {
            margin-left: 0;
        }
    }
</style>