<?php
// Fichier : seller/orders/view.php
// Détail d'une commande

require_once __DIR__ . '/../../includes/auth.php';
$pageTitle = 'Détail de la commande - Zeko.app';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user_id'];
$pdo = getPDO();

if ($orderId <= 0) {
    setFlashMessage('error', 'Commande non trouvée.');
    redirect(APP_URL . 'seller/orders/index.php');
}

// Récupérer la commande
$stmt = $pdo->prepare("
    SELECT o.* 
    FROM orders o
    WHERE o.id = ? AND o.seller_id = ?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch();

if (!$order) {
    setFlashMessage('error', 'Commande non trouvée.');
    redirect(APP_URL . 'seller/orders/index.php');
}

// Récupérer les produits de la commande
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.id as product_id
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

// Mise à jour du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = sanitize($_POST['status'] ?? '');
    $allowedStatus = ['pending', 'completed', 'cancelled'];
    
    if (in_array($newStatus, $allowedStatus)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND seller_id = ?");
        $stmt->execute([$newStatus, $orderId, $userId]);
        setFlashMessage('success', 'Statut de la commande mis à jour.');
        redirect(APP_URL . 'seller/orders/view.php?id=' . $orderId);
    }
}
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Commande #<?php echo escape($order['order_number']); ?></h1>
            <p class="dashboard-subtitle">
                Commandée le <?php echo formatDate($order['created_at']); ?>
            </p>
        </div>
        <div class="dashboard-actions">
            <a href="<?php echo APP_URL; ?>seller/orders/index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Informations client -->
    <div class="order-info-grid">
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-user"></i> Informations client</h3>
            </div>
            <div class="card-body">
                <p><strong>Nom :</strong> <?php echo escape($order['buyer_name']); ?></p>
                <p><strong>Email :</strong> <?php echo escape($order['buyer_email']); ?></p>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-info-circle"></i> Statut de la commande</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <select name="status" id="status" style="width: 100%; padding: 10px; border: 1px solid var(--gray-300); border-radius: 6px;">
                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Terminée</option>
                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                        </select>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-primary btn-sm">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Produits commandés -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-boxes"></i> Produits commandés</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                        <th>Télécharger</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo escape($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo formatPrice($item['price']); ?></td>
                            <td><strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong></td>
                            <td>
                                <a href="<?php echo APP_URL; ?>download.php?id=<?php echo $item['product_id']; ?>" 
                                   class="btn-icon" title="Télécharger">
                                    <i class="fas fa-download" style="color: #10b981;"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                        <td colspan="2"><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../includes/header.php';
?>
<div class="app-layout">
    <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php echo $content; ?>
    </main>
</div>
<?php
include __DIR__ . '/../../includes/footer.php';
?>