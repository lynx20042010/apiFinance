<?php
// Désactiver toute mise en cache
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><title>Debug API Finance</title></head><body>";
echo "<h1>Diagnostic API Finance</h1>";

// 1. Test PHP
echo "<h2>✅ PHP fonctionne</h2>";
echo "<p>Version PHP: " . phpversion() . "</p>";

// 2. Test des extensions
echo "<h2>Extensions PHP</h2>";
$extensions = ['pdo', 'pdo_pgsql', 'mbstring', 'json'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>" . $ext . ": " . ($loaded ? "✅ Installé" : "❌ Manquant") . "</p>";
}

// 3. Test des variables d'environnement
echo "<h2>Variables d'environnement</h2>";
$env_vars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD', 'RENDER2_DB_HOST'];
foreach ($env_vars as $var) {
    $value = getenv($var);
    echo "<p>" . $var . ": " . ($value ? "✅ Défini (" . htmlspecialchars(substr($value, 0, 20)) . "...)" : "❌ Non défini") . "</p>";
}

// 4. Test des fichiers Laravel
echo "<h2>Fichiers Laravel</h2>";
$files = [
    'index.php',
    'artisan',
    'config/database.php',
    'app/Models/Transaction.php',
    'routes/api.php',
    'public/index.php'
];
foreach ($files as $file) {
    $exists = file_exists($file);
    echo "<p>" . $file . ": " . ($exists ? "✅ Existe" : "❌ Manquant") . "</p>";
}

// 5. Test de connexion base de données principale
echo "<h2>Test connexion base de données principale</h2>";
try {
    $host = getenv('DB_HOST');
    $dbname = getenv('DB_DATABASE');
    $user = getenv('DB_USERNAME');
    $pass = getenv('DB_PASSWORD');

    if (!$host || !$dbname || !$user) {
        throw new Exception("Variables d'environnement manquantes");
    }

    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->query("SELECT 1");
    echo "<p>✅ Connexion réussie à la base principale</p>";
} catch (Exception $e) {
    echo "<p>❌ Erreur base principale: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 6. Test de connexion base Render2
echo "<h2>Test connexion base Render2</h2>";
if (getenv('RENDER2_DB_HOST')) {
    try {
        $host2 = getenv('RENDER2_DB_HOST');
        $dbname2 = getenv('RENDER2_DB_DATABASE');
        $user2 = getenv('RENDER2_DB_USERNAME');
        $pass2 = getenv('RENDER2_DB_PASSWORD');

        $conn2 = new PDO("pgsql:host=$host2;dbname=$dbname2", $user2, $pass2);
        $conn2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt2 = $conn2->query("SELECT 1");
        echo "<p>✅ Connexion réussie à Render2</p>";
    } catch (Exception $e) {
        echo "<p>❌ Erreur Render2: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>ℹ️ Render2 non configuré</p>";
}

// 7. Test Laravel
echo "<h2>Test Laravel</h2>";
try {
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        echo "<p>✅ Autoloader Laravel chargé</p>";
    } else {
        echo "<p>❌ Autoloader Laravel manquant</p>";
    }

    if (file_exists('bootstrap/app.php')) {
        require_once 'bootstrap/app.php';
        echo "<p>✅ Bootstrap Laravel chargé</p>";
    } else {
        echo "<p>❌ Bootstrap Laravel manquant</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur Laravel: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// 8. Test des routes
echo "<h2>Test des routes</h2>";
try {
    if (file_exists('routes/api.php')) {
        echo "<p>✅ routes/api.php existe</p>";
        $content = file_get_contents('routes/api.php');
        if (strpos($content, 'comptes') !== false) {
            echo "<p>✅ Routes comptes trouvées</p>";
        } else {
            echo "<p>⚠️ Routes comptes non trouvées</p>";
        }
    } else {
        echo "<p>❌ routes/api.php manquant</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erreur routes: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
?>