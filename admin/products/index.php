<?php
// Fichier : admin/products/index.php
// Supervision des produits

require_once __DIR__ . '/../../includes/admin-auth.php';
$pageTitle = 'Supervision des produits - Administration';

$pdo = getPDO();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$sql = "SELECT p.*, u.first_name, u.last_name, c.name as category_name 
        FROM products p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status)) {
    $sql .= " AND p.status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Action: changer le statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $productId = (int)$_POST['product_id'];
    $newStatus = sanitize($_POST['status'] ?? '');
    
    if (in_array($newStatus, ['active', 'inactive'])) {
        $stmt = $pdo->prepare("UPDATE products SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $productId]);
        setFlashMessage('success', 'Statut du produit mis a jour.');
    }
    redirect(APP_URL . 'admin/products/index.php');
}
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Supervision des produits</h1>
            <p class="dashboard-subtitle"><?php echo count($products); ?> produit(s) au total</p>
        </div>
    </div>
    
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
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-search"></i> Filtrer
            </button>
            <?php if ($search || $status): ?>
                <a href="<?php echo APP_URL; ?>admin/products/index.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Reinitialiser
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if (empty($products)): ?>
        <div class="empty-state-large">
            <i class="fas fa-box-open"></i>
            <h3>Aucun produit</h3>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Vendeur</th>
                        <th>Categorie</th>
                        <th>Prix</th>
                        <th>Ventes</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <strong><?php echo escape($product['name']); ?></strong>
                            </td>
                            <td><?php echo escape($product['first_name'] . ' ' . $product['last_name']); ?></td>
                            <td><?php echo escape($product['category_name'] ?? 'Non categorise'); ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td><?php echo $product['sales_count'] ?? 0; ?></td>
                            <td>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 4px; border: 1px solid var(--gray-300);">
                                        <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Actif</option>
                                        <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?php echo APP_URL; ?>download.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-icon" title="Telecharger">
                                        <i class="fas fa-download" style="color: #10b981;"></i>
                                    </a>
                                    <a href="<?php echo APP_URL; ?>seller/products/edit.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-icon" title="Modifier">
                                        <i class="fas fa-edit"></i>
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
    <?php include __DIR__ . '/../../includes/sidebar-admin.php'; ?>
    <main class="main-content">
        <?php echo $content; ?>
    </main>
</div>
<?php
include __DIR__ . '/../../includes/footer-private.php';
?>