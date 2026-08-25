<?php
// Fichier : includes/layout-public.php
// Layout pour les pages publiques (landing, login, register)

// Démarrer la session si ce n'est pas fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la configuration
require_once __DIR__ . '/config.php';

// Inclure le header public
include __DIR__ . '/header-public.php';

// Afficher le contenu de la page
echo $content;

// Inclure le footer public
include __DIR__ . '/footer-public.php';
?>