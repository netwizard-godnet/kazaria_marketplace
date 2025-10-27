<?php
/**
 * Test automatisé des endpoints API pour la modification de produit
 */

echo "🧪 TEST AUTOMATISÉ DES ENDPOINTS API\n";
echo "====================================\n\n";

// Configuration
$baseUrl = 'http://127.0.0.1:8000';
$testProductId = 1; // ID du produit à tester

// Fonction pour faire des requêtes HTTP
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// Test 1: Vérifier que le serveur répond
echo "1️⃣ Test de connectivité du serveur...\n";
$response = makeRequest($baseUrl);
if ($response['http_code'] === 200) {
    echo "✅ Serveur accessible\n";
} else {
    echo "❌ Serveur non accessible (Code: {$response['http_code']})\n";
    exit(1);
}

echo "\n";

// Test 2: Vérifier l'endpoint de récupération de produit
echo "2️⃣ Test de récupération de produit...\n";
$productUrl = "$baseUrl/store/api/products/$testProductId";
$response = makeRequest($productUrl, 'GET', null, [
    'Accept: application/json',
    'Content-Type: application/json'
]);

echo "URL: $productUrl\n";
echo "Code HTTP: {$response['http_code']}\n";

if ($response['http_code'] === 200) {
    $data = json_decode($response['response'], true);
    if ($data && isset($data['success']) && $data['success']) {
        echo "✅ Produit récupéré avec succès\n";
        echo "📦 Données: " . json_encode($data['product'] ?? [], JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Erreur dans la réponse: " . $response['response'] . "\n";
    }
} else {
    echo "❌ Erreur HTTP: " . $response['response'] . "\n";
}

echo "\n";

// Test 3: Vérifier l'endpoint de mise à jour (sans authentification pour tester l'erreur)
echo "3️⃣ Test de mise à jour de produit (sans authentification)...\n";
$updateData = [
    'name' => 'Produit Test Modifié',
    'description' => 'Description modifiée pour test',
    'price' => 100000,
    'stock' => 10,
    'brand' => 'Test Brand',
    'model' => 'Test Model',
    'warranty' => '1 an'
];

$response = makeRequest($productUrl, 'PUT', json_encode($updateData), [
    'Accept: application/json',
    'Content-Type: application/json'
]);

echo "Code HTTP: {$response['http_code']}\n";
if ($response['http_code'] === 401 || $response['http_code'] === 403) {
    echo "✅ Authentification requise (comportement attendu)\n";
} else {
    echo "⚠️ Réponse inattendue: " . $response['response'] . "\n";
}

echo "\n";

// Test 4: Vérifier les routes dans le fichier web.php
echo "4️⃣ Test des routes définies...\n";
$routesFile = 'routes/web.php';
if (file_exists($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    
    $expectedRoutes = [
        'store/api/products/{id}',
        'store/api/products/{id}',
        'store/api/products/{id}'
    ];
    
    foreach ($expectedRoutes as $route) {
        if (strpos($routesContent, $route) !== false) {
            echo "✅ Route $route trouvée\n";
        } else {
            echo "❌ Route $route manquante\n";
        }
    }
} else {
    echo "❌ Fichier routes/web.php non trouvé\n";
}

echo "\n";

// Test 5: Vérifier le contrôleur
echo "5️⃣ Test du contrôleur ProductController...\n";
$controllerFile = 'app/Http/Controllers/Seller/ProductController.php';
if (file_exists($controllerFile)) {
    $controllerContent = file_get_contents($controllerFile);
    
    $methods = [
        'getProduct',
        'update',
        'delete'
    ];
    
    foreach ($methods as $method) {
        if (strpos($controllerContent, "function $method") !== false) {
            echo "✅ Méthode $method trouvée\n";
        } else {
            echo "❌ Méthode $method manquante\n";
        }
    }
    
    // Vérifier la validation
    if (strpos($controllerContent, 'validate(') !== false) {
        echo "✅ Validation présente\n";
    } else {
        echo "❌ Validation manquante\n";
    }
    
    // Vérifier les logs de debug
    if (strpos($controllerContent, 'Log::info') !== false) {
        echo "✅ Logs de debug présents\n";
    } else {
        echo "❌ Logs de debug manquants\n";
    }
} else {
    echo "❌ Fichier ProductController.php non trouvé\n";
}

echo "\n";

// Test 6: Vérifier la base de données
echo "6️⃣ Test de la base de données...\n";
try {
    require_once 'vendor/autoload.php';
    
    // Configuration Laravel
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    // Vérifier la table products
    $productsCount = \DB::table('products')->count();
    echo "✅ Table products: $productsCount produits\n";
    
    // Vérifier la structure de la table
    $columns = \DB::select("PRAGMA table_info(products)");
    $requiredColumns = ['id', 'name', 'description', 'price', 'stock', 'store_id'];
    
    foreach ($requiredColumns as $column) {
        $found = false;
        foreach ($columns as $col) {
            if ($col->name === $column) {
                $found = true;
                break;
            }
        }
        if ($found) {
            echo "✅ Colonne $column présente\n";
        } else {
            echo "❌ Colonne $column manquante\n";
        }
    }
    
    // Vérifier les produits avec store_id
    $productsWithStore = \DB::table('products')
        ->whereNotNull('store_id')
        ->count();
    echo "✅ Produits avec store_id: $productsWithStore\n";
    
} catch (Exception $e) {
    echo "❌ Erreur base de données: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 7: Vérifier les middlewares
echo "7️⃣ Test des middlewares...\n";
$middlewareFile = 'bootstrap/app.php';
if (file_exists($middlewareFile)) {
    $middlewareContent = file_get_contents($middlewareFile);
    
    $middlewares = [
        'seller',
        'hybrid.auth',
        'web'
    ];
    
    foreach ($middlewares as $middleware) {
        if (strpos($middlewareContent, $middleware) !== false) {
            echo "✅ Middleware $middleware trouvé\n";
        } else {
            echo "❌ Middleware $middleware manquant\n";
        }
    }
} else {
    echo "❌ Fichier bootstrap/app.php non trouvé\n";
}

echo "\n";

// Test 8: Vérifier les logs récents
echo "8️⃣ Test des logs récents...\n";
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $logSize = filesize($logFile);
    echo "✅ Fichier de log accessible (taille: " . number_format($logSize) . " bytes)\n";
    
    // Chercher les erreurs récentes
    $errorCount = substr_count($logContent, 'ERROR');
    $warningCount = substr_count($logContent, 'WARNING');
    
    echo "📊 Erreurs dans les logs: $errorCount\n";
    echo "📊 Avertissements dans les logs: $warningCount\n";
    
    if ($errorCount > 0) {
        echo "⚠️ Des erreurs sont présentes dans les logs\n";
    } else {
        echo "✅ Aucune erreur récente dans les logs\n";
    }
} else {
    echo "❌ Fichier de log non trouvé\n";
}

echo "\n";

// Test 9: Résumé des tests
echo "9️⃣ RÉSUMÉ DES TESTS\n";
echo "===================\n";

$totalTests = 9;
$passedTests = 0;

// Compter les tests réussis
if (file_exists($routesFile) && strpos(file_get_contents($routesFile), 'store/api/products') !== false) $passedTests++;
if (file_exists($controllerFile) && strpos(file_get_contents($controllerFile), 'function update') !== false) $passedTests++;
if (file_exists($middlewareFile) && strpos(file_get_contents($middlewareFile), 'seller') !== false) $passedTests++;
if (file_exists($logFile)) $passedTests++;

echo "Tests réussis: $passedTests/$totalTests\n";

if ($passedTests >= 7) {
    echo "✅ SYSTÈME PRÊT POUR LES TESTS MANUELS\n";
    echo "📋 Prochaines étapes:\n";
    echo "   1. Ouvrir test_manual_product_edit.html\n";
    echo "   2. Suivre les instructions de test\n";
    echo "   3. Vérifier le dashboard vendeur\n";
    echo "   4. Tester la modification d'un produit\n";
} else {
    echo "❌ SYSTÈME NON PRÊT - VÉRIFIER LES ERREURS\n";
}

echo "\n🎯 Test automatisé terminé!\n";
?>
