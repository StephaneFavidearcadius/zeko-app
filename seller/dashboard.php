<?php
// Fichier : seller/dashboard.php
// Tableau de bord du vendeur

require_once __DIR__ . '/../includes/auth.php';
$pageTitle = 'Tableau de bord - Zeko.app';

// Récupérer les statistiques du vendeur
$userId = $_SESSION['user_id'];
$pdo = getPDO();

// 1. Nombre total de produits
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE user_id = ?");
$stmt->execute([$userId]);
$totalProducts = $stmt->fetch()['count'];

// 2. Nombre total de ventes
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE seller_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$totalSales = $stmt->fetch()['count'];

// 3. Chiffre d'affaires
$stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE seller_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$revenue = $stmt->fetch()['total'] ?? 0;

// 4. Nombre total de clients uniques
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT buyer_email) as count FROM orders WHERE seller_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$totalCustomers = $stmt->fetch()['count'];

// 5. Ventes récentes
$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE o.seller_id = ? 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$stmt->execute([$userId]);
$recentOrders = $stmt->fetchAll();

// 6. Produits les plus vendus
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.price, p.cover_image, COUNT(oi.id) as sales_count
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN orders o ON oi.order_id = o.id AND o.status = 'completed'
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY sales_count DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$topProducts = $stmt->fetchAll();

// 7. Ventes par mois (pour le graphique)
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as sales_count,
        SUM(total_amount) as total
    FROM orders 
    WHERE seller_id = ? AND status = 'completed'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
");
$stmt->execute([$userId]);
$monthlySales = $stmt->fetchAll();
$monthlySales = array_reverse($monthlySales);

// 8. Activité récente - VERSION CORRIGÉE
$recentActivity = [];

try {
    $stmt = $pdo->prepare("
        (SELECT 'order' as type, created_at, 
                CAST(order_number AS CHAR) as description 
         FROM orders WHERE seller_id = ?)
        UNION ALL
        (SELECT 'product' as type, created_at,
                CAST(name AS CHAR) as description 
         FROM products WHERE user_id = ?)
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$userId, $userId]);
    $recentActivityRaw = $stmt->fetchAll();

    foreach ($recentActivityRaw as $activity) {
        // FORCER la conversion en chaîne
        $desc = is_array($activity['description']) ? implode(' ', $activity['description']) : (string)$activity['description'];
        
        $recentActivity[] = [
            'type' => $activity['type'],
            'created_at' => $activity['created_at'],
            'description' => $activity['type'] === 'order' 
                ? 'Nouvelle commande #' . $desc 
                : 'Nouveau produit : ' . $desc
        ];
    }
} catch (Exception $e) {
    // En cas d'erreur, on laisse vide
    $recentActivity = [];
}
?>

<?php ob_start(); ?>

<!-- MESSAGES FLASH -->
<?php 
$flash = getFlashMessage();
if ($flash): 
?>
    <div class="flash-message flash-<?php echo $flash['type']; ?>">
        <span><?php echo escape($flash['message']); ?></span>
        <button class="flash-close">&times;</button>
    </div>
<?php endif; ?>

<div class="dashboard-container">
    <!-- Header -->
    <div class="dashboard-header">
        <div>
            <h1>Bonjour, <?php echo escape($_SESSION['user_first_name']); ?> 👋</h1>
            <p class="dashboard-subtitle">Voici un aperçu de votre activité</p>
        </div>
        <div class="dashboard-actions">
            <a href="<?php echo APP_URL; ?>seller/products/add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau produit
            </a>
        </div>
    </div>
    
    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e91e63;">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo formatPrice($revenue); ?></span>
                <span class="stat-label">Chiffre d'affaires</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #3b82f6;">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalSales; ?></span>
                <span class="stat-label">Ventes</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalProducts; ?></span>
                <span class="stat-label">Produits</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #f59e0b;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalCustomers; ?></span>
                <span class="stat-label">Clients</span>
            </div>
        </div>
    </div>
    
    <!-- Graphique des ventes mensuelles -->
    <?php if (!empty($monthlySales)): ?>
    <div class="chart-section">
        <div class="section-header">
            <h3>Évolution des ventes</h3>
        </div>
        <div class="chart-container">
            <canvas id="salesChart" height="250"></canvas>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Deux colonnes : Ventes récentes + Produits populaires -->
    <div class="dashboard-grid">
        <!-- Ventes récentes -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-clock"></i> Ventes récentes</h3>
                <a href="<?php echo APP_URL; ?>seller/orders/index.php" class="card-link">Voir tout</a>
            </div>
            <?php if (!empty($recentOrders)): ?>
                <div class="order-list">
                    <?php foreach ($recentOrders as $order): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <span class="order-number">#<?php echo escape($order['order_number']); ?></span>
                                <span class="order-product"><?php echo escape($order['product_name'] ?? 'Produit inconnu'); ?></span>
                            </div>
                            <div class="order-meta">
                                <span class="order-price"><?php echo formatPrice($order['total_amount']); ?></span>
                                <span class="order-status status-<?php echo $order['status']; ?>">
                                    <?php 
                                        $statusLabels = [
                                            'pending' => 'En attente',
                                            'completed' => 'Terminée',
                                            'cancelled' => 'Annulée'
                                        ];
                                        echo $statusLabels[$order['status']] ?? $order['status'];
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-state">Aucune vente récente.</p>
            <?php endif; ?>
        </div>
        
        <!-- Produits populaires -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-star"></i> Produits populaires</h3>
                <a href="<?php echo APP_URL; ?>seller/products/index.php" class="card-link">Voir tout</a>
            </div>
            <?php if (!empty($topProducts)): ?>
                <div class="product-list">
                    <?php foreach ($topProducts as $product): ?>
                        <?php if ($product['sales_count'] > 0): ?>
                            <div class="product-item">
                                <div class="product-info">
                                    <span class="product-name"><?php echo escape($product['name']); ?></span>
                                    <span class="product-sales"><?php echo $product['sales_count']; ?> ventes</span>
                                </div>
                                <span class="product-price"><?php echo formatPrice($product['price']); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="empty-state">Aucun produit vendu pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Activité récente -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-bolt"></i> Activité récente</h3>
        </div>
        <?php if (!empty($recentActivity)): ?>
            <div class="activity-list">
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="activity-item">
                        <span class="activity-icon <?php echo $activity['type']; ?>">
                            <i class="fas fa-<?php echo $activity['type'] === 'order' ? 'shopping-bag' : 'box'; ?>"></i>
                        </span>
                        <span class="activity-description"><?php echo escape($activity['description']); ?></span>
                        <span class="activity-time"><?php echo formatDate($activity['created_at']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-state">Aucune activité récente.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$monthlySalesJson = json_encode($monthlySales);
$additionalJS = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique des ventes
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        const monthlyData = {$monthlySalesJson};
        const labels = monthlyData.map(item => item.month);
        const sales = monthlyData.map(item => item.sales_count);
        const totals = monthlyData.map(item => parseFloat(item.total));
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Nombre de ventes',
                        data: sales,
                        borderColor: '#e91e63',
                        backgroundColor: 'rgba(233, 30, 99, 0.1)',
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: "Chiffre d'affaires (€)",
                        data: totals,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
});
</script>
JS;

$content = ob_get_clean();
include __DIR__ . '/../includes/header-private.php';
?>
<div class="app-layout">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="main-content">
        <?php echo $content; ?>
    </main>
</div>
<?php
include __DIR__ . '/../includes/footer-private.php';
?>