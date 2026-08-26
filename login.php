<?php
// Fichier : login.php
// Page de connexion des utilisateurs

require_once __DIR__ . '/includes/config.php';

// Si déjà connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(APP_URL . 'admin/dashboard.php');
    } else {
        redirect(APP_URL . 'seller/dashboard.php');
    }
}

$pageTitle = 'Connexion - Zeko.app';

$email = '';
$errors = [];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validation
    if (empty($email)) {
        $errors['email'] = 'L\'email est requis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Veuillez entrer une adresse email valide.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Le mot de passe est requis.';
    }
    
    // Si pas d'erreurs, vérifier les identifiants
    if (empty($errors)) {
        try {
            $pdo = getPDO();
            
            // Récupérer l'utilisateur
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                
                // Vérifier si le compte est actif
                if ($user['status'] !== 'active') {
                    setFlashMessage('error', 'Votre compte est désactivé. Contactez l\'administrateur.');
                    redirect(APP_URL . 'login.php');
                }
                
                // Créer la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_first_name'] = $user['first_name'];
                $_SESSION['user_last_name'] = $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // "Se souvenir de moi" - Cookie
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + 86400 * 30, '/');
                    // TODO: Stocker le token en base de données
                }
                
                setFlashMessage('success', 'Bonjour ' . $user['first_name'] . ' ! Vous êtes connecté.');
                
                // Rediriger selon le rôle
                if ($user['role'] === 'admin') {
                    redirect(APP_URL . 'admin/dashboard.php');
                } else {
                    redirect(APP_URL . 'seller/dashboard.php');
                }
                
            } else {
                $errors['general'] = 'Email ou mot de passe incorrect.';
            }
            
        } catch (PDOException $e) {
            $errors['general'] = 'Une erreur technique est survenue. Veuillez réessayer.';
        }
    }
}
?>

<?php ob_start(); ?>

<div class="auth-page">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <a href="<?php echo APP_URL; ?>" class="auth-logo">Zeko<span>.app</span></a>
                    <h1>Connexion</h1>
                    <p>Connectez-vous à votre espace vendeur</p>
                </div>
                
                <?php if (isset($errors['general'])): ?>
                    <div class="auth-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo escape($errors['general']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" data-validate>
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Adresse email</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                value="<?php echo escape($email); ?>"
                                placeholder="exemple@email.com"
                                required
                            >
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <span class="field-error"><?php echo escape($errors['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Mot de passe - AJOUT DE LA CLASSE has-toggle -->
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Votre mot de passe"
                                required
                                class="has-toggle"
                            >
                            <button type="button" class="toggle-password" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <span class="field-error"><?php echo escape($errors['password']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Options -->
                    <div class="form-options">
                        <div class="form-check">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Se souvenir de moi</label>
                        </div>
                        <a href="forgot-password.php" class="forgot-link">Mot de passe oublié ?</a>
                    </div>
                    
                    <!-- Bouton -->
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                    
                    <!-- Lien inscription -->
                    <div class="auth-footer">
                        Pas encore de compte ? 
                        <a href="<?php echo APP_URL; ?>register.php">S'inscrire</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($pageTitle); ?></title>
    
    <!-- CSS Global -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/style.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            color: var(--text-secondary, #666);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }
        .back-home:hover {
            color: var(--primary, #4f46e5);
        }
    </style>
</head>
<body>

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

<?php echo $content; ?>

<!-- Retour à l'accueil -->
<div style="text-align: center; padding-bottom: 40px;">
    <a href="<?php echo APP_URL; ?>" class="back-home">
        <i class="fas fa-arrow-left"></i> Retour à l'accueil
    </a>
</div>

<!-- JavaScript Global -->
<script src="<?php echo APP_URL; ?>assets/js/app.js"></script>

</body>
</html>