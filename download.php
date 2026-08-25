<?php
// Fichier : download.php
// Téléchargement sécurisé des produits

require_once __DIR__ . '/includes/config.php';

// Vérifier que l'utilisateur est connecté
if (!isLoggedIn()) {
    setFlashMessage('error', 'Vous devez être connecté pour télécharger ce fichier.');
    redirect(APP_URL . 'login.php');
    exit();
}

// Récupérer l'ID du produit
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($productId <= 0) {
    setFlashMessage('error', 'Produit non trouvé.');
    redirect(APP_URL . 'seller/dashboard.php');
    exit();
}

$userId = $_SESSION['user_id'];
$pdo = getPDO();

try {
    // 1. Récupérer les informations du produit
    $stmt = $pdo->prepare("
        SELECT p.*, u.id as seller_id, u.first_name, u.last_name 
        FROM products p 
        LEFT JOIN users u ON p.user_id = u.id 
        WHERE p.id = ? AND p.status = 'active'
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        setFlashMessage('error', 'Produit non trouvé ou indisponible.');
        redirect(APP_URL . 'seller/dashboard.php');
        exit();
    }

    // 2. Vérifier les droits de téléchargement
    $hasAccess = false;

    // Cas 1 : Le vendeur peut télécharger ses propres produits
    if ($product['user_id'] == $userId) {
        $hasAccess = true;
    }

    // Cas 2 : L'admin peut tout télécharger
    if (isAdmin()) {
        $hasAccess = true;
    }

    // Cas 3 : L'utilisateur a acheté le produit
    if (!$hasAccess) {
        $stmt = $pdo->prepare("
            SELECT o.id 
            FROM orders o
            JOIN order_items oi ON o.id = oi.order_id
            WHERE oi.product_id = ? 
            AND o.buyer_email = ? 
            AND o.status = 'completed'
            LIMIT 1
        ");
        $stmt->execute([$productId, $_SESSION['user_email']]);
        if ($stmt->fetch()) {
            $hasAccess = true;
        }
    }

    // Cas 4 : Token de téléchargement (pour les liens partagés)
    if (!$hasAccess && !empty($token)) {
        $stmt = $pdo->prepare("
            SELECT id FROM downloads 
            WHERE product_id = ? AND token = ? AND expires_at > NOW()
        ");
        $stmt->execute([$productId, $token]);
        if ($stmt->fetch()) {
            $hasAccess = true;
        }
    }

    // Si pas d'accès, refuser
    if (!$hasAccess) {
        setFlashMessage('error', 'Vous n\'avez pas les droits pour télécharger ce fichier.');
        redirect(APP_URL . 'seller/dashboard.php');
        exit();
    }

    // 3. Vérifier que le fichier existe physiquement
    $filePath = $product['file_path'];
    if (!file_exists($filePath)) {
        setFlashMessage('error', 'Le fichier n\'est plus disponible.');
        redirect(APP_URL . 'seller/dashboard.php');
        exit();
    }

    // 4. Enregistrer le téléchargement dans l'historique
    $stmt = $pdo->prepare("
        INSERT INTO downloads (order_id, product_id, user_id, ip_address, user_agent, downloaded_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $orderId = null; // On pourrait récupérer l'ID de commande si acheté
    $stmt->execute([
        $orderId,
        $productId,
        $userId,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);

    // 5. Incrémenter le compteur de téléchargements du produit
    $stmt = $pdo->prepare("UPDATE products SET downloads_count = downloads_count + 1 WHERE id = ?");
    $stmt->execute([$productId]);

    // 6. Servir le fichier
    $fileName = $product['file_name'];
    $fileSize = filesize($filePath);
    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // Headers pour le téléchargement
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . $fileSize);
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    // Lire et envoyer le fichier
    readfile($filePath);
    exit();

} catch (PDOException $e) {
    setFlashMessage('error', 'Une erreur technique est survenue.');
    redirect(APP_URL . 'seller/dashboard.php');
    exit();
}
?>