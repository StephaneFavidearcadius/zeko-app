<?php
// Fichier : admin/users/view.php
// Détail d'un utilisateur

require_once __DIR__ . '/../../includes/admin-auth.php';
$pageTitle = 'Détail utilisateur - Administration';

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = getPDO();

if ($userId <= 0) {
    setFlashMessage('error', 'Utilisateur non trouvé.');
    redirect(APP_URL . 'admin/users/index.php');
}

// Récupérer l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'Utilisateur non trouvé.');
    redirect(APP_URL . 'admin/users/index.php');
}

// Ses produits
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC
");
$stmt->execute([$userId]);
$products = $stmt->fetchAll();

// Ses ventes (en tant que vendeur)
$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.seller_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 20
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

// Ses achats (en tant qu'acheteur)
$stmt = $pdo->prepare("
    SELECT o.*, 
           GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.buyer_email = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 20
");
$stmt->execute([$user['email']]);
$purchases = $stmt->fetchAll();

// Stats
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE user_id = ?");
$stmt->execute([$userId]);
$totalProducts = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE seller_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$totalSales = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE seller_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$totalRevenue = $stmt->fetch()['total'] ?? 0;
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1><?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?></h1>
            <p class="dashboard-subtitle"><?php echo escape($user['email']); ?></p>
        </div>
        <div class="dashboard-actions">
            <a href="<?php echo APP_URL; ?>admin/users/edit.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="<?php echo APP_URL; ?>admin/users/index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Infos de base -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon" style="background: #3b82f6;">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalProducts; ?></span>
                <span class="stat-label">Produits</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalSales; ?></span>
                <span class="stat-label">Ventes</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #e91e63;">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo formatPrice($totalRevenue); ?></span>
                <span class="stat-label">Chiffre d'affaires</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: <?php echo $user['status'] === 'active' ? '#10b981' : '#e74c3c'; ?>;">
                <i class="fas fa-circle"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo ucfirst($user['status']); ?></span>
                <span class="stat-label">Statut</span>
            </div>
        </div>
    </div>

    <!-- Détails du compte -->
    <div class="dashboard-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h3><i class="fas fa-user"></i> Informations du compte</h3>
        </div>
        <div class="card-body">
            <div class="settings-info-list">
                <div class="settings-info-item">
                    <span class="settings-info-label">ID</span>
                    <span class="settings-info-value">#<?php echo $user['id']; ?></span>
                </div>
                <div class="settings-info-item">
                    <span class="settings-info-label">Nom complet</span>
                    <span class="settings-info-value"><?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?></span>
                </div>
                <div class="settings-info-item">
                    <span class="settings-info-label">Email</span>
                    <span class="settings-info-value"><?php echo escape($user['email']); ?></span>
                </div>
                <div class="settings-info-item">
                    <span class="settings-info-label">Rôle</span>
                    <span class="settings-info-value">
                        <span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
                    </span>
                </div>
                <div class="settings-info-item">
                    <span class="settings-info-label">Statut</span>
                    <span class="settings-info-value">
                        <span class="badge badge-<?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span>
                    </span>
                </div>
                <div class="settings-info-item">
                    <span class="settings-info-label">Inscrit le</span>
                    <span class="settings-info-value"><?php echo formatDate($user['created_at'], 'd/m/Y à H:i'); ?></span>
                </div>
                <div class="settings-info-item">
                    <span class="settings-info-label">Dernière modification</span>
                    <span class="settings-info-value"><?php echo formatDate($user['updated_at'], 'd/m/Y à H:i'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Produits -->
    <?php if (!empty($products)): ?>
    <div class="dashboard-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h3><i class="fas fa-boxes"></i> Produits (<?php echo count($products); ?>)</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Ventes</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><strong><?php echo escape($product['name']); ?></strong></td>
                            <td><?php echo escape($product['category_name'] ?? 'N/A'); ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td><?php echo $product['sales_count'] ?? 0; ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $product['status']; ?>">
                                    <?php echo $product['status'] === 'active' ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Ventes -->
    <?php if (!empty($orders)): ?>
    <div class="dashboard-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h3><i class="fas fa-shopping-cart"></i> Ventes (<?php echo count($orders); ?>)</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Produits</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong>#<?php echo escape($order['order_number']); ?></strong></td>
                            <td><?php echo escape($order['buyer_name']); ?></td>
                            <td><?php echo escape(substr($order['product_names'] ?? '', 0, 30)) . (strlen($order['product_names'] ?? '') > 30 ? '...' : ''); ?></td>
                            <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php 
                                        $labels = ['pending' => 'En attente', 'completed' => 'Terminée', 'cancelled' => 'Annulée'];
                                        echo $labels[$order['status']] ?? $order['status'];
                                    ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($order['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Achats -->
    <?php if (!empty($purchases)): ?>
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-shopping-basket"></i> Achats (<?php echo count($purchases); ?>)</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Vendeur</th>
                        <th>Produits</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchases as $purchase): ?>
                        <tr>
                            <td><strong>#<?php echo escape($purchase['order_number']); ?></strong></td>
                            <td><?php echo $purchase['seller_id']; ?></td>
                            <td><?php echo escape(substr($purchase['product_names'] ?? '', 0, 30)) . (strlen($purchase['product_names'] ?? '') > 30 ? '...' : ''); ?></td>
                            <td><strong><?php echo formatPrice($purchase['total_amount']); ?></strong></td>
                            <td>
                                <span class="status-badge status-<?php echo $purchase['status']; ?>">
                                    <?php 
                                        $labels = ['pending' => 'En attente', 'completed' => 'Terminée', 'cancelled' => 'Annulée'];
                                        echo $labels[$purchase['status']] ?? $purchase['status'];
                                    ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($purchase['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
