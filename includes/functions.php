<?php
// Fichier : includes/functions.php
// Fonctions utilitaires

/**
 * Échapper une chaîne pour l'affichage HTML (protection XSS)
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Sécuriser une donnée avant affichage
 */
function sanitize($string) {
    return trim(htmlspecialchars($string, ENT_QUOTES, 'UTF-8'));
}

/**
 * Générer un token CSRF
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifier un token CSRF
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rediriger vers une URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Afficher un message flash
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Formatage de prix
 */
function formatPrice($price) {
    return number_format($price, 2, ',', ' ') . ' €';
}

/**
 * Formatage de date
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Générer un numéro de commande unique
 */
function generateOrderNumber() {
    return 'ZEK' . date('Ymd') . '-' . strtoupper(uniqid());
}

/**
 * Tronquer un texte
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Vérifier si une extension est autorisée
 */
function isAllowedExtension($filename, $allowedExtensions) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowedExtensions);
}

/**
 * Nettoyer le nom d'un fichier pour l'upload
 */
function sanitizeFilename($filename) {
    // Supprimer les accents
    $filename = iconv('UTF-8', 'ASCII//TRANSLIT', $filename);
    // Remplacer les espaces par des tirets
    $filename = str_replace(' ', '-', $filename);
    // Supprimer les caractères spéciaux
    $filename = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $filename);
    // Mettre en minuscules
    return strtolower($filename);
}

/**
 * Obtenir le nom de la catégorie à partir de son slug
 */
function getCategoryName($slug) {
    $categories = [
        'ebooks' => 'E-books',
        'pdf' => 'PDF',
        'formations' => 'Formations',
        'templates' => 'Templates',
        'guides' => 'Guides'
    ];
    return isset($categories[$slug]) ? $categories[$slug] : ucfirst($slug);
}
?>