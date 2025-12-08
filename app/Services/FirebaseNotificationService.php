<?php

namespace App\Services;

use App\Models\FcmToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service pour envoyer des notifications push via Firebase Cloud Messaging
 */
class FirebaseNotificationService
{
    private string $serverKey;
    private string $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    public function __construct()
    {
        // Récupérer la clé serveur Firebase depuis les variables d'environnement
        $this->serverKey = config('services.fcm.server_key', env('FCM_SERVER_KEY'));
        
        if (empty($this->serverKey)) {
            Log::warning('⚠️ [FCM] FCM_SERVER_KEY non configurée dans .env');
        }
    }

    /**
     * Envoyer une notification à un utilisateur spécifique
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): array
    {
        $tokens = FcmToken::where('user_id', $userId)
            ->active()
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            Log::info("📭 [FCM] Aucun token actif pour l'utilisateur $userId");
            return ['success' => false, 'message' => 'Aucun token actif'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Envoyer une notification à plusieurs utilisateurs
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): array
    {
        $tokens = FcmToken::whereIn('user_id', $userIds)
            ->active()
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            Log::info("📭 [FCM] Aucun token actif pour les utilisateurs");
            return ['success' => false, 'message' => 'Aucun token actif'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Envoyer une notification à tous les utilisateurs
     */
    public function sendToAll(string $title, string $body, array $data = []): array
    {
        $tokens = FcmToken::active()
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            Log::info("📭 [FCM] Aucun token actif");
            return ['success' => false, 'message' => 'Aucun token actif'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Envoyer une notification à des tokens spécifiques
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        if (empty($this->serverKey)) {
            Log::error('❌ [FCM] FCM_SERVER_KEY non configurée');
            return ['success' => false, 'message' => 'FCM_SERVER_KEY non configurée'];
        }

        // Firebase limite à 1000 tokens par requête
        $chunks = array_chunk($tokens, 1000);
        $results = [];

        foreach ($chunks as $chunk) {
            $result = $this->sendBatch($chunk, $title, $body, $data);
            $results[] = $result;
        }

        $totalSent = array_sum(array_column($results, 'success_count'));
        $totalFailed = array_sum(array_column($results, 'failure_count'));

        Log::info("📤 [FCM] Notifications envoyées: $totalSent réussies, $totalFailed échouées");

        return [
            'success' => $totalSent > 0,
            'success_count' => $totalSent,
            'failure_count' => $totalFailed,
            'total' => count($tokens),
        ];
    }

    /**
     * Envoyer un batch de notifications
     */
    private function sendBatch(array $tokens, string $title, string $body, array $data): array
    {
        $payload = [
            'registration_ids' => $tokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'badge' => 1,
            ],
            'data' => array_merge([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'type' => $data['type'] ?? 'general',
            ], $data),
            'priority' => 'high',
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->post($this->fcmUrl, $payload);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['success'])) {
                $successCount = $responseData['success'] ?? 0;
                $failureCount = $responseData['failure'] ?? 0;

                // Gérer les tokens invalides
                if (isset($responseData['results'])) {
                    foreach ($responseData['results'] as $index => $result) {
                        if (isset($result['error'])) {
                            $error = $result['error'];
                            // Désactiver les tokens invalides
                            if (in_array($error, ['InvalidRegistration', 'NotRegistered'])) {
                                $token = $tokens[$index];
                                FcmToken::where('token', $token)->update(['is_active' => false]);
                                Log::info("🗑️ [FCM] Token désactivé: $token (erreur: $error)");
                            }
                        }
                    }
                }

                return [
                    'success' => true,
                    'success_count' => $successCount,
                    'failure_count' => $failureCount,
                ];
            }

            Log::error('❌ [FCM] Erreur réponse Firebase: ' . $response->body());
            return [
                'success' => false,
                'success_count' => 0,
                'failure_count' => count($tokens),
                'error' => $responseData['error'] ?? 'Erreur inconnue',
            ];
        } catch (\Exception $e) {
            Log::error('❌ [FCM] Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'success_count' => 0,
                'failure_count' => count($tokens),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envoyer une alerte de prix pour un produit
     */
    public function sendPriceAlert(int $productId, string $productName, float $oldPrice, float $newPrice, array $userIds = []): array
    {
        $discount = $oldPrice - $newPrice;
        $discountPercent = round(($discount / $oldPrice) * 100, 0);

        $title = "💰 Prix réduit !";
        $body = "$productName : " . number_format($newPrice, 0, ',', ' ') . " FCFA (-$discountPercent%)";

        $data = [
            'type' => 'price_alert',
            'product_id' => $productId,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'discount' => $discount,
            'discount_percent' => $discountPercent,
        ];

        if (!empty($userIds)) {
            return $this->sendToUsers($userIds, $title, $body, $data);
        }

        return $this->sendToAll($title, $body, $data);
    }
}
