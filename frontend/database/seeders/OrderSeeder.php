<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier s'il y a des utilisateurs et des produits
        $users = User::all();
        $products = Product::all();
        
        if ($users->count() === 0 || $products->count() === 0) {
            $this->command->info('Aucun utilisateur ou produit trouvé. Création de données de test...');
            
            // Créer un utilisateur de test
            $user = User::create([
                'nom' => 'Dupont',
                'prenoms' => 'Jean',
                'email' => 'jean.dupont@example.com',
                'telephone' => '+237 123 456 789',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            
            // Créer quelques produits de test
            $products = collect();
            for ($i = 1; $i <= 5; $i++) {
                $product = Product::create([
                    'name' => "Produit Test {$i}",
                    'description' => "Description du produit test {$i}",
                    'price' => rand(5000, 50000),
                    'quantity' => rand(10, 100),
                    'sku' => "SKU-{$i}",
                    'status' => 'active',
                    'category_id' => 1,
                    'subcategory_id' => 1,
                    'store_id' => 1,
                ]);
                $products->push($product);
            }
        } else {
            $user = $users->first();
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
            $selectedProducts = $products->random($itemCount);
            
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
        
        $this->command->info('Commandes de test créées avec succès !');
    }
}