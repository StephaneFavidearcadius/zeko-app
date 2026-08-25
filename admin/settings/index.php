<?php
// Fichier : admin/settings/index.php
// Paramètres de l'administrateur

require_once __DIR__ . '/../../includes/admin-auth.php';
$pageTitle = 'Paramètres - Administration';

$pdo = getPDO();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Paramètres</h1>
            <p class="dashboard-subtitle">Gestion des paramètres administrateur</p>
        </div>
        <div class="dashboard-actions">
            <a href="<?php echo APP_URL; ?>admin/dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="settings-grid">
        <!-- Informations admin -->
        <div class="dashboard-card settings-card">
            <div class="card-header">
                <h3><i class="fas fa-user-cog"></i> Mon compte</h3>
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
                        <span class="settings-info-label">Role</span>
                        <span class="settings-info-value">
                            <span class="badge badge-admin">Administrateur</span>
                        </span>
                    </div>
                    <div class="settings-info-item">
                        <span class="settings-info-label">Membre depuis</span>
                        <span class="settings-info-value"><?php echo formatDate($user['created_at'], 'd/m/Y'); ?></span>
                    </div>
                </div>
                <div class="settings-action">
                    <a href="<?php echo APP_URL; ?>seller/profile.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Modifier le profil
                    </a>
                </div>
            </div>
        </div>

        <!-- Actions admin -->
        <div class="dashboard-card settings-card">
            <div class="card-header">
                <h3><i class="fas fa-tools"></i> Actions</h3>
            </div>
            <div class="card-body">
                <div class="security-actions">
                    <div class="security-action">
                        <div class="security-action-icon" style="background: #3b82f6;">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="security-action-info">
                            <span class="security-action-label">Base de donnees</span>
                            <span class="security-action-desc">Gerer les donnees de la plateforme</span>
                        </div>
                        <a href="<?php echo APP_URL; ?>phpmyadmin/" target="_blank" class="btn btn-outline btn-sm">
                            Acceder <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                    
                    <div class="security-action">
                        <div class="security-action-icon" style="background: #10b981;">
                            <i class="fas fa-sync"></i>
                        </div>
                        <div class="security-action-info">
                            <span class="security-action-label">Nettoyer le cache</span>
                            <span class="security-action-desc">Vider les fichiers temporaires</span>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" 
                                onclick="alert('Fonctionnalite a venir')">
                            <i class="fas fa-broom"></i> Nettoyer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zone de danger -->
        <div class="dashboard-card settings-card danger-zone">
            <div class="card-header">
                <h3><i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i> Zone de danger</h3>
            </div>
            <div class="card-body">
                <p class="danger-description">
                    <i class="fas fa-exclamation-circle"></i> 
                    Ces actions sont irreversibles. Utilisez-les avec prudence.
                </p>
                <div class="security-actions">
                    <div class="security-action" style="border-left: 3px solid #e74c3c;">
                        <div class="security-action-icon" style="background: #e74c3c;">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <div class="security-action-info">
                            <span class="security-action-label">Desactiver un utilisateur</span>
                            <span class="security-action-desc">Desactiver un compte utilisateur</span>
                        </div>
                        <a href="<?php echo APP_URL; ?>admin/users/index.php" class="btn btn-danger btn-sm">
                            Gerer <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
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