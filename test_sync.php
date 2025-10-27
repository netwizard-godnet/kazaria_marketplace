<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;

echo "=== Test de synchronisation Admin/Site ===\n\n";

// Test 1: Produits actifs sur le site
$activeProducts = Product::active()->get();
echo "1. Produits actifs sur le site: " . $activeProducts->count() . "\n";

// Test 2: Produits en stock sur le site
$inStockProducts = Product::active()->inStock()->get();
echo "2. Produits actifs ET en stock: " . $inStockProducts->count() . "\n";

// Test 3: Tous les produits (admin)
$allProducts = Product::all();
echo "3. Tous les produits (admin): " . $allProducts->count() . "\n";

// Test 4: Produits inactifs
$inactiveProducts = Product::where('is_active', false)->get();
echo "4. Produits inactifs: " . $inactiveProducts->count() . "\n";

// Test 5: Produits sans stock
$outOfStockProducts = Product::where('stock', '<=', 0)->get();
echo "5. Produits sans stock: " . $outOfStockProducts->count() . "\n";

echo "\n=== Détails des produits ===\n";

// Afficher quelques exemples
$sampleProducts = Product::take(3)->get();
foreach($sampleProducts as $product) {
    echo "\nProduit: {$product->name}\n";
    echo "- Actif: " . ($product->is_active ? 'Oui' : 'Non') . "\n";
    echo "- Stock: {$product->stock}\n";
    echo "- Visible sur site: " . ($product->is_active && $product->stock > 0 ? 'Oui' : 'Non') . "\n";
    echo "- Image: " . ($product->first_image_url ?? 'Aucune') . "\n";
}

echo "\n=== Test terminé ===\n";
