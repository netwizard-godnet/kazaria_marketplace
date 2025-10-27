<?php
/**
 * Vérification de la structure de la base de données
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== VÉRIFICATION STRUCTURE BASE DE DONNÉES ===\n\n";

// Test 1: Vérifier la table orders
echo "🔍 TEST 1: Table orders\n";
echo "=======================\n";

try {
    $orders = \Illuminate\Support\Facades\DB::select("SELECT * FROM orders LIMIT 3");
    echo "📋 Nombre de commandes: " . count($orders) . "\n";
    
    foreach ($orders as $order) {
        echo "   📋 Commande: {$order->order_number} (ID: {$order->id}, Statut: {$order->status})\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Vérifier la table order_items
echo "🔍 TEST 2: Table order_items\n";
echo "============================\n";

try {
    $orderItems = \Illuminate\Support\Facades\DB::select("SELECT * FROM order_items LIMIT 5");
    echo "📋 Nombre d'articles: " . count($orderItems) . "\n";
    
    foreach ($orderItems as $item) {
        echo "   📋 Article: Commande {$item->order_id}, Produit {$item->product_id}, Quantité {$item->quantity}\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Vérifier la table products
echo "🔍 TEST 3: Table products\n";
echo "========================\n";

try {
    $products = \Illuminate\Support\Facades\DB::select("SELECT id, nom, store_id FROM products WHERE store_id IS NOT NULL LIMIT 5");
    echo "📋 Produits avec boutique: " . count($products) . "\n";
    
    foreach ($products as $product) {
        echo "   📋 Produit: {$product->nom} (ID: {$product->id}, Boutique: {$product->store_id})\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Vérifier les relations
echo "🔍 TEST 4: Relations entre tables\n";
echo "==================================\n";

try {
    // Requête pour trouver les commandes avec des produits d'une boutique spécifique
    $storeId = 4; // Samsung
    echo "📋 Recherche des commandes pour la boutique ID: {$storeId}\n";
    
    $sql = "
        SELECT DISTINCT o.id, o.order_number, o.status, o.created_at
        FROM orders o
        INNER JOIN order_items oi ON o.id = oi.order_id
        INNER JOIN products p ON oi.product_id = p.id
        WHERE p.store_id = ?
        ORDER BY o.created_at DESC
    ";
    
    $orders = \Illuminate\Support\Facades\DB::select($sql, [$storeId]);
    echo "📋 Commandes trouvées: " . count($orders) . "\n";
    
    foreach ($orders as $order) {
        echo "   📋 Commande: {$order->order_number} (Statut: {$order->status})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Vérifier la requête exacte du contrôleur
echo "🔍 TEST 5: Requête exacte du contrôleur\n";
echo "=======================================\n";

try {
    $storeId = 4; // Samsung
    
    // Simuler la requête du contrôleur
    $orders = \App\Models\Order::query()
        ->whereHas('orderItems.product', function($q) use ($storeId) {
            $q->where('store_id', $storeId);
        })
        ->with(['orderItems.product' => function($q) use ($storeId) {
            $q->where('store_id', $storeId);
        }, 'user'])
        ->get();
    
    echo "📋 Commandes via Eloquent: " . $orders->count() . "\n";
    
    foreach ($orders as $order) {
        echo "   📋 Commande: {$order->order_number} (Statut: {$order->status})\n";
        echo "      📋 Client: {$order->user->nom} {$order->user->prenoms}\n";
        echo "      📋 Articles: " . $order->orderItems->count() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🎯 RÉSULTAT DE LA VÉRIFICATION :\n";
echo "=================================\n";
echo "✅ Structure de la base vérifiée\n";
echo "✅ Relations analysées\n";
echo "✅ Requête du contrôleur testée\n";
?>
