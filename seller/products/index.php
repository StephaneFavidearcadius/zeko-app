<?php
// Fichier : seller/products/index.php
// Liste des produits du vendeur

require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Mes produits - Zeko.app';

$userId = $_SESSION['user_id'];
$pdo = getPDO();

// Filtres et recherche
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

// Construction de la requête
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.user_id = ?";
$params = [$userId];

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status)) {
    $sql .= " AND p.status = ?";
    $params[] = $status;
}

if (!empty($category)) {
    $sql .= " AND c.slug = ?";
    $params[] = $category;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Récupérer les catégories pour le filtre
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Statistiques rapides
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE user_id = ?");
$stmt->execute([$userId]);
$totalProducts = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE user_id = ? AND status = 'active'");
$stmt->execute([$userId]);
$activeProducts = $stmt->fetch()['total'];

// Suppression (via GET)
if (isset($_GET['delete']) && isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    $productId = (int)$_GET['delete'];
    
    $stmt = $pdo->prepare("SELECT file_path FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$productId, $userId]);
    $product = $stmt->fetch();
    
    if ($product) {
        if (file_exists($product['file_path'])) {
            unlink($product['file_path']);
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
        $stmt->execute([$productId, $userId]);
        
        setFlashMessage('success', 'Produit supprimé avec succès.');
    } else {
        setFlashMessage('error', 'Produit non trouvé.');
    }
    
    redirect(APP_URL . 'seller/products/index.php');
}
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <!-- Header -->
    <div class="dashboard-header">
        <div>
            <h1>Mes produits</h1>
            <p class="dashboard-subtitle">
                <?php echo $totalProducts; ?> produit(s) au total, 
                <?php echo $activeProducts; ?> actif(s)
            </p>
        </div>
        <div class="dashboard-actions">
            <a href="<?php echo APP_URL; ?>seller/products/add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un produit
            </a>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="filters-bar">
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Rechercher un produit..." 
                       value="<?php echo escape($search); ?>">
            </div>
            <div class="filter-group">
                <select name="status">
                    <option value="">Tous les statuts</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Actif</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                </select>
            </div>
            <div class="filter-group">
                <select name="category">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo escape($cat['slug']); ?>" 
                                <?php echo $category === $cat['slug'] ? 'selected' : ''; ?>>
                            <?php echo escape($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-search"></i> Filtrer
            </button>
            <?php if ($search || $status || $category): ?>
                <a href="<?php echo APP_URL; ?>seller/products/index.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Liste des produits -->
    <?php if (empty($products)): ?>
        <div class="empty-state-large">
            <i class="fas fa-box-open"></i>
            <h3>Aucun produit</h3>
            <p>Vous n'avez pas encore ajouté de produit. Commencez dès maintenant !</p>
            <a href="<?php echo APP_URL; ?>seller/products/add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter mon premier produit
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Ventes</th>
                        <th>Téléch.</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <?php if ($product['cover_image']): ?>
                                        <img src="<?php echo APP_URL . 'uploads/products/' . $product['cover_image']; ?>" 
                                             alt="<?php echo escape($product['name']); ?>" 
                                             class="product-thumb">
                                    <?php else: ?>
                                        <div class="product-thumb placeholder">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="product-cell-info">
                                        <span class="product-cell-name"><?php echo escape($product['name']); ?></span>
                                        <span class="product-cell-file"><?php echo escape($product['file_name']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo escape($product['category_name'] ?? 'Non catégorisé'); ?></td>
                            <td><strong><?php echo formatPrice($product['price']); ?></strong></td>
                            <td><?php echo $product['sales_count'] ?? 0; ?></td>
                            <td><?php echo $product['downloads_count'] ?? 0; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $product['status']; ?>">
                                    <?php echo $product['status'] === 'active' ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($product['created_at'], 'd/m/Y'); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?php echo APP_URL; ?>product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-icon" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?php echo APP_URL; ?>seller/products/edit.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-icon" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <!-- BOUTON TÉLÉCHARGER -->
                                    <a href="<?php echo APP_URL; ?>download.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-icon" title="Télécharger le fichier">
                                        <i class="fas fa-download" style="color: #10b981;"></i>
                                    </a>
                                    <!-- BOUTON SUPPRIMER AVEC MODALE -->
                                    <a href="<?php echo APP_URL; ?>seller/products/index.php?delete=<?php echo $product['id']; ?>&confirm=yes" 
                                       class="btn-icon delete-confirm danger" 
                                       title="Supprimer"
                                       data-product-name="<?php echo escape($product['name']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/header-private.php';
?>
<div class="app-layout">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php echo $content; ?>
    </main>
</div>
<?php
include __DIR__ . '/../../includes/footer-private.php';
?>