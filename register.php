<?php
// Fichier : register.php
// Page d'inscription des nouveaux utilisateurs

require_once __DIR__ . '/includes/config.php';

// Si déjà connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(APP_URL . 'admin/dashboard.php');
    } else {
        redirect(APP_URL . 'seller/dashboard.php');
    }
}

$pageTitle = 'Inscription - Créez votre compte';

// Initialisation des variables
$firstName = '';
$lastName = '';
$email = '';
$errors = [];
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération et nettoyage des données
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    
    // === VALIDATION ===
    
    // 1. Prénom
    if (empty($firstName)) {
        $errors['first_name'] = 'Le prénom est requis.';
    } elseif (strlen($firstName) < 2) {
        $errors['first_name'] = 'Le prénom doit contenir au moins 2 caractères.';
    } elseif (strlen($firstName) > 50) {
        $errors['first_name'] = 'Le prénom ne doit pas dépasser 50 caractères.';
    }
    
    // 2. Nom
    if (empty($lastName)) {
        $errors['last_name'] = 'Le nom est requis.';
    } elseif (strlen($lastName) < 2) {
        $errors['last_name'] = 'Le nom doit contenir au moins 2 caractères.';
    } elseif (strlen($lastName) > 50) {
        $errors['last_name'] = 'Le nom ne doit pas dépasser 50 caractères.';
    }
    
    // 3. Email
    if (empty($email)) {
        $errors['email'] = 'L\'email est requis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Veuillez entrer une adresse email valide.';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'L\'email ne doit pas dépasser 100 caractères.';
    } else {
        // Vérifier si l'email existe déjà
        try {
            $pdo = getPDO();
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors['email'] = 'Cet email est déjà utilisé. Veuillez vous connecter ou utiliser un autre email.';
            }
        } catch (PDOException $e) {
            // Erreur de base de données
            $errors['general'] = 'Une erreur technique est survenue. Veuillez réessayer.';
        }
    }
    
    // 4. Mot de passe
    if (empty($password)) {
        $errors['password'] = 'Le mot de passe est requis.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif (strlen($password) > 255) {
        $errors['password'] = 'Le mot de passe ne doit pas dépasser 255 caractères.';
    }
    
    // 5. Confirmation du mot de passe
    if (empty($passwordConfirm)) {
        $errors['password_confirm'] = 'Veuillez confirmer votre mot de passe.';
    } elseif ($password !== $passwordConfirm) {
        $errors['password_confirm'] = 'Les mots de passe ne correspondent pas.';
    }
    
    // === SI PAS D'ERREURS, CRÉER LE COMPTE ===
    if (empty($errors)) {
        try {
            $pdo = getPDO();
            
            // Hash du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertion en base de données
            $sql = "INSERT INTO users (first_name, last_name, email, password, role, status) 
                    VALUES (?, ?, ?, ?, 'seller', 'active')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);
            
            $userId = $pdo->lastInsertId();
            
            // Créer la session automatiquement
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_first_name'] = $firstName;
            $_SESSION['user_last_name'] = $lastName;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'seller';
            
            // Message de succès
            setFlashMessage('success', 'Bienvenue ' . $firstName . ' ! Votre compte a été créé avec succès.');
            
            // Rediriger vers le dashboard vendeur
            redirect(APP_URL . 'seller/dashboard.php');
            
        } catch (PDOException $e) {
            // Erreur de base de données
            $errors['general'] = 'Une erreur est survenue lors de la création du compte. Veuillez réessayer.';
        }
    }
}
?>

<?php ob_start(); ?>

<div class="auth-page">
    <div class="container">
        <div class="auth-container">
            <!-- Formulaire d'inscription -->
            <div class="auth-card">
                <div class="auth-header">
                    <a href="<?php echo APP_URL; ?>" class="auth-logo">Zeko<span>.app</span></a>
                    <h1>Créer votre compte</h1>
                    <p>Rejoignez la plateforme des vendeurs de produits numériques</p>
                </div>
                
                <!-- Affichage des erreurs générales -->
                <?php if (isset($errors['general'])): ?>
                    <div class="auth-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo escape($errors['general']); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" data-validate class="auth-form">
                    <!-- Ligne 1 : Prénom + Nom -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Prénom</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input 
                                    type="text" 
                                    id="first_name" 
                                    name="first_name" 
                                    value="<?php echo escape($firstName); ?>"
                                    placeholder="Jean"
                                    required
                                    minlength="2"
                                    maxlength="50"
                                >
                            </div>
                            <?php if (isset($errors['first_name'])): ?>
                                <span class="field-error"><?php echo escape($errors['first_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Nom</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input 
                                    type="text" 
                                    id="last_name" 
                                    name="last_name" 
                                    value="<?php echo escape($lastName); ?>"
                                    placeholder="Dupont"
                                    required
                                    minlength="2"
                                    maxlength="50"
                                >
                            </div>
                            <?php if (isset($errors['last_name'])): ?>
                                <span class="field-error"><?php echo escape($errors['last_name']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
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
                                maxlength="100"
                            >
                        </div>
                        <?php if (isset($errors['email'])): ?>
                            <span class="field-error"><?php echo escape($errors['email']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Mot de passe -->
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Au moins 6 caractères"
                                required
                                minlength="6"
                            >
                            <button type="button" class="toggle-password" data-target="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="field-hint">Le mot de passe doit contenir au moins 6 caractères.</span>
                        <?php if (isset($errors['password'])): ?>
                            <span class="field-error"><?php echo escape($errors['password']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Confirmation mot de passe -->
                    <div class="form-group">
                        <label for="password_confirm">Confirmer le mot de passe</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input 
                                type="password" 
                                id="password_confirm" 
                                name="password_confirm" 
                                placeholder="Confirmez votre mot de passe"
                                required
                                data-match="#password"
                            >
                            <button type="button" class="toggle-password" data-target="password_confirm">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['password_confirm'])): ?>
                            <span class="field-error"><?php echo escape($errors['password_confirm']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Conditions -->
                    <div class="form-check">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            J'accepte les 
                            <a href="#">conditions générales</a> 
                            et la 
                            <a href="#">politique de confidentialité</a>
                        </label>
                    </div>
                    
                    <!-- Bouton -->
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Créer mon compte
                    </button>
                    
                    <!-- Lien connexion -->
                    <div class="auth-footer">
                        Déjà un compte ? 
                        <a href="<?php echo APP_URL; ?>login.php">Se connecter</a>
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