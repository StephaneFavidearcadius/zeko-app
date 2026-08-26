<?php
// Fichier : admin/users/index.php
// Gestion des utilisateurs

require_once __DIR__ . '/../../includes/admin-auth.php';
$pageTitle = 'Gestion des utilisateurs - Administration';

$pdo = getPDO();

// Filtres
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$role = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

// Requete
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($role)) {
    $sql .= " AND role = ?";
    $params[] = $role;
}

if (!empty($status)) {
    $sql .= " AND status = ?";
    $params[] = $status;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Action: modifier le role ou le statut
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)$_POST['user_id'];
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_role') {
        $newRole = sanitize($_POST['role'] ?? '');
        if (in_array($newRole, ['admin', 'seller'])) {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $userId]);
            setFlashMessage('success', 'Role de l\'utilisateur mis a jour.');
        }
    } elseif ($action === 'update_status') {
        $newStatus = sanitize($_POST['status'] ?? '');
        if (in_array($newStatus, ['active', 'inactive'])) {
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $userId]);
            setFlashMessage('success', 'Statut de l\'utilisateur mis a jour.');
        }
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$userId]);
        setFlashMessage('success', 'Utilisateur supprime.');
    }
    
    redirect(APP_URL . 'admin/users/index.php');
}
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Gestion des utilisateurs</h1>
            <p class="dashboard-subtitle"><?php echo count($users); ?> utilisateur(s) au total</p>
        </div>
    </div>
    
    <!-- Filtres -->
    <div class="filters-bar">
        <form method="GET" action="" class="filters-form">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Rechercher..." 
                       value="<?php echo escape($search); ?>">
            </div>
            <div class="filter-group">
                <select name="role">
                    <option value="">Tous les roles</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="seller" <?php echo $role === 'seller' ? 'selected' : ''; ?>>Vendeur</option>
                </select>
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
            <?php if ($search || $role || $status): ?>
                <a href="<?php echo APP_URL; ?>admin/users/index.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Reinitialiser
                </a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Liste des utilisateurs -->
    <?php if (empty($users)): ?>
        <div class="empty-state-large">
            <i class="fas fa-users"></i>
            <h3>Aucun utilisateur</h3>
            <p>Aucun utilisateur ne correspond a vos criteres.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <strong><?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                            </td>
                            <td><?php echo escape($user['email']); ?></td>
                            <td>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="update_role">
                                    <select name="role" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 4px; border: 1px solid var(--gray-300);">
                                        <option value="seller" <?php echo $user['role'] === 'seller' ? 'selected' : ''; ?>>Vendeur</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 4px; border: 1px solid var(--gray-300);">
                                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Actif</option>
                                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo formatDate($user['created_at'], 'd/m/Y'); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($user['role'] !== 'admin'): ?>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn-icon delete-confirm danger" 
                                                    data-product-name="<?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?>"
                                                    title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 0.8rem;">Protege</span>
                                    <?php endif; ?>
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