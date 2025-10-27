<?php
/**
 * Test simple des produits
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST SIMPLE PRODUITS ===\n\n";

try {
    // Test 1: Vérifier les produits en base
    echo "1. PRODUITS EN BASE:\n";
    $products = \App\Models\Product::all();
    echo "   Total: " . $products->count() . "\n";
    
    foreach ($products as $product) {
        echo "   - {$product->name} (ID: {$product->id})\n";
        echo "     Prix: " . number_format($product->price, 0, ',', ' ') . " FCFA\n";
        echo "     Stock: {$product->stock}\n";
        echo "     Actif: " . ($product->is_active ? 'Oui' : 'Non') . "\n";
        echo "     Boutique: " . ($product->store_id ?? 'N/A') . "\n";
        echo "\n";
    }
    
    // Test 2: Vérifier les boutiques
    echo "2. BOUTIQUES:\n";
    $stores = \App\Models\Store::all();
    echo "   Total: " . $stores->count() . "\n";
    
    foreach ($stores as $store) {
        echo "   - {$store->name} (ID: {$store->id})\n";
        echo "     Statut: {$store->status}\n";
        echo "     Produits: " . $store->products()->count() . "\n";
        echo "\n";
    }
    
    // Test 3: Vérifier les utilisateurs vendeurs
    echo "3. VENDEURS:\n";
    $sellers = \App\Models\User::where('is_seller', true)->get();
    echo "   Total: " . $sellers->count() . "\n";
    
    foreach ($sellers as $seller) {
        echo "   - {$seller->nom} {$seller->prenoms} (ID: {$seller->id})\n";
        echo "     Email: {$seller->email}\n";
        echo "     Boutique: " . ($seller->store ? $seller->store->name : 'Aucune') . "\n";
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}

echo "TEST TERMINÉ\n";
?>
