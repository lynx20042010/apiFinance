<?php
header('Content-Type: application/json');

// Activer l'affichage des erreurs temporairement
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'extensions' => [
        'pdo' => extension_loaded('pdo'),
        'pdo_pgsql' => extension_loaded('pdo_pgsql'),
        'mbstring' => extension_loaded('mbstring'),
    ],
    'environment' => [
        'DB_HOST' => getenv('DB_HOST') ?: 'not_set',
        'DB_DATABASE' => getenv('DB_DATABASE') ?: 'not_set',
        'DB_USERNAME' => getenv('DB_USERNAME') ?: 'not_set',
        'RENDER2_DB_HOST' => getenv('RENDER2_DB_HOST') ?: 'not_set',
        'APP_ENV' => getenv('APP_ENV') ?: 'not_set',
        'APP_KEY' => getenv('APP_KEY') ? 'set' : 'not_set',
    ],
    'files' => [
        'index.php' => file_exists('index.php') ? 'exists' : 'missing',
        'config/Database.php' => file_exists('config/Database.php') ? 'exists' : 'missing',
        'config/database.php' => file_exists('config/database.php') ? 'exists' : 'missing',
        '.env' => file_exists('.env') ? 'exists' : 'missing',
    ],
    'database_tests' => []
];

// Test de connexion à la base principale
try {
    $pdo = new PDO(
        "pgsql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT 1");
    $health['database_tests']['render_main'] = 'connected';
} catch (Exception $e) {
    $health['database_tests']['render_main'] = 'error: ' . $e->getMessage();
}

// Test de connexion à la base Render2 si configurée
if (getenv('RENDER2_DB_HOST')) {
    try {
        $pdo2 = new PDO(
            "pgsql:host=" . getenv('RENDER2_DB_HOST') . ";dbname=" . getenv('RENDER2_DB_DATABASE'),
            getenv('RENDER2_DB_USERNAME'),
            getenv('RENDER2_DB_PASSWORD')
        );
        $pdo2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt2 = $pdo2->query("SELECT 1");
        $health['database_tests']['render2'] = 'connected';
    } catch (Exception $e) {
        $health['database_tests']['render2'] = 'error: ' . $e->getMessage();
    }
}

echo json_encode($health, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>