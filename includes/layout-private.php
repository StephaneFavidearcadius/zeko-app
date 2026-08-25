<?php
// Fichier : includes/layout-private.php
// Layout pour les pages privées (dashboard vendeur et admin)

// Vérifier l'authentification
require_once __DIR__ . '/auth.php';

// Démarrer la session si ce n'est pas fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration
require_once __DIR__ . '/config.php';

// Inclure le header privé (minimal)
include __DIR__ . '/header-private.php';

// Afficher le contenu de la page
echo $content;

// Inclure le footer privé (minimal)
include __DIR__ . '/footer-private.php';
?>