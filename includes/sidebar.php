<?php
// Fichier : includes/sidebar.php
// Sidebar du dashboard vendeur

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo APP_URL; ?>seller/dashboard.php" class="sidebar-logo">
            Zeko<span>.app</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="<?php echo APP_URL; ?>seller/dashboard.php" 
                   class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            <li class="nav-divider">Gestion</li>
            <li>
                <a href="<?php echo APP_URL; ?>seller/products/index.php" 
                   class="<?php echo $currentDir === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-boxes"></i>
                    <span>Produits</span>
                </a>
            </li>
            <li>
                <a href="<?php echo APP_URL; ?>seller/products/add.php" 
                   class="<?php echo $currentPage === 'add.php' && $currentDir === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Ajouter un produit</span>
                </a>
            </li>
            <li>
                <a href="<?php echo APP_URL; ?>seller/orders/index.php" 
                   class="<?php echo $currentDir === 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Commandes</span>
                </a>
            </li>
            <li>
                <a href="<?php echo APP_URL; ?>seller/customers/index.php" 
                   class="<?php echo $currentDir === 'customers' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Clients</span>
                </a>
            </li>
            <li>
                <a href="<?php echo APP_URL; ?>seller/downloads.php" 
                   class="<?php echo $currentPage === 'downloads.php' ? 'active' : ''; ?>">
                    <i class="fas fa-download"></i>
                    <span>Téléchargements</span>
                </a>
            </li>
            <li class="nav-divider">Paramètres</li>
            <li>
                <a href="<?php echo APP_URL; ?>seller/profile.php" 
                   class="<?php echo $currentPage === 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>Profil</span>
                </a>
            </li>
            <li>
                <a href="<?php echo APP_URL; ?>seller/settings.php" 
                   class="<?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>Paramètres</span>
                </a>
            </li>
            <li class="nav-divider"></li>
            <!-- ⬇️ LIEN DÉCONNEXION AVEC MODALE - MODIFIÉ ⬇️ -->
            <li>
                <a href="#" 
                   class="logout-link" 
                   id="logoutButton"
                   data-confirm="Êtes-vous sûr de vouloir vous déconnecter ?"
                   data-confirm-title="Déconnexion"
                   data-confirm-icon="warning"
                   data-confirm-text="Se déconnecter"
                   data-confirm-detail="Vous serez redirigé vers la page d'accueil."
                   onclick="event.preventDefault(); openModal({
                       title: 'Déconnexion',
                       message: 'Êtes-vous sûr de vouloir vous déconnecter ?',
                       icon: 'warning',
                       iconClass: 'fas fa-exclamation-triangle',
                       confirmText: 'Se déconnecter',
                       confirmClass: 'btn-warning',
                       detail: 'Vous serez redirigé vers la page d\'accueil.',
                       onConfirm: function() { window.location.href = '<?php echo APP_URL; ?>logout.php'; }
                   });">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
            <!-- ⬆️ FIN MODIFICATION ⬆️ -->
        </ul>
    </nav>
</aside>