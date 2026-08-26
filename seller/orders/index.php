<?php
// Fichier : seller/orders/index.php
// Liste des commandes du vendeur

require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Mes commandes - Zeko.app';

$userId = $_SESSION['user_id'];
$pdo = getPDO();

// Filtres
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Requête
$sql = "SELECT o.*, 
        GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE o.seller_id = ?";
$params = [$userId];

if (!empty($status)) {
    $sql .= " AND o.status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $sql .= " AND (o.order_number LIKE ? OR o.buyer_name LIKE ? OR o.buyer_email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Statistiques
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE seller_id = ?");
$stmt->execute([$userId]);
$totalOrders = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE seller_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$completedOrders = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE seller_id = ? AND status = 'pending'");
$stmt->execute([$userId]);
$pendingOrders = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE seller_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$totalRevenue = $stmt->fetch()['total'] ?? 0;
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Mes commandes</h1>
            <p class="dashboard-subtitle">
                <?php echo $totalOrders; ?> commande(s) au total
            </p>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon" style="background: #3b82f6;">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalOrders; ?></span>
                <span class="stat-label">Total commandes</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $completedOrders; ?></span>
                <span class="stat-label">Commandes terminées</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #f59e0b;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $pendingOrders; ?></span>
                <span class="stat-label">En attente</span>
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
    </div>

    <!-- Filtres -->
    <div class="filters-bar">
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Rechercher une commande..." 
                       value="<?php echo escape($search); ?>">
            </div>
            <div class="filter-group">
                <select name="status">
                    <option value="">Tous les statuts</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>En attente</option>
                    <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Terminée</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-search"></i> Filtrer
            </button>
            <?php if ($search || $status): ?>
                <a href="<?php echo APP_URL; ?>seller/orders/index.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Liste des commandes -->
    <?php if (empty($orders)): ?>
        <div class="empty-state-large">
            <i class="fas fa-shopping-bag"></i>
            <h3>Aucune commande</h3>
            <p>Vous n'avez pas encore reçu de commandes.</p>
        </div>
    <?php else: ?>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <strong>#<?php echo escape($order['order_number']); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo escape($order['buyer_name']); ?></strong>
                                <br>
                                <small><?php echo escape($order['buyer_email']); ?></small>
                            </td>
                            <td><?php echo escape($order['product_names'] ?? 'N/A'); ?></td>
                            <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php 
                                        $statusLabels = [
                                            'pending' => 'En attente',
                                            'completed' => 'Terminée',
                                            'cancelled' => 'Annulée'
                                        ];
                                        echo $statusLabels[$order['status']] ?? $order['status'];
                                    ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($order['created_at']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?php echo APP_URL; ?>seller/orders/view.php?id=<?php echo $order['id']; ?>" 
                                       class="btn-icon" title="Voir le détail">
                                        <i class="fas fa-eye"></i>
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