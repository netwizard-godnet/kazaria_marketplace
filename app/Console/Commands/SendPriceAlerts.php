<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Services\FirebaseNotificationService;

class SendPriceAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-price-alerts {--product_id= : ID du produit spécifique}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoyer des alertes de prix pour les produits en promotion';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notificationService = new FirebaseNotificationService();
        
        $productId = $this->option('product_id');
        
        if ($productId) {
            // Envoyer pour un produit spécifique
            $product = Product::find($productId);
            
            if (!$product) {
                $this->error("Produit #$productId introuvable");
                return 1;
            }
            
            if ($product->old_price && $product->price < $product->old_price) {
                $result = $notificationService->sendPriceAlert(
                    $product->id,
                    $product->name,
                    $product->old_price,
                    $product->price
                );
                
                $this->info("✅ Notification envoyée pour {$product->name}");
                $this->info("   Succès: {$result['success_count']}, Échecs: {$result['failure_count']}");
            } else {
                $this->warn("Le produit {$product->name} n'a pas de réduction de prix");
            }
        } else {
            // Envoyer pour tous les produits en promotion
            $products = Product::whereColumn('price', '<', 'old_price')
                ->where('is_active', true)
                ->whereNotNull('old_price')
                ->get();
            
            if ($products->isEmpty()) {
                $this->info("Aucun produit en promotion trouvé");
                return 0;
            }
            
            $this->info("Envoi des alertes pour {$products->count()} produit(s)...");
            
            $bar = $this->output->createProgressBar($products->count());
            $bar->start();
            
            foreach ($products as $product) {
                $result = $notificationService->sendPriceAlert(
                    $product->id,
                    $product->name,
                    $product->old_price,
                    $product->price
                );
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("✅ Toutes les notifications ont été envoyées");
        }
        
        return 0;
    }
}
