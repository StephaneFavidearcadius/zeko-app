<?php
// Fichier : seller/settings.php
// Paramètres du vendeur

require_once __DIR__ . '/../includes/auth.php';
if (isAdmin()) redirect(APP_URL . 'admin/dashboard.php');
$pageTitle = 'Paramètres - Zeko.app';

$userId = $_SESSION['user_id'];
$pdo = getPDO();

// Récupérer l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Traitement des préférences
$preferences = [];
$stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
$stmt->execute([$userId]);
$prefData = $stmt->fetch();

if ($prefData) {
    $preferences = json_decode($prefData['preferences'], true) ?? [];
}

// Mise à jour des préférences
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_preferences'])) {
    $notifications = isset($_POST['notifications']) ? 1 : 0;
    $marketing = isset($_POST['marketing']) ? 1 : 0;
    
    $prefJson = json_encode([
        'notifications' => $notifications,
        'marketing' => $marketing
    ]);
    
    // Vérifier si l'utilisateur a déjà des préférences
    $stmt = $pdo->prepare("SELECT id FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE user_preferences SET preferences = ? WHERE user_id = ?");
        $stmt->execute([$prefJson, $userId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, preferences) VALUES (?, ?)");
        $stmt->execute([$userId, $prefJson]);
    }
    
    setFlashMessage('success', 'Préférences mises à jour avec succès.');
    redirect(APP_URL . 'seller/settings.php');
}
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Paramètres</h1>
            <p class="dashboard-subtitle">Gérez vos préférences et la sécurité de votre compte</p>
        </div>
        <div class="dashboard-actions">
            <a href="<?php echo APP_URL; ?>seller/dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="settings-grid">
        <!-- Informations du compte -->
        <div class="dashboard-card settings-card">
            <div class="card-header">
                <h3><i class="fas fa-user-cog"></i> Informations du compte</h3>
            </div>
            <div class="card-body">
                <div class="settings-info-list">
                    <div class="settings-info-item">
                        <span class="settings-info-label">Nom</span>
                        <span class="settings-info-value"><?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?></span>
                    </div>
                    <div class="settings-info-item">
                        <span class="settings-info-label">Email</span>
                        <span class="settings-info-value"><?php echo escape($user['email']); ?></span>
                    </div>
                    <div class="settings-info-item">
                        <span class="settings-info-label">Rôle</span>
                        <span class="settings-info-value">
                            <span class="badge badge-<?php echo $user['role']; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="settings-info-item">
                        <span class="settings-info-label">Statut</span>
                        <span class="settings-info-value">
                            <span class="badge badge-<?php echo $user['status']; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </span>
                    </div>
                    <div class="settings-info-item">
                        <span class="settings-info-label">Membre depuis</span>
                        <span class="settings-info-value"><?php echo formatDate($user['created_at'], 'd/m/Y à H:i'); ?></span>
                    </div>
                </div>
                <div class="settings-action">
                    <a href="<?php echo APP_URL; ?>seller/profile.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Modifier le profil
                    </a>
                </div>
            </div>
        </div>

        <!-- Préférences -->
        <div class="dashboard-card settings-card">
            <div class="card-header">
                <h3><i class="fas fa-sliders-h"></i> Préférences</h3>
            </div>
            <div class="card-body">
                <p class="settings-description">
                    <i class="fas fa-info-circle"></i> 
                    Personnalisez vos préférences pour une meilleure expérience.
                </p>
                
                <form method="POST" action="" class="settings-form">
                    <div class="settings-options">
                        <div class="settings-option">
                            <div class="settings-option-control">
                                <label class="switch">
                                    <input type="checkbox" name="notifications" 
                                           <?php echo ($preferences['notifications'] ?? 1) ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="settings-option-info">
                                <span class="settings-option-label">Notifications par email</span>
                                <span class="settings-option-desc">Recevoir des notifications pour les nouvelles commandes</span>
                            </div>
                        </div>
                        
                        <div class="settings-option">
                            <div class="settings-option-control">
                                <label class="switch">
                                    <input type="checkbox" name="marketing" 
                                           <?php echo ($preferences['marketing'] ?? 0) ? 'checked' : ''; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div class="settings-option-info">
                                <span class="settings-option-label">Offres et nouveautés</span>
                                <span class="settings-option-desc">Recevoir les offres promotionnelles et les nouveautés</span>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_preferences" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer les préférences
                    </button>
                </form>
            </div>
        </div>

        <!-- Sécurité -->
        <div class="dashboard-card settings-card">
            <div class="card-header">
                <h3><i class="fas fa-shield-alt"></i> Sécurité</h3>
            </div>
            <div class="card-body">
                <p class="settings-description">
                    <i class="fas fa-info-circle"></i> 
                    Gérez la sécurité de votre compte et protégez vos données.
                </p>
                
                <div class="security-actions">
                    <div class="security-action">
                        <div class="security-action-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <div class="security-action-info">
                            <span class="security-action-label">Changer le mot de passe</span>
                            <span class="security-action-desc">Mettez à jour votre mot de passe régulièrement</span>
                        </div>
                        <a href="<?php echo APP_URL; ?>seller/profile.php#password" class="btn btn-outline btn-sm">
                            Modifier <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    
                    <div class="security-action">
                        <div class="security-action-icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <div class="security-action-info">
                            <span class="security-action-label">Déconnexion</span>
                            <span class="security-action-desc">Terminer votre session en toute sécurité</span>
                        </div>
                        <a href="<?php echo APP_URL; ?>logout.php" class="btn btn-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Se déconnecter
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action rapide : Supprimer le compte -->
        <div class="dashboard-card settings-card danger-zone">
            <div class="card-header">
                <h3><i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i> Zone de danger</h3>
            </div>
            <div class="card-body">
                <p class="danger-description">
                    <i class="fas fa-exclamation-circle"></i> 
                    La suppression de votre compte est irréversible. Toutes vos données seront perdues.
                </p>
                <button type="button" class="btn btn-danger" 
                        onclick="openModal({
                            title: 'Supprimer le compte',
                            message: 'Êtes-vous sûr de vouloir supprimer votre compte ?',
                            icon: 'danger',
                            iconClass: 'fas fa-exclamation-circle',
                            confirmText: 'Supprimer définitivement',
                            confirmClass: 'btn-danger',
                            detail: 'Cette action est irréversible. Tous vos produits, commandes et données seront supprimés.',
                            onConfirm: function() {
                                window.location.href = '<?php echo APP_URL; ?>delete-account.php';
                            }
                        })">
                    <i class="fas fa-trash"></i> Supprimer mon compte
                </button>
            </div>
        </div>
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