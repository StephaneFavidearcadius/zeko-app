<?php
// Fichier : admin/orders/index.php
// Supervision des commandes

require_once __DIR__ . '/../../includes/admin-auth.php';
$pageTitle = 'Supervision des commandes - Administration';

$pdo = getPDO();

$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$sql = "SELECT o.*, u.first_name, u.last_name,
        GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
        FROM orders o
        LEFT JOIN users u ON o.seller_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE 1=1";
$params = [];

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

// Mise a jour du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitize($_POST['status'] ?? '');
    
    if (in_array($newStatus, ['pending', 'completed', 'cancelled'])) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        setFlashMessage('success', 'Statut de la commande mis a jour.');
    }
    redirect(APP_URL . 'admin/orders/index.php');
}
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Supervision des commandes</h1>
            <p class="dashboard-subtitle"><?php echo count($orders); ?> commande(s) au total</p>
        </div>
    </div>
    
    <div class="filters-bar">
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Numero commande, client..." 
                       value="<?php echo escape($search); ?>">
            </div>
            <div class="filter-group">
                <select name="status">
                    <option value="">Tous les statuts</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>En attente</option>
                    <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Terminee</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Annulee</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-search"></i> Filtrer
            </button>
            <?php if ($search || $status): ?>
                <a href="<?php echo APP_URL; ?>admin/orders/index.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Reinitialiser
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if (empty($orders)): ?>
        <div class="empty-state-large">
            <i class="fas fa-shopping-bag"></i>
            <h3>Aucune commande</h3>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Vendeur</th>
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
                            <td><?php echo escape($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo escape($order['buyer_name']); ?></td>
                            <td><?php echo escape(substr($order['product_names'] ?? '', 0, 40)) . (strlen($order['product_names'] ?? '') > 40 ? '...' : ''); ?></td>
                            <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                            <td>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 4px; border: 1px solid var(--gray-300);">
                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Terminee</option>
                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulee</option>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo formatDate($order['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/header.php';
?>
<div class="app-layout">
    <?php include __DIR__ . '/../../includes/sidebar-admin.php'; ?>
    <main class="main-content">
        <?php echo $content; ?>
    </main>
</div>
<?php
include __DIR__ . '/../../includes/footer.php';
?>