<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;

class ProductMultipleCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Récupérer les catégories
        $telephones = Category::where('slug', 'telephones-et-tablettes')->first();
        $tv = Category::where('slug', 'tv-et-electronique')->first();
        $electro = Category::where('slug', 'electromenager')->first();
        $ordinateurs = Category::where('slug', 'ordinateurs-et-accessoires')->first();

        // Récupérer quelques sous-catégories
        $smartphones = Subcategory::where('slug', 'smartphones')->first();
        $tablettes = Subcategory::where('slug', 'tablettes')->first();
        $laptops = Subcategory::where('slug', 'ordinateurs-portables')->first();
        $desktops = Subcategory::where('slug', 'ordinateurs-de-bureau')->first();
        $ecrans = Subcategory::where('slug', 'ecrans')->first();
        $gaming = Subcategory::where('slug', 'consoles-de-jeux')->first();

        // Exemples de produits avec plusieurs catégories
        $products = [
            [
                'name' => 'iPhone 15 Pro Max',
                'categories' => [$telephones->id], // Catégorie principale
                'subcategories' => [$smartphones->id],
                'additional_categories' => [$tv->id], // Catégorie supplémentaire (électronique)
            ],
            [
                'name' => 'MacBook Pro M3',
                'categories' => [$ordinateurs->id],
                'subcategories' => [$laptops->id],
                'additional_categories' => [$tv->id], // Électronique aussi
            ],
            [
                'name' => 'iPad Pro 12.9"',
                'categories' => [$telephones->id], // Tablettes
                'subcategories' => [$tablettes->id],
                'additional_categories' => [$tv->id], // Électronique
            ],
            [
                'name' => 'PC Gaming Alienware',
                'categories' => [$ordinateurs->id],
                'subcategories' => [$desktops->id, $gaming->id], // Plusieurs sous-catégories
                'additional_categories' => [$tv->id], // Électronique
            ],
            [
                'name' => 'Écran Gaming 27"',
                'categories' => [$ordinateurs->id],
                'subcategories' => [$ecrans->id, $gaming->id], // Écran ET gaming
                'additional_categories' => [$tv->id], // Électronique
            ],
            [
                'name' => 'Samsung Galaxy Tab S9',
                'categories' => [$telephones->id],
                'subcategories' => [$tablettes->id],
                'additional_categories' => [$tv->id], // Électronique
            ],
        ];

        foreach ($products as $productData) {
            // Trouver le produit existant
            $product = Product::where('name', $productData['name'])->first();
            
            if ($product) {
                // Vider les catégories existantes
                $product->categories()->detach();
                $product->subcategories()->detach();
                
                // Ajouter les nouvelles catégories
                foreach ($productData['categories'] as $index => $categoryId) {
                    $isPrimary = $index === 0; // Première catégorie = principale
                    $product->addCategory($categoryId, $isPrimary, $index);
                }
                
                foreach ($productData['additional_categories'] as $categoryId) {
                    $product->addCategory($categoryId, false, 99); // Non principale
                }
                
                // Ajouter les sous-catégories
                foreach ($productData['subcategories'] as $index => $subcategoryId) {
                    $isPrimary = $index === 0; // Première sous-catégorie = principale
                    $product->addSubcategory($subcategoryId, $isPrimary, $index);
                }
                
                echo "Produit '{$product->name}' mis à jour avec catégories multiples.\n";
            }
        }
    }
}
