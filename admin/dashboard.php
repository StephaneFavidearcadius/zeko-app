<?php
// Fichier : admin/dashboard.php
// Tableau de bord de l'administrateur

require_once __DIR__ . '/../includes/admin-auth.php';
$pageTitle = 'Tableau de bord - Administration';

$pdo = getPDO();

// Statistiques globales
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'seller'");
$totalSellers = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$totalAdmins = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
$totalProducts = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
$totalOrders = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'completed'");
$totalRevenue = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$pendingOrders = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
$newUsers = $stmt->fetch()['total'];

// Ventes récentes
$stmt = $pdo->query("
    SELECT 
        o.*, 
        u.first_name, 
        u.last_name,
        GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') as product_names
    FROM orders o
    LEFT JOIN users u ON o.seller_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 10
");
$recentOrders = $stmt->fetchAll();

// Ventes par mois
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as sales_count,
        SUM(total_amount) as total
    FROM orders 
    WHERE status = 'completed'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");
$monthlySales = $stmt->fetchAll();
$monthlySales = array_reverse($monthlySales);

// IMPORTANT : on calcule le JSON AVANT le heredoc.
// Un heredoc n'exécute pas le PHP à l'intérieur (les balises <?php ?>
// ne sont jamais interprétées) : il ne fait qu'interpoler des variables.
// Comme $monthlySales est un tableau, l'ancien code provoquait
// "Warning: Array to string conversion".
$monthlySalesJson = json_encode($monthlySales);
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Tableau de bord</h1>
            <p class="dashboard-subtitle">Vue d'ensemble de la plateforme</p>
        </div>
        <div class="dashboard-actions">
            <span class="admin-badge">
                <i class="fas fa-shield-alt"></i> Administrateur
            </span>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #3b82f6;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalUsers; ?></span>
                <span class="stat-label">Utilisateurs</span>
                <span class="stat-detail">+<?php echo $newUsers; ?> nouveaux</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalProducts; ?></span>
                <span class="stat-label">Produits</span>
                <span class="stat-detail">Tous les vendeurs</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #e91e63;">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo formatPrice($totalRevenue); ?></span>
                <span class="stat-label">Chiffre d'affaires</span>
                <span class="stat-detail">Total des ventes</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #f59e0b;">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $totalOrders; ?></span>
                <span class="stat-label">Commandes</span>
                <span class="stat-detail"><?php echo $pendingOrders; ?> en attente</span>
            </div>
        </div>
    </div>
    
    <?php if (!empty($monthlySales)): ?>
    <div class="chart-section">
        <div class="section-header">
            <h3>Evolution des ventes</h3>
        </div>
        <div class="chart-container">
            <canvas id="salesChart" height="250"></canvas>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-clock"></i> Ventes récentes</h3>
            <a href="<?php echo APP_URL; ?>admin/orders/index.php" class="card-link">Voir tout</a>
        </div>
        <?php if (!empty($recentOrders)): ?>
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
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo htmlspecialchars($order['order_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td>
                                    <?php 
                                        $sellerName = ($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '');
                                        echo htmlspecialchars(trim($sellerName) ?: 'Inconnu', ENT_QUOTES, 'UTF-8');
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        $products = $order['product_names'] ?? '';
                                        if (is_array($products)) {
                                            $products = implode(', ', $products);
                                        }
                                        if (is_null($products)) {
                                            $products = '';
                                        }
                                        $products = (string)$products;
                                        $display = strlen($products) > 40 ? substr($products, 0, 40) . '...' : $products;
                                        echo htmlspecialchars($display, ENT_QUOTES, 'UTF-8');
                                    ?>
                                </td>
                                <td><strong><?php echo formatPrice($order['total_amount'] ?? 0); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status'] ?? 'pending'; ?>">
                                        <?php 
                                            $statusLabels = [
                                                'pending' => 'En attente',
                                                'completed' => 'Terminee',
                                                'cancelled' => 'Annulee'
                                            ];
                                            echo $statusLabels[$order['status'] ?? 'pending'] ?? $order['status'];
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($order['created_at'] ?? date('Y-m-d H:i:s')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty-state">Aucune vente recente.</p>
        <?php endif; ?>
    </div>
    
    <div class="admin-quick-stats">
        <div class="quick-stat">
            <span class="quick-stat-label">Vendeurs</span>
            <span class="quick-stat-value"><?php echo $totalSellers; ?></span>
        </div>
        <div class="quick-stat">
            <span class="quick-stat-label">Administrateurs</span>
            <span class="quick-stat-value"><?php echo $totalAdmins; ?></span>
        </div>
        <div class="quick-stat">
            <span class="quick-stat-label">Commandes en attente</span>
            <span class="quick-stat-value"><?php echo $pendingOrders; ?></span>
        </div>
        <div class="quick-stat">
            <span class="quick-stat-label">Nouveaux utilisateurs</span>
            <span class="quick-stat-value">+<?php echo $newUsers; ?></span>
        </div>
    </div>
</div>

<?php
$additionalJS = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('salesChart');
    if (ctx) {
        var monthlyData = {$monthlySalesJson};
        var labels = monthlyData.map(function(item) { return item.month; });
        var sales = monthlyData.map(function(item) { return item.sales_count; });
        var totals = monthlyData.map(function(item) { return parseFloat(item.total); });
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Nombre de ventes',
                        data: sales,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: "Chiffre d'affaires (€)",
                        data: totals,
                        borderColor: '#e91e63',
                        backgroundColor: 'rgba(233, 30, 99, 0.1)',
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

// ============================================
// INCLUSION DU HEADER ET DE LA SIDEBAR
// ============================================
include __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
    <?php 
    // Chemin absolu pour inclure la sidebar (fiable quel que soit le include_path)
    include dirname(__DIR__) . '/includes/sidebar-admin.php'; 
    ?>
    <main class="main-content">
        <?php echo $content; ?>
    </main>
</div>
<?php
include __DIR__ . '/../includes/footer.php';
?>