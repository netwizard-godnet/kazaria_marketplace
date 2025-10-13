<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrer les catégories existantes vers la nouvelle structure
        $products = DB::table('products')->whereNotNull('category_id')->get();
        
        foreach ($products as $product) {
            // Insérer dans product_categories
            DB::table('product_categories')->insert([
                'product_id' => $product->id,
                'category_id' => $product->category_id,
                'is_primary' => true, // La catégorie existante devient la principale
                'order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Insérer dans product_subcategories si sous-catégorie existe
            if ($product->subcategory_id) {
                DB::table('product_subcategories')->insert([
                    'product_id' => $product->id,
                    'subcategory_id' => $product->subcategory_id,
                    'is_primary' => true, // La sous-catégorie existante devient la principale
                    'order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        echo "Migration des catégories terminée. " . count($products) . " produits migrés.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Vider les tables de liaison
        DB::table('product_subcategories')->truncate();
        DB::table('product_categories')->truncate();
    }
};
