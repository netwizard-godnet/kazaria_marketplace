<?php
/**
 * Debug des données du formulaire
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG DONNÉES FORMULAIRE ===\n\n";

try {
    // Simuler une requête de test
    $product = \App\Models\Product::where('name', 'Galaxy S25 Ultra')->first();
    
    if (!$product) {
        echo "❌ Produit non trouvé\n";
        exit;
    }
    
    echo "📋 PRODUIT EN BASE :\n";
    echo "📋 Nom: {$product->name}\n";
    echo "📋 Description: " . substr($product->description, 0, 50) . "...\n";
    echo "📋 Prix: {$product->price}\n";
    echo "📋 Old Price: " . ($product->old_price ?: 'Aucun') . "\n";
    echo "📋 Stock: {$product->stock}\n";
    echo "📋 Brand: " . ($product->brand ?: 'Aucun') . "\n";
    echo "📋 Model: " . ($product->model ?: 'Aucun') . "\n";
    echo "📋 Warranty: " . ($product->warranty ?: 'Aucun') . "\n";
    
    // Vérifier les règles de validation
    echo "\n📋 RÈGLES DE VALIDATION :\n";
    echo "========================\n";
    echo "📋 name: required|string|max:255\n";
    echo "📋 description: required|string|min:50\n";
    echo "📋 price: required|numeric|min:0\n";
    echo "📋 promo_price: nullable|numeric|min:0\n";
    echo "📋 stock: required|integer|min:0\n";
    echo "📋 discount: nullable|numeric|min:0|max:100\n";
    echo "📋 brand: nullable|string|max:100\n";
    echo "📋 model: nullable|string|max:100\n";
    echo "📋 warranty: nullable|string|max:100\n";
    
    // Vérifier la longueur de la description
    echo "\n📋 VÉRIFICATION DESCRIPTION :\n";
    echo "============================\n";
    echo "📋 Longueur: " . strlen($product->description) . " caractères\n";
    echo "📋 Minimum requis: 50 caractères\n";
    echo "📋 Valide: " . (strlen($product->description) >= 50 ? "✅ Oui" : "❌ Non") . "\n";
    
    if (strlen($product->description) < 50) {
        echo "⚠️ La description est trop courte !\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n🎯 DEBUG TERMINÉ\n";
?>
