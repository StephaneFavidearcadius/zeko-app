<?php
// Fichier : includes/auth.php
// Vérification d'authentification pour les pages protégées

require_once __DIR__ . '/config.php';

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    setFlashMessage('error', 'Vous devez être connecté pour accéder à cette page.');
    redirect(APP_URL . 'login.php');
    exit();
}