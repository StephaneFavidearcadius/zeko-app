<?php
// Fichier : admin/users/edit.php
// Modification d'un utilisateur par l'admin

require_once __DIR__ . '/../../includes/admin-auth.php';
$pageTitle = 'Modifier utilisateur - Administration';

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = getPDO();

if ($userId <= 0) {
    setFlashMessage('error', 'Utilisateur non trouvé.');
    redirect(APP_URL . 'admin/users/index.php');
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('error', 'Utilisateur non trouvé.');
    redirect(APP_URL . 'admin/users/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $role = sanitize($_POST['role'] ?? '');
    $status = sanitize($_POST['status'] ?? '');

    // Validation
    if (empty($firstName)) $errors['first_name'] = 'Le prénom est requis.';
    if (empty($lastName)) $errors['last_name'] = 'Le nom est requis.';
    if (empty($email)) {
        $errors['email'] = 'L\'email est requis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email invalide.';
    } else {
        // Vérifier unicité
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Cet email est déjà utilisé.';
        }
    }
    if (!in_array($role, ['admin', 'seller'])) $errors['role'] = 'Rôle invalide.';
    if (!in_array($status, ['active', 'inactive'])) $errors['status'] = 'Statut invalide.';

    // Empêcher de se désactiver soi-même
    if ($userId == $_SESSION['user_id'] && $status === 'inactive') {
        $errors['status'] = 'Vous ne pouvez pas désactiver votre propre compte.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, status = ? WHERE id = ?");
        $stmt->execute([$firstName, $lastName, $email, $role, $status, $userId]);
        setFlashMessage('success', 'Utilisateur modifié avec succès.');
        redirect(APP_URL . 'admin/users/index.php');
    }
}

$firstName = $user['first_name'];
$lastName = $user['last_name'];
$email = $user['email'];
$role = $user['role'];
$status = $user['status'];
?>

<?php ob_start(); ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1>Modifier l'utilisateur</h1>
            <p class="dashboard-subtitle"><?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?></p>
        </div>
        <div class="dashboard-actions">
            <a href="<?php echo APP_URL; ?>admin/users/view.php?id=<?php echo $userId; ?>" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="form-card">
        <?php if (isset($errors['general'])): ?>
            <div class="auth-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo escape($errors['general']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-main">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Prénom *</label>
                            <input type="text" id="first_name" name="first_name"
                                   value="<?php echo escape($firstName); ?>" required>
                            <?php if (isset($errors['first_name'])): ?>
                                <span class="field-error"><?php echo escape($errors['first_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="last_name">Nom *</label>
                            <input type="text" id="last_name" name="last_name"
                                   value="<?php echo escape($lastName); ?>" required>
                            <?php if (isset($errors['last_name'])): ?>
                                <span class="field-error"><?php echo escape($errors['last_name']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Adresse email *</label>
                        <input type="email" id="email" name="email"
                               value="<?php echo escape($email); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="field-error"><?php echo escape($errors['email']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="role">Rôle *</label>
                            <select id="role" name="role" required>
                                <option value="seller" <?php echo $role === 'seller' ? 'selected' : ''; ?>>Vendeur</option>
                                <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                            </select>
                            <?php if (isset($errors['role'])): ?>
                                <span class="field-error"><?php echo escape($errors['role']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="status">Statut *</label>
                            <select id="status" name="status" required>
                                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Actif</option>
                                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                            </select>
                            <?php if (isset($errors['status'])): ?>
                                <span class="field-error"><?php echo escape($errors['status']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Membre depuis</label>
                        <input type="text" value="<?php echo formatDate($user['created_at'], 'd/m/Y à H:i'); ?>" disabled>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
                <a href="<?php echo APP_URL; ?>admin/users/view.php?id=<?php echo $userId; ?>" class="btn btn-outline">
                    Annuler
                </a>
            </div>
        </form>
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
