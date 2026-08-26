<?php
// Fichier : seller/downloads.php
// Gestion des téléchargements pour le vendeur

require_once __DIR__ . '/../includes/auth.php';
$pageTitle = 'Téléchargements - Zeko.app';

$userId = $_SESSION['user_id'];
$pdo = getPDO();

// Récupérer les téléchargements des produits du vendeur
$stmt = $pdo->prepare("
    SELECT 
        d.*,
        p.name as product_name,
        p.id as product_id,
        u.first_name,
        u.last_name,
        u.email as user_email
    FROM downloads d
    JOIN products p ON d.product_id = p.id
    LEFT JOIN users u ON d.user_id = u.id
    WHERE p.user_id = ?
    ORDER BY d.downloaded_at DESC
    LIMIT 100
");
$stmt->execute([$userId]);
$downloads = $stmt->fetchAll();

// Statistiques - CORRECTION ICI : préciser d.user_id au lieu de user_id
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_downloads,
        COUNT(DISTINCT d.product_id) as total_products,
        COUNT(DISTINCT d.user_id) as total_users
    FROM downloads d
    JOIN products p ON d.product_id = p.id
    WHERE p.user_id = ?
");
$stmt->execute([$userId]);
$stats = $stmt->fetch();

// Téléchargements par produit
$stmt = $pdo->prepare("
    SELECT 
        p.id,
        p.name,
        COUNT(d.id) as download_count,
        p.downloads_count
    FROM products p
    LEFT JOIN downloads d ON p.id = d.product_id
    WHERE p.user_id = ?
    GROUP BY p.id
    ORDER BY download_count DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$productStats = $stmt->fetchAll();
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Téléchargements</h1>
            <p class="dashboard-subtitle">Suivez l'activité de téléchargement de vos produits</p>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #3b82f6;">
                <i class="fas fa-download"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $stats['total_downloads'] ?? 0; ?></span>
                <span class="stat-label">Téléchargements totaux</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $stats['total_products'] ?? 0; ?></span>
                <span class="stat-label">Produits téléchargés</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #f59e0b;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value"><?php echo $stats['total_users'] ?? 0; ?></span>
                <span class="stat-label">Utilisateurs uniques</span>
            </div>
        </div>
    </div>

    <!-- Produits les plus téléchargés -->
    <?php if (!empty($productStats)): ?>
    <div class="dashboard-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h3><i class="fas fa-chart-bar"></i> Produits les plus téléchargés</h3>
        </div>
        <div class="product-list">
            <?php foreach ($productStats as $product): ?>
                <div class="product-item">
                    <div class="product-info">
                        <span class="product-name"><?php echo escape($product['name']); ?></span>
                        <span class="product-sales"><?php echo $product['download_count']; ?> téléchargements</span>
                    </div>
                    <span class="product-price"><?php echo $product['download_count']; ?> téléchargements</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Liste des téléchargements -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Derniers téléchargements</h3>
        </div>
        <?php if (empty($downloads)): ?>
            <p class="empty-state">Aucun téléchargement enregistré.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Utilisateur</th>
                            <th>IP</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($downloads as $download): ?>
                            <tr>
                                <td>
                                    <strong><?php echo escape($download['product_name']); ?></strong>
                                </td>
                                <td>
                                    <?php if ($download['user_id']): ?>
                                        <?php echo escape($download['first_name'] . ' ' . $download['last_name']); ?>
                                        <br>
                                        <small><?php echo escape($download['user_email']); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Invité</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo escape($download['ip_address']); ?></td>
                                <td><?php echo formatDate($download['downloaded_at']); ?></td>
                                <td>
                                    <a href="<?php echo APP_URL; ?>download.php?id=<?php echo $download['product_id']; ?>" 
                                       class="btn-icon" title="Télécharger">
                                        <i class="fas fa-redo"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
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