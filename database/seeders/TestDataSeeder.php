<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Store;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur de test
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'nom' => 'Dupont',
                'prenoms' => 'Jean',
                'telephone' => '+237 123 456 789',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Créer une catégorie de test
        $category = Category::firstOrCreate(
            ['name' => 'Électronique'],
            [
                'slug' => 'electronique',
                'description' => 'Catégorie électronique',
                'status' => 'active',
                'order' => 1,
            ]
        );

        // Créer une sous-catégorie
        $subcategory = Category::firstOrCreate(
            ['name' => 'Smartphones'],
            [
                'slug' => 'smartphones',
                'description' => 'Smartphones',
                'status' => 'active',
                'order' => 1,
                'parent_id' => $category->id,
            ]
        );

        // Créer un magasin de test
        $store = Store::firstOrCreate(
            ['name' => 'Magasin Test'],
            [
                'slug' => 'magasin-test',
                'description' => 'Magasin de test',
                'status' => 'active',
                'user_id' => $user->id,
            ]
        );

        // Créer des produits de test
        $products = [];
        for ($i = 1; $i <= 5; $i++) {
            $product = Product::firstOrCreate(
                ['sku' => "TEST-{$i}"],
                [
                    'name' => "Produit Test {$i}",
                    'description' => "Description du produit test {$i}",
                    'price' => rand(5000, 50000),
                    'quantity' => rand(10, 100),
                    'status' => 'active',
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategory->id,
                    'store_id' => $store->id,
                    'images' => ['test-image.jpg'],
                ]
            );
            $products[] = $product;
        }

        // Créer des commandes de test
        $statuses = ['pending', 'paid', 'processing', 'shipped', 'delivered'];
        
        for ($i = 1; $i <= 10; $i++) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'shipping_name' => $user->prenoms . ' ' . $user->nom,
                'shipping_email' => $user->email,
                'shipping_phone' => $user->telephone,
                'shipping_address' => '123 Rue de la Paix',
                'shipping_city' => 'Douala',
                'shipping_postal_code' => '12345',
                'shipping_country' => 'Cameroun',
                'subtotal' => 0,
                'shipping_cost' => 2000,
                'tax' => 0,
                'discount' => 0,
                'total' => 0,
                'status' => $statuses[array_rand($statuses)],
                'payment_status' => rand(0, 1) ? 'paid' : 'pending',
                'payment_method' => 'mobile_money',
                'customer_notes' => "Commande de test {$i}",
                'admin_notes' => null,
                'paid_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
            ]);
            
            // Ajouter des articles à la commande
            $subtotal = 0;
            $itemCount = rand(1, 3);
            $selectedProducts = collect($products)->random($itemCount);
            
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->price;
                $total = $price * $quantity;
                $subtotal += $total;
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->images && is_array($product->images) ? $product->images[0] ?? null : null,
                    'product_sku' => $product->sku,
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $total,
                ]);
            }
            
            // Mettre à jour le total de la commande
            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal + $order->shipping_cost,
            ]);
        }
        
        $this->command->info('Données de test créées avec succès !');
        $this->command->info('Utilisateur: ' . $user->email);
        $this->command->info('Commandes créées: ' . Order::count());
        $this->command->info('Articles créés: ' . OrderItem::count());
    }
}