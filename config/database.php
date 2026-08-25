<?php
// Fichier : config/database.php
// Configuration de la connexion à MySQL avec PDO

// Définition des constantes de connexion
define('DB_HOST', 'localhost');
define('DB_NAME', 'zeko_db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Fonction qui retourne une instance PDO
function getPDO() {
    try {
        // Construction du DSN (Data Source Name)
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        
        // Options PDO pour la sécurité et la performance
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // Gestion des erreurs par exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch en tableau associatif
            PDO::ATTR_EMULATE_PREPARES => false,              // Utiliser les vraies requêtes préparées
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4" // Encodage UTF-8
        ];
        
        // Création de l'objet PDO
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        
        return $pdo;
        
    } catch (PDOException $e) {
        // En cas d'erreur, afficher un message (en développement)
        die('Erreur de connexion à la base de données : ' . $e->getMessage());
    }
}

// Fonction de test pour vérifier que ça fonctionne
function testDatabaseConnection() {
    try {
        $pdo = getPDO();
        $stmt = $pdo->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Si le fichier est appelé directement, test
if (basename($_SERVER['PHP_SELF']) == 'database.php') {
    if (testDatabaseConnection()) {
        echo "✅ Connexion à la base de données réussie !";
    } else {
        echo "❌ Échec de la connexion à la base de données.";
    }
}
?>