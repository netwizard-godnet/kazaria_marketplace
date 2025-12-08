<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\PriceAlert;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    private FirebaseNotificationService $notificationService;

    public function __construct()
    {
        $this->notificationService = new FirebaseNotificationService();
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Vérifier si le prix a changé
        if ($product->wasChanged('price') || $product->wasChanged('old_price')) {
            $this->handlePriceChange($product);
        }
    }

    /**
     * Gérer le changement de prix
     */
    private function handlePriceChange(Product $product): void
    {
        // Récupérer l'ancien prix depuis les attributs originaux
        $originalPrice = $product->getOriginal('price');
        $newPrice = $product->price;

        // Si le nouveau prix est inférieur à l'ancien (baisse de prix)
        if ($originalPrice && $newPrice < $originalPrice && $product->is_active) {
            Log::info("💰 [PRICE_ALERT] Prix réduit pour le produit {$product->id}: {$originalPrice} -> {$newPrice}");

            // Option 1: Envoyer à tous les utilisateurs qui ont l'app
            $this->sendPriceAlertToAll($product, $originalPrice, $newPrice);

            // Option 2: Envoyer uniquement aux utilisateurs qui ont une alerte de prix active pour ce produit
            $this->sendPriceAlertToSubscribers($product, $originalPrice, $newPrice);
        }
    }

    /**
     * Envoyer une alerte de prix à tous les utilisateurs
     */
    private function sendPriceAlertToAll(Product $product, float $oldPrice, float $newPrice): void
    {
        try {
            $result = $this->notificationService->sendPriceAlert(
                $product->id,
                $product->name,
                $oldPrice,
                $newPrice
            );

            Log::info("📤 [PRICE_ALERT] Notification envoyée à tous: " . json_encode($result));
        } catch (\Exception $e) {
            Log::error("❌ [PRICE_ALERT] Erreur envoi à tous: " . $e->getMessage());
        }
    }

    /**
     * Envoyer une alerte de prix uniquement aux abonnés (utilisateurs avec alertes actives)
     */
    private function sendPriceAlertToSubscribers(Product $product, float $oldPrice, float $newPrice): void
    {
        try {
            // Récupérer les alertes actives pour ce produit où le prix cible est atteint
            $alerts = PriceAlert::where('product_id', $product->id)
                ->active()
                ->where('target_price', '>=', $newPrice)
                ->with('user')
                ->get();

            if ($alerts->isEmpty()) {
                Log::info("📭 [PRICE_ALERT] Aucune alerte active pour le produit {$product->id}");
                return;
            }

            $userIds = $alerts->pluck('user_id')->unique()->toArray();

            $result = $this->notificationService->sendPriceAlert(
                $product->id,
                $product->name,
                $oldPrice,
                $newPrice,
                $userIds
            );

            // Marquer les alertes comme notifiées
            foreach ($alerts as $alert) {
                if ($alert->checkPriceReached()) {
                    Log::info("✅ [PRICE_ALERT] Alerte {$alert->id} déclenchée pour l'utilisateur {$alert->user_id}");
                }
            }

            Log::info("📤 [PRICE_ALERT] Notifications envoyées aux abonnés: " . json_encode($result));
        } catch (\Exception $e) {
            Log::error("❌ [PRICE_ALERT] Erreur envoi aux abonnés: " . $e->getMessage());
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
