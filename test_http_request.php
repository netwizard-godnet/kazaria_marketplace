<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test de requête HTTP simulée ===\n\n";

try {
    // Simuler exactement une requête HTTP vers la page d'accueil
    $request = \Illuminate\Http\Request::create('/', 'GET', [], [], [], [
        'HTTP_HOST' => '127.0.0.1:8000',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'HTTP_ACCEPT_LANGUAGE' => 'fr-FR,fr;q=0.9,en;q=0.8',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
        'HTTP_CONNECTION' => 'keep-alive',
        'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
    ]);
    
    echo "1. Requête HTTP simulée créée\n";
    echo "   - URL: " . $request->url() . "\n";
    echo "   - Method: " . $request->method() . "\n";
    echo "   - Headers: " . count($request->headers->all()) . "\n";
    
    // Traiter la requête
    echo "\n2. Traitement de la requête...\n";
    $response = $app->handle($request);
    
    echo "   - Status: " . $response->getStatusCode() . "\n";
    echo "   - Headers: " . count($response->headers->all()) . "\n";
    echo "   - Contenu: " . strlen($response->getContent()) . " caractères\n";
    
    if ($response->getStatusCode() === 200) {
        echo "   - ✅ Requête réussie\n";
        
        // Vérifier le contenu
        $content = $response->getContent();
        if (strpos($content, 'KAZARIA') !== false) {
            echo "   - ✅ Contenu KAZARIA trouvé\n";
        } else {
            echo "   - ⚠️  Contenu KAZARIA non trouvé\n";
        }
        
        if (strpos($content, 'Téléphones et tablettes') !== false) {
            echo "   - ✅ Catégories trouvées\n";
        } else {
            echo "   - ⚠️  Catégories non trouvées\n";
        }
        
    } else {
        echo "   - ❌ Erreur HTTP: " . $response->getStatusCode() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Type: " . get_class($e) . "\n";
    
    if (strpos($e->getMessage(), 'CategoryComposer') !== false) {
        echo "⚠️  Erreur CategoryComposer détectée !\n";
    }
    
    // Afficher la stack trace
    echo "\nStack trace:\n";
    $trace = $e->getTraceAsString();
    $lines = explode("\n", $trace);
    foreach (array_slice($lines, 0, 15) as $line) {
        echo "  " . $line . "\n";
    }
}

echo "\n=== Fin du test HTTP ===\n";
