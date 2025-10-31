<?php
// Prueba de dependencias y configuración

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

echo "=== PRUEBA DE DEPENDENCIAS ===\n\n";

// 1. Verificar PHP básico
echo "1. PHP Version: " . PHP_VERSION . "\n";

// 2. Verificar que puede cargar archivos
if (file_exists('.env')) {
    echo "2. Archivo .env: ENCONTRADO\n";
} else {
    echo "2. Archivo .env: NO ENCONTRADO\n";
}

// 3. Verificar autoloader de Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "3. Vendor autoload: ENCONTRADO\n";
    try {
        require_once __DIR__ . '/../vendor/autoload.php';
        echo "4. Autoload cargado: OK\n";
    } catch (Exception $e) {
        echo "4. Error cargando autoload: " . $e->getMessage() . "\n";
    }
} else {
    echo "3. Vendor autoload: NO ENCONTRADO\n";
}

// 5. Verificar Firebase JWT
try {
    if (class_exists('Firebase\JWT\JWT')) {
        echo "5. Firebase JWT: DISPONIBLE\n";
    } else {
        echo "5. Firebase JWT: NO DISPONIBLE\n";
    }
} catch (Exception $e) {
    echo "5. Error verificando JWT: " . $e->getMessage() . "\n";
}

// 6. Verificar PDO MySQL
if (extension_loaded('pdo_mysql')) {
    echo "6. PDO MySQL: DISPONIBLE\n";
} else {
    echo "6. PDO MySQL: NO DISPONIBLE\n";
}

// 7. Verificar variables de entorno
echo "7. APP_KEY existe: " . (getenv('APP_KEY') ? 'SÍ' : 'NO') . "\n";
echo "8. DB_HOST: " . (getenv('DB_HOST') ? getenv('DB_HOST') : 'NO DEFINIDO') . "\n";

echo "\n=== FIN PRUEBA ===";
?>