<?php
// Fichier : includes/config.php
// Configuration globale de l'application

// Démarrer les sessions si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définir les constantes de l'application
define('APP_NAME', 'Zeko.app');
define('APP_URL', 'http://localhost/zeko-app/');
define('APP_VERSION', '1.0.0');

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Chemin absolu vers la racine du projet
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

// Chemins vers les dossiers importants
define('UPLOAD_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('PRODUCTS_PATH', UPLOAD_PATH . 'products' . DIRECTORY_SEPARATOR);
define('AVATARS_PATH', UPLOAD_PATH . 'avatars' . DIRECTORY_SEPARATOR);

// Taille maximale des fichiers uploadés (en octets)
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50 MB
define('MAX_AVATAR_SIZE', 5 * 1024 * 1024); // 5 MB

// Extensions autorisées pour les produits
define('ALLOWED_PRODUCT_EXTENSIONS', ['pdf', 'zip', 'rar', 'doc', 'docx', 'txt']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Inclusion du fichier database
require_once ROOT_PATH . 'config/database.php';

// Inclusion des fonctions
require_once ROOT_PATH . 'includes/functions.php';

// Vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Vérifier si l'utilisateur est admin
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Vérifier si l'utilisateur est vendeur
function isSeller() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'seller';
}
?>