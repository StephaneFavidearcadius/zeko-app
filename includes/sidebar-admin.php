<?php
// Fichier : includes/sidebar-admin.php
// Sidebar de l'espace administrateur

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo APP_URL; ?>admin/dashboard.php" class="sidebar-logo">
            Zeko<span>.app</span>
            <small style="font-size: 0.6rem; opacity: 0.7; display: block; margin-top: 2px;">Admin</small>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?php echo APP_URL; ?>admin/dashboard.php" 
                   class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            <li class="nav-divider">Gestion</li>
            <li>
                <a href="<?php echo APP_URL; ?>admin/users/index.php" 
                   class="<?php echo $currentDir === 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            <li>
                <a href="<?php echo APP_URL; ?>admin/products/index.php" 
                   class="<?php echo $currentDir === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-boxes"></i>
                    <span>Produits</span>
                </a>
            </li>
            <li>
                <a href="<?php echo APP_URL; ?>admin/orders/index.php" 
                   class="<?php echo $currentDir === 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Commandes</span>
                </a>
            </li>
            <li class="nav-divider">Paramètres</li>
            <li>
                <a href="<?php echo APP_URL; ?>admin/settings/index.php" 
                   class="<?php echo $currentPage === 'index.php' && $currentDir === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </li>
            <li class="nav-divider"></li>
            <li>
                <a href="#"
                   class="logout-link"
                   data-confirm="Êtes-vous sûr de vouloir vous déconnecter ?"
                   data-confirm-title="Déconnexion"
                   data-confirm-icon="warning"
                   data-confirm-text="Se déconnecter"
                   data-confirm-detail="Vous serez redirigé vers la page de connexion."
                   data-redirect="<?php echo APP_URL; ?>logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
