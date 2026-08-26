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

$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');

        if (empty($firstName)) $errors['first_name'] = 'Le prénom est requis.';
        if (empty($lastName)) $errors['last_name'] = 'Le nom est requis.';
        if (empty($email)) {
            $errors['email'] = 'L\'email est requis.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email invalide.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $errors['email'] = 'Cet email est déjà utilisé.';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
            $stmt->execute([$firstName, $lastName, $email, $userId]);

            $_SESSION['user_first_name'] = $firstName;
            $_SESSION['user_last_name'] = $lastName;
            $_SESSION['user_email'] = $email;

            setFlashMessage('success', 'Profil mis à jour avec succès.');
            redirect(APP_URL . 'admin/settings/index.php');
        }
    }

    if ($action === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword)) {
            $errors['current_password'] = 'Veuillez entrer votre mot de passe actuel.';
        } else {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $userData = $stmt->fetch();
            if (!password_verify($currentPassword, $userData['password'])) {
                $errors['current_password'] = 'Mot de passe actuel incorrect.';
            }
        }

        if (empty($newPassword)) {
            $errors['new_password'] = 'Veuillez entrer un nouveau mot de passe.';
        } elseif (strlen($newPassword) < 6) {
            $errors['new_password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }

        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Les mots de passe ne correspondent pas.';
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $userId]);
            setFlashMessage('success', 'Mot de passe mis à jour avec succès.');
            redirect(APP_URL . 'admin/settings/index.php');
        }
    }
}
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Paramètres</h1>
            <p class="dashboard-subtitle">Gérez votre profil et la sécurité de votre compte</p>
        </div>
        <div class="dashboard-actions">
            <a href="<?php echo APP_URL; ?>admin/dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Messages d'erreur -->
    <?php if (!empty($errors)): ?>
        <div class="auth-error" style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
            Veuillez corriger les erreurs ci-dessous.
        </div>
    <?php endif; ?>

    <div class="profile-grid">
        <!-- Informations personnelles -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-user-edit"></i> Informations personnelles</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Prénom</label>
                            <input type="text" id="first_name" name="first_name"
                                   value="<?php echo escape($user['first_name']); ?>" placeholder="Votre prénom" required>
                            <?php if (isset($errors['first_name'])): ?>
                                <span class="field-error"><?php echo escape($errors['first_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Nom</label>
                            <input type="text" id="last_name" name="last_name"
                                   value="<?php echo escape($user['last_name']); ?>" placeholder="Votre nom" required>
                            <?php if (isset($errors['last_name'])): ?>
                                <span class="field-error"><?php echo escape($errors['last_name']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input type="email" id="email" name="email"
                               value="<?php echo escape($user['email']); ?>" placeholder="exemple@email.com" required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="field-error"><?php echo escape($errors['email']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label>Date d'inscription</label>
                        <input type="text" value="<?php echo formatDate($user['created_at'], 'd/m/Y à H:i'); ?>" disabled>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </form>
            </div>
        </div>

        <!-- Changement de mot de passe -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fas fa-key"></i> Sécurité</h3>
            </div>
            <div class="card-body">
                <p class="security-info">
                    <i class="fas fa-shield-alt"></i>
                    Changez votre mot de passe régulièrement pour protéger votre compte.
                </p>

                <form method="POST" action="" class="password-form">
                    <input type="hidden" name="action" value="update_password">

                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="current_password" name="current_password"
                                   placeholder="Votre mot de passe actuel" required>
                            <button type="button" class="toggle-password" data-target="current_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['current_password'])): ?>
                            <span class="field-error"><?php echo escape($errors['current_password']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="new_password" name="new_password"
                                   placeholder="Au moins 6 caractères" required minlength="6">
                            <button type="button" class="toggle-password" data-target="new_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="field-hint">Le mot de passe doit contenir au moins 6 caractères.</span>
                        <?php if (isset($errors['new_password'])): ?>
                            <span class="field-error"><?php echo escape($errors['new_password']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   placeholder="Confirmez votre mot de passe" required>
                            <button type="button" class="toggle-password" data-target="confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <span class="field-error"><?php echo escape($errors['confirm_password']); ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key"></i> Changer le mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Actions admin -->
    <div class="dashboard-card" style="margin-top: 24px;">
        <div class="card-header">
            <h3><i class="fas fa-tools"></i> Actions système</h3>
        </div>
        <div class="card-body">
            <div class="security-actions">
                <div class="security-action">
                    <div class="security-action-icon" style="background: #3b82f6;">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="security-action-info">
                        <span class="security-action-label">Base de données</span>
                        <span class="security-action-desc">Gérer les données de la plateforme</span>
                    </div>
                    <a href="<?php echo APP_URL; ?>phpmyadmin/" target="_blank" class="btn btn-outline btn-sm">
                        Accéder <i class="fas fa-external-link-alt"></i>
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
                            onclick="alert('Fonctionnalité à venir')">
                        <i class="fas fa-broom"></i> Nettoyer
                    </button>
                </div>
            </div>
        </div>
    </div>
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
