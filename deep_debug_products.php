<?php
/**
 * Diagnostic approfondi des produits non affichés
 */

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DIAGNOSTIC APPROFONDI PRODUITS ===\n\n";

// 1. Vérifier la connexion à la base de données
echo "🔍 1. CONNEXION BASE DE DONNÉES\n";
echo "===============================\n";

try {
    $connection = \DB::connection()->getPdo();
    echo "✅ Connexion à la base de données réussie\n";
    echo "📋 Driver: " . \DB::connection()->getDriverName() . "\n";
    echo "📋 Base: " . \DB::connection()->getDatabaseName() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
    exit;
}

echo "\n";

// 2. Vérifier les utilisateurs vendeurs
echo "🔍 2. UTILISATEURS VENDEURS\n";
echo "============================\n";

try {
    $sellers = \App\Models\User::where('is_seller', true)->get();
    echo "📋 Vendeurs trouvés: " . $sellers->count() . "\n";
    
    foreach ($sellers as $seller) {
        echo "   📋 {$seller->nom} {$seller->prenoms} (ID: {$seller->id})\n";
        echo "      📋 Email: {$seller->email}\n";
        echo "      📋 Boutique: " . ($seller->store ? $seller->store->name : 'Aucune') . "\n";
        
        if ($seller->store) {
            $productCount = $seller->store->products()->count();
            echo "      📋 Produits: {$productCount}\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// 3. Vérifier les boutiques
echo "🔍 3. BOUTIQUES\n";
echo "================\n";

try {
    $stores = \App\Models\Store::with('products')->get();
    echo "📋 Boutiques trouvées: " . $stores->count() . "\n";
    
    foreach ($stores as $store) {
        echo "   📋 {$store->name} (ID: {$store->id})\n";
        echo "      📋 Statut: {$store->status}\n";
        echo "      📋 Produits: " . $store->products()->count() . "\n";
        
        $products = $store->products()->limit(3)->get();
        foreach ($products as $product) {
            echo "         📋 {$product->name} (ID: {$product->id})\n";
            echo "            📋 Prix: " . number_format($product->price, 0, ',', ' ') . " FCFA\n";
            echo "            📋 Stock: {$product->stock}\n";
            echo "            📋 Actif: " . ($product->is_active ? 'Oui' : 'Non') . "\n";
            echo "            📋 Image: " . ($product->image ? 'Oui' : 'Non') . "\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Simuler l'appel au contrôleur
echo "🔍 4. SIMULATION CONTRÔLEUR\n";
echo "============================\n";

try {
    // Simuler un utilisateur connecté
    $user = \App\Models\User::where('is_seller', true)->first();
    
    if (!$user) {
        echo "❌ Aucun utilisateur vendeur trouvé\n";
        exit;
    }
    
    echo "📋 Utilisateur simulé: {$user->nom} {$user->prenoms}\n";
    
    // Simuler auth()->user()
    \Auth::login($user);
    
    $store = $user->store;
    if (!$store) {
        echo "❌ L'utilisateur n'a pas de boutique\n";
        exit;
    }
    
    echo "📋 Boutique: {$store->name}\n";
    echo "📋 Statut boutique: {$store->status}\n";
    
    // Récupérer les produits comme dans le contrôleur
    $products = $store->products()
        ->with(['category', 'subcategory'])
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "📋 Produits chargés: " . $products->count() . "\n";
    
    foreach ($products as $product) {
        echo "   📋 {$product->name} (ID: {$product->id})\n";
        echo "      📋 Prix: " . number_format($product->price, 0, ',', ' ') . " FCFA\n";
        echo "      📋 Stock: {$product->stock}\n";
        echo "      📋 Actif: " . ($product->is_active ? 'Oui' : 'Non') . "\n";
        echo "      📋 Catégorie: " . ($product->category->name ?? 'N/A') . "\n";
        echo "      📋 Sous-catégorie: " . ($product->subcategory->name ?? 'N/A') . "\n";
        echo "      📋 Image: " . ($product->image ? 'Oui (' . $product->image . ')' : 'Non') . "\n";
        echo "      📋 Images: " . (is_array($product->images) ? count($product->images) . ' images' : 'Aucune') . "\n";
        echo "      📋 Marque: '{$product->brand}'\n";
        echo "      📋 Modèle: '{$product->model}'\n";
        echo "      📋 Garantie: '{$product->warranty}'\n";
        echo "      📋 Description: " . substr($product->description, 0, 50) . "...\n";
        echo "      📋 Tags: " . (is_array($product->tags) ? implode(', ', $product->tags) : $product->tags) . "\n";
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "📋 Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// 5. Vérifier les catégories
echo "🔍 5. CATÉGORIES\n";
echo "=================\n";

try {
    $categories = \App\Models\Category::with('subcategories')->get();
    echo "📋 Catégories trouvées: " . $categories->count() . "\n";
    
    foreach ($categories as $category) {
        echo "   📋 {$category->name} (ID: {$category->id})\n";
        echo "      📋 Sous-catégories: " . $category->subcategories->count() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// 6. Vérifier les relations
echo "🔍 6. RELATIONS\n";
echo "================\n";

try {
    $product = \App\Models\Product::with(['category', 'subcategory', 'store'])->first();
    
    if ($product) {
        echo "📋 Produit test: {$product->name}\n";
        echo "   📋 Catégorie: " . ($product->category ? $product->category->name : 'N/A') . "\n";
        echo "   📋 Sous-catégorie: " . ($product->subcategory ? $product->subcategory->name : 'N/A') . "\n";
        echo "   📋 Boutique: " . ($product->store ? $product->store->name : 'N/A') . "\n";
    } else {
        echo "❌ Aucun produit trouvé\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

// 7. Vérifier les images
echo "🔍 7. IMAGES\n";
echo "=============\n";

try {
    $product = \App\Models\Product::first();
    
    if ($product) {
        echo "📋 Produit: {$product->name}\n";
        echo "   📋 Image: " . ($product->image ? $product->image : 'Aucune') . "\n";
        echo "   📋 Images: " . (is_array($product->images) ? json_encode($product->images) : 'Aucune') . "\n";
        
        if ($product->image) {
            $imagePath = storage_path('app/public/' . $product->image);
            echo "   📋 Chemin image: {$imagePath}\n";
            echo "   📋 Image existe: " . (file_exists($imagePath) ? 'Oui' : 'Non') . "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n";

echo "🎯 DIAGNOSTIC APPROFONDI TERMINÉ\n";
echo "================================\n";
echo "📋 RÉSUMÉ :\n";
echo "1. Connexion à la base de données\n";
echo "2. Utilisateurs vendeurs\n";
echo "3. Boutiques et leurs produits\n";
echo "4. Simulation du contrôleur\n";
echo "5. Catégories et sous-catégories\n";
echo "6. Relations des produits\n";
echo "7. Images des produits\n";
echo "\n🔧 PROCHAINES ÉTAPES :\n";
echo "1. Identifier où les données se perdent\n";
echo "2. Vérifier le rendu de la vue\n";
echo "3. Tester l'accès au dashboard\n";
echo "4. Vérifier les middlewares\n";
echo "5. Corriger les problèmes identifiés\n";
?>
