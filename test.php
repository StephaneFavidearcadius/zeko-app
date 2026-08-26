<?php
require_once __DIR__ . '/includes/config.php';

$pageTitle = 'Test de configuration';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div style="max-width: 800px; margin: 50px auto; padding: 20px;">
    <h1>🧪 Test de l'architecture Zeko.app</h1>
    
    <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h2>✅ Configurations vérifiées :</h2>
        <ul>
            <li>✓ Fichier config.php chargé</li>
            <li>✓ Fichier database.php chargé</li>
            <li>✓ Connexion PDO : <?php echo testDatabaseConnection() ? '✅ OK' : '❌ ÉCHEC'; ?></li>
            <li>✓ Dossier uploads : <?php echo is_dir(UPLOAD_PATH) ? '✅ OK' : '❌ À créer'; ?></li>
        </ul>
    </div>
    
    <div style="background: #f0f0f0; padding: 20px; border-radius: 8px;">
        <h3>Infos système :</h3>
        <ul>
            <li>PHP Version : <?php echo phpversion(); ?></li>
            <li>Serveur : <?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
            <li>Racine projet : <?php echo ROOT_PATH; ?></li>
        </ul>
    </div>
</div>

<?php
?>
<a href="<?php echo APP_URL; ?>" style="display: inline-flex; align-items: center; gap: 8px; margin-top: 24px; color: #666; text-decoration: none;">← Retour à l'accueil</a>
</body>
</html>