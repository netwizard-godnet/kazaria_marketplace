<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::with('subcategories')->get();

        // Produits de base pour chaque catégorie
        $productsData = [
            'Téléphones et tablettes' => [
                ['name' => 'iPhone 15 Pro Max', 'price' => 850000, 'old_price' => 950000, 'brand' => 'Apple', 'is_featured' => true],
                ['name' => 'Samsung Galaxy S24 Ultra', 'price' => 750000, 'old_price' => 850000, 'brand' => 'Samsung', 'is_trending' => true],
                ['name' => 'iPad Pro 12.9"', 'price' => 650000, 'old_price' => 750000, 'brand' => 'Apple', 'is_new' => true],
                ['name' => 'Xiaomi Redmi Note 13', 'price' => 120000, 'old_price' => 150000, 'brand' => 'Xiaomi', 'is_best_offer' => true],
                ['name' => 'Samsung Galaxy Tab S9', 'price' => 450000, 'old_price' => 550000, 'brand' => 'Samsung', 'is_trending' => true],
                ['name' => 'OnePlus 12 Pro', 'price' => 380000, 'old_price' => 450000, 'brand' => 'OnePlus', 'is_featured' => true],
                ['name' => 'Realme 11 Pro', 'price' => 180000, 'old_price' => 220000, 'brand' => 'Realme', 'is_best_offer' => true],
                ['name' => 'Tecno Spark 20', 'price' => 85000, 'old_price' => 110000, 'brand' => 'Tecno', 'is_new' => true],
                ['name' => 'Infinix Note 30', 'price' => 95000, 'old_price' => 125000, 'brand' => 'Infinix', 'is_best_offer' => true],
                ['name' => 'Huawei MatePad Pro', 'price' => 320000, 'old_price' => 400000, 'brand' => 'Huawei', 'is_featured' => true],
            ],
            'TV et Electronique' => [
                ['name' => 'TV Samsung 65" QLED', 'price' => 850000, 'old_price' => 1000000, 'brand' => 'Samsung', 'is_featured' => true],
                ['name' => 'TV LG 55" OLED', 'price' => 650000, 'old_price' => 800000, 'brand' => 'LG', 'is_trending' => true],
                ['name' => 'TV Sony 50" 4K', 'price' => 450000, 'old_price' => 550000, 'brand' => 'Sony', 'is_new' => true],
                ['name' => 'Home Cinéma Samsung', 'price' => 180000, 'old_price' => 250000, 'brand' => 'Samsung', 'is_best_offer' => true],
                ['name' => 'Barre de son LG', 'price' => 120000, 'old_price' => 150000, 'brand' => 'LG', 'is_best_offer' => true],
                ['name' => 'TV TCL 43" Smart', 'price' => 220000, 'old_price' => 280000, 'brand' => 'TCL', 'is_new' => true],
                ['name' => 'Projecteur Epson', 'price' => 350000, 'old_price' => 420000, 'brand' => 'Epson', 'is_featured' => true],
                ['name' => 'Décodeur Canal+', 'price' => 45000, 'old_price' => 60000, 'brand' => 'Canal+', 'is_best_offer' => true],
                ['name' => 'TV Hisense 40"', 'price' => 180000, 'old_price' => 230000, 'brand' => 'Hisense', 'is_trending' => true],
                ['name' => 'Antenne parabolique', 'price' => 35000, 'old_price' => 50000, 'brand' => 'Générique', 'is_best_offer' => true],
            ],
            'Electroménager' => [
                ['name' => 'Réfrigérateur Samsung 450L', 'price' => 450000, 'old_price' => 550000, 'brand' => 'Samsung', 'is_featured' => true],
                ['name' => 'Cuisinière Bosch 4 feux', 'price' => 280000, 'old_price' => 350000, 'brand' => 'Bosch', 'is_trending' => true],
                ['name' => 'Micro-ondes LG 30L', 'price' => 85000, 'old_price' => 110000, 'brand' => 'LG', 'is_best_offer' => true],
                ['name' => 'Lave-linge Whirlpool 8kg', 'price' => 320000, 'old_price' => 400000, 'brand' => 'Whirlpool', 'is_new' => true],
                ['name' => 'Climatiseur Samsung 2CV', 'price' => 380000, 'old_price' => 450000, 'brand' => 'Samsung', 'is_featured' => true],
                ['name' => 'Ventilateur Binatone', 'price' => 45000, 'old_price' => 60000, 'brand' => 'Binatone', 'is_best_offer' => true],
                ['name' => 'Congélateur Hisense 200L', 'price' => 280000, 'old_price' => 350000, 'brand' => 'Hisense', 'is_trending' => true],
                ['name' => 'Bouilloire électrique', 'price' => 18000, 'old_price' => 25000, 'brand' => 'Générique', 'is_best_offer' => true],
                ['name' => 'Mixeur Moulinex', 'price' => 35000, 'old_price' => 45000, 'brand' => 'Moulinex', 'is_new' => true],
                ['name' => 'Fer à repasser Philips', 'price' => 28000, 'old_price' => 38000, 'brand' => 'Philips', 'is_best_offer' => true],
            ],
            'Ordinateurs et accessoires' => [
                ['name' => 'MacBook Pro M3 16"', 'price' => 1200000, 'old_price' => 1400000, 'brand' => 'Apple', 'is_featured' => true],
                ['name' => 'Dell XPS 15', 'price' => 850000, 'old_price' => 1000000, 'brand' => 'Dell', 'is_trending' => true],
                ['name' => 'HP Pavilion 14"', 'price' => 380000, 'old_price' => 450000, 'brand' => 'HP', 'is_best_offer' => true],
                ['name' => 'Lenovo ThinkPad', 'price' => 520000, 'old_price' => 650000, 'brand' => 'Lenovo', 'is_new' => true],
                ['name' => 'Asus ROG Gaming', 'price' => 980000, 'old_price' => 1150000, 'brand' => 'Asus', 'is_featured' => true],
                ['name' => 'Souris Logitech MX', 'price' => 35000, 'old_price' => 45000, 'brand' => 'Logitech', 'is_best_offer' => true],
                ['name' => 'Clavier mécanique', 'price' => 45000, 'old_price' => 60000, 'brand' => 'Corsair', 'is_trending' => true],
                ['name' => 'Imprimante HP LaserJet', 'price' => 180000, 'old_price' => 230000, 'brand' => 'HP', 'is_new' => true],
                ['name' => 'Webcam Logitech 4K', 'price' => 85000, 'old_price' => 110000, 'brand' => 'Logitech', 'is_best_offer' => true],
                ['name' => 'Disque dur externe 2TB', 'price' => 55000, 'old_price' => 75000, 'brand' => 'Seagate', 'is_best_offer' => true],
            ],
        ];

        foreach ($categories as $category) {
            if (isset($productsData[$category->name])) {
                $subcategories = $category->subcategories->pluck('id')->toArray();
                
                foreach ($productsData[$category->name] as $index => $productData) {
                    $discountPercentage = $productData['old_price'] 
                        ? round((($productData['old_price'] - $productData['price']) / $productData['old_price']) * 100)
                        : 0;
                    
                    // Générer un nom de fichier pour l'image du produit
                    $imageNumber = ($index + 1);
                    $categorySlug = \Illuminate\Support\Str::slug($category->name);
                    $productSlug = \Illuminate\Support\Str::slug($productData['name']);
                    
                    // Image principale dans le dossier produits
                    $mainImage = "images/produits/default.jpg"; // Vous pourrez remplacer par : "images/produits/{$categorySlug}-{$imageNumber}.jpg"
                    
                    // Images secondaires (gallery) - 4 images pour la galerie
                    $secondaryImages = [
                        "images/produits/default.jpg", // Vous pourrez remplacer par : "images/produits/{$categorySlug}-{$imageNumber}-1.jpg"
                        "images/produits/default.jpg", // Vous pourrez remplacer par : "images/produits/{$categorySlug}-{$imageNumber}-2.jpg"
                        "images/produits/default.jpg", // Vous pourrez remplacer par : "images/produits/{$categorySlug}-{$imageNumber}-3.jpg"
                        "images/produits/default.jpg", // Vous pourrez remplacer par : "images/produits/{$categorySlug}-{$imageNumber}-4.jpg"
                    ];
                    
                    Product::create([
                        'category_id' => $category->id,
                        'subcategory_id' => !empty($subcategories) ? $subcategories[array_rand($subcategories)] : null,
                        'name' => $productData['name'],
                        'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris. Ce produit de qualité supérieure vous offre les meilleures performances du marché.',
                        'price' => $productData['price'],
                        'old_price' => $productData['old_price'] ?? null,
                        'discount_percentage' => $discountPercentage,
                        'brand' => $productData['brand'] ?? null,
                        'stock' => rand(5, 50),
                        'image' => $mainImage,
                        'images' => $secondaryImages,
                        'rating' => 0, // Sera mis à jour quand de vrais avis seront ajoutés
                        'reviews_count' => 0, // Sera mis à jour quand de vrais avis seront ajoutés
                        'is_featured' => $productData['is_featured'] ?? false,
                        'is_trending' => $productData['is_trending'] ?? false,
                        'is_new' => $productData['is_new'] ?? false,
                        'is_best_offer' => $productData['is_best_offer'] ?? false,
                        'is_active' => true
                    ]);
                }
            }
        }
    }
}
