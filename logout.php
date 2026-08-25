<?php
// Fichier : logout.php
// Déconnexion de l'utilisateur

require_once __DIR__ . '/includes/config.php';

// Détruire la session
$_SESSION = array();

// Supprimer le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Supprimer le cookie "se souvenir de moi"
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 86400, '/');
}

// Message flash
setFlashMessage('success', 'Vous avez été déconnecté avec succès.');

// Rediriger vers la landing page
redirect(APP_URL);
?>