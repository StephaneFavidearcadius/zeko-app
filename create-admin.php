<?php
// Fichier : create-admin.php
// Script pour créer un compte admin

require_once __DIR__ . '/includes/config.php';

$pdo = getPDO();

$email = 'admin@zeko.app';
$password = 'Admin123!';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Vérifier si l'email existe déjà
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    echo "❌ Le compte admin existe déjà !\n";
    echo "Pour réinitialiser le mot de passe, exécute reset-admin-password.php\n";
    exit();
}

// Créer l'admin
$stmt = $pdo->prepare("
    INSERT INTO users (first_name, last_name, email, password, role, status) 
    VALUES ('Admin', 'Zeko', ?, ?, 'admin', 'active')
");
$stmt->execute([$email, $hashedPassword]);

echo "✅ Compte admin créé avec succès !\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "  Email    : " . $email . "\n";
echo "  Mot de passe : " . $password . "\n";
echo "  URL      : " . APP_URL . "admin-login.php\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
?>