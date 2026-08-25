<?php
// Fichier : includes/admin-auth.php
// Vérification d'authentification pour l'admin

require_once __DIR__ . '/auth.php';

// Vérifier si l'utilisateur est admin
if (!isAdmin()) {
    setFlashMessage('error', 'Accès non autorisé. Vous devez être administrateur.');
    redirect(APP_URL . 'seller/dashboard.php');
    exit();
}