<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escape($pageTitle) . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- CSS Global -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/style.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <?php if (isset($additionalCSS)): ?>
        <?php echo $additionalCSS; ?>
    <?php endif; ?>
</head>
<body>

<!-- ============================================ -->
<!-- HEADER / NAVIGATION                          -->
<!-- ============================================ -->
<header>
    <div class="container">
        <nav class="navbar">
            <a href="<?php echo APP_URL; ?>" class="logo">
                Zeko<span>.app</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="<?php echo APP_URL; ?>#features">Fonctionnalités</a></li>
                <li><a href="<?php echo APP_URL; ?>#how-it-works">Comment ça marche</a></li>
                <li><a href="<?php echo APP_URL; ?>#testimonials">Témoignages</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo APP_URL; ?>seller/dashboard.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a></li>
                    <li><a href="<?php echo APP_URL; ?>logout.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a></li>
                <?php else: ?>
                    <li><a href="<?php echo APP_URL; ?>login.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </a></li>
                    <li><a href="<?php echo APP_URL; ?>register.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </a></li>
                <?php endif; ?>
            </ul>
            
            <button class="menu-toggle" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </nav>
    </div>
</header>

<!-- ============================================ -->
<!-- MESSAGES FLASH                              -->
<!-- ============================================ -->
<?php 
$flash = getFlashMessage();
if ($flash): 
?>
    <div class="flash-message flash-<?php echo $flash['type']; ?>">
        <div class="container">
            <span><?php echo escape($flash['message']); ?></span>
            <button class="flash-close">&times;</button>
        </div>
    </div>
<?php endif; ?>

<!-- ============================================ -->
<!-- MODALE DE CONFIRMATION                       -->
<!-- ============================================ -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Confirmer</h3>
            <button type="button" class="modal-close" id="modalClose">&times;</button>
        </div>
        <div class="modal-body">
            <p id="modalMessage">Êtes-vous sûr de vouloir effectuer cette action ?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="modalCancel">Annuler</button>
            <button type="button" class="btn btn-danger" id="modalConfirm">Confirmer</button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CONTENU PRINCIPAL                            -->
<!-- ============================================ -->
<main>