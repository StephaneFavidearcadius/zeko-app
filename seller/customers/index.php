<?php
// Fichier : seller/customers/index.php
// Liste des clients du vendeur

require_once __DIR__ . '/../../includes/auth.php';
if (isAdmin()) redirect(APP_URL . 'admin/dashboard.php');
$pageTitle = 'Mes clients - Zeko.app';

$userId = $_SESSION['user_id'];
$pdo = getPDO();

// Requête pour récupérer les clients
$stmt = $pdo->prepare("
    SELECT 
        buyer_email,
        buyer_name,
        COUNT(*) as order_count,
        SUM(total_amount) as total_spent,
        MAX(created_at) as last_order,
        MIN(created_at) as first_order
    FROM orders
    WHERE seller_id = ? AND status = 'completed'
    GROUP BY buyer_email, buyer_name
    ORDER BY total_spent DESC
");
$stmt->execute([$userId]);
$customers = $stmt->fetchAll();

$totalCustomers = count($customers);
$totalRevenue = array_sum(array_column($customers, 'total_spent'));
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Mes clients</h1>
            <p class="dashboard-subtitle">
                <?php echo $totalCustomers; ?> client(s) au total
            </p>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        <div class="stat-card">
            <div class="stat-icon" style="background: #3b82f6;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalCustomers; ?></span>
                <span class="stat-label">Clients uniques</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo formatPrice($totalRevenue); ?></span>
                <span class="stat-label">Chiffre d'affaires total</span>
            </div>
        </div>
    </div>

    <!-- Liste des clients -->
    <?php if (empty($customers)): ?>
        <div class="empty-state-large">
            <i class="fas fa-users"></i>
            <h3>Aucun client</h3>
            <p>Vous n'avez pas encore de clients.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Email</th>
                        <th>Commandes</th>
                        <th>Total dépensé</th>
                        <th>Première commande</th>
                        <th>Dernière commande</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><strong><?php echo escape($customer['buyer_name']); ?></strong></td>
                            <td><?php echo escape($customer['buyer_email']); ?></td>
                            <td><?php echo $customer['order_count']; ?></td>
                            <td><strong><?php echo formatPrice($customer['total_spent']); ?></strong></td>
                            <td><?php echo formatDate($customer['first_order'], 'd/m/Y'); ?></td>
                            <td><?php echo formatDate($customer['last_order'], 'd/m/Y'); ?></td>
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