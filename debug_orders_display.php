<?php
/**
 * Debug complet de l'affichage des commandes
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG COMPLET DE L'AFFICHAGE DES COMMANDES ===\n\n";

// Test 1: Simuler un utilisateur vendeur connecté
echo "🔍 TEST 1: Simulation utilisateur vendeur connecté\n";
echo "==================================================\n";

$seller = \App\Models\User::where('is_seller', true)->first();
if (!$seller) {
    echo "❌ Aucun vendeur trouvé\n";
    exit;
}

\Illuminate\Support\Facades\Auth::login($seller);
echo "✅ Utilisateur connecté: {$seller->nom} {$seller->prenoms} (ID: {$seller->id})\n";

// Vérifier sa boutique
$store = $seller->store;
if ($store) {
    echo "✅ Boutique: {$store->name} (ID: {$store->id})\n";
    echo "📋 Produits dans la boutique: " . $store->products()->count() . "\n";
} else {
    echo "❌ Aucune boutique trouvée\n";
    exit;
}

echo "\n";

// Test 2: Appeler directement la méthode getOrders
echo "🔍 TEST 2: Appel direct de getOrders\n";
echo "====================================\n";

try {
    $request = \Illuminate\Http\Request::create('/store/api/orders', 'GET');
    $controller = new \App\Http\Controllers\Seller\OrderController();
    $response = $controller->getOrders($request);
    
    $data = json_decode($response->getContent(), true);
    echo "📋 Succès: " . ($data['success'] ? 'Oui' : 'Non') . "\n";
    echo "📋 Message: " . ($data['message'] ?? 'Aucun message') . "\n";
    echo "📋 Nombre de commandes: " . count($data['orders'] ?? []) . "\n";
    
    if (!empty($data['orders'])) {
        echo "📋 Première commande:\n";
        $firstOrder = $data['orders'][0];
        echo "   📋 ID: " . $firstOrder['id'] . "\n";
        echo "   📋 Numéro: " . $firstOrder['order_number'] . "\n";
        echo "   📋 Statut: " . $firstOrder['status'] . "\n";
        echo "   📋 Client: " . $firstOrder['user']['nom'] . " " . $firstOrder['user']['prenoms'] . "\n";
        echo "   📋 Articles: " . count($firstOrder['items']) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Vérifier la requête SQL générée
echo "🔍 TEST 3: Vérification de la requête SQL\n";
echo "==========================================\n";

try {
    // Activer le logging des requêtes
    \Illuminate\Support\Facades\DB::enableQueryLog();
    
    // Exécuter la même requête que dans le contrôleur
    $storeProducts = $store->products()->pluck('id')->toArray();
    echo "📋 IDs des produits de la boutique: " . implode(', ', $storeProducts) . "\n";
    
    if (!empty($storeProducts)) {
        $orders = \App\Models\Order::query()
            ->whereHas('orderItems.product', function($q) use ($store) {
                $q->where('store_id', $store->id);
            })
            ->with(['orderItems.product' => function($q) use ($store) {
                $q->where('store_id', $store->id);
            }, 'user'])
            ->get();
        
        echo "📋 Commandes trouvées: " . $orders->count() . "\n";
        
        foreach ($orders as $order) {
            echo "   📋 Commande: {$order->order_number} (Statut: {$order->status})\n";
            echo "      📋 Client: {$order->user->nom} {$order->user->prenoms}\n";
            echo "      📋 Articles: " . $order->orderItems->count() . "\n";
        }
    } else {
        echo "❌ Aucun produit dans la boutique\n";
    }
    
    // Afficher les requêtes SQL
    $queries = \Illuminate\Support\Facades\DB::getQueryLog();
    echo "\n📋 Requêtes SQL exécutées:\n";
    foreach ($queries as $query) {
        echo "   📋 " . $query['query'] . "\n";
        echo "      📋 Bindings: " . implode(', ', $query['bindings']) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Vérifier les relations dans la base de données
echo "🔍 TEST 4: Vérification des relations en base\n";
echo "=============================================\n";

try {
    // Vérifier les produits de la boutique
    $storeProducts = \App\Models\Product::where('store_id', $store->id)->get();
    echo "📋 Produits de la boutique: " . $storeProducts->count() . "\n";
    
    foreach ($storeProducts as $product) {
        echo "   📋 Produit: {$product->nom} (ID: {$product->id})\n";
        
        // Vérifier les commandes pour ce produit
        $productOrders = \App\Models\Order::whereHas('orderItems', function($q) use ($product) {
            $q->where('product_id', $product->id);
        })->count();
        
        echo "      📋 Commandes pour ce produit: {$productOrders}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Simuler une requête HTTP complète
echo "🔍 TEST 5: Simulation requête HTTP complète\n";
echo "===========================================\n";

try {
    $request = \Illuminate\Http\Request::create('/store/api/orders', 'GET');
    $request->setLaravelSession(app('session.store'));
    
    $response = $app->handle($request);
    echo "📋 Code de réponse: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() === 200) {
        $content = $response->getContent();
        $data = json_decode($content, true);
        echo "📋 Données retournées: " . ($data['success'] ? 'Succès' : 'Échec') . "\n";
        echo "📋 Nombre de commandes: " . count($data['orders'] ?? []) . "\n";
    } else {
        echo "📋 Réponse: " . $response->getContent() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

\Illuminate\Support\Facades\Auth::logout();
echo "\nDéconnexion de l'utilisateur.\n";

echo "\n🎯 RÉSULTAT DU DEBUG :\n";
echo "======================\n";
echo "✅ Utilisateur vendeur simulé\n";
echo "✅ Méthode getOrders testée\n";
echo "✅ Requêtes SQL analysées\n";
echo "✅ Relations vérifiées\n";
echo "✅ Requête HTTP simulée\n";
?>
