<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderStatusService
{
    // Order Statuses
    const STATUS_PENDING = 'pending'; // En cours de validation
    const STATUS_PROCESSING = 'processing'; // En cours de livraison (préparation/expédition)
    const STATUS_DELIVERED = 'delivered'; // Livrée
    const STATUS_CANCELLED = 'cancelled'; // Annulée

    // Payment Statuses
    const PAYMENT_PENDING = 'pending'; // En attente de paiement
    const PAYMENT_PAID = 'paid'; // Payé
    const PAYMENT_FAILED = 'failed'; // Échec de paiement
    const PAYMENT_REFUNDED = 'refunded'; // Remboursé

    // Valid Order Status Transitions
    const VALID_STATUS_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_DELIVERED, self::STATUS_CANCELLED],
        self::STATUS_DELIVERED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED], // Permet le retour ou l'annulation après livraison
        self::STATUS_CANCELLED => [self::STATUS_PENDING, self::STATUS_PROCESSING], // Permet la réactivation
    ];

    // Valid Payment Status Transitions
    const VALID_PAYMENT_TRANSITIONS = [
        self::PAYMENT_PENDING => [self::PAYMENT_PAID, self::PAYMENT_FAILED, self::PAYMENT_REFUNDED],
        self::PAYMENT_PAID => [self::PAYMENT_PENDING, self::PAYMENT_REFUNDED],
        self::PAYMENT_FAILED => [self::PAYMENT_PENDING, self::PAYMENT_PAID],
        self::PAYMENT_REFUNDED => [self::PAYMENT_PENDING],
    ];

    /**
     * Change le statut d'une commande et gère la synchronisation du stock.
     *
     * @param Order $order
     * @param string $newStatus
     * @param string|null $reason
     * @return bool
     * @throws \Exception
     */
    public static function changeOrderStatus(Order $order, string $newStatus, string $reason = null): bool
    {
        $oldStatus = $order->status;
        
        // Vérifier si le changement est valide
        if (!self::isValidStatusChange($oldStatus, $newStatus)) {
            throw new \Exception("Changement de statut invalide: {$oldStatus} -> {$newStatus}");
        }

        try {
            DB::beginTransaction();

            $order->update(['status' => $newStatus]);

            // Gérer le stock en fonction des transitions
            if ($oldStatus === self::STATUS_PENDING && $newStatus === self::STATUS_CANCELLED) {
                // Si annulée depuis pending, libérer le stock
                StockService::releaseStock($order);
            } elseif ($oldStatus === self::STATUS_PROCESSING && $newStatus === self::STATUS_CANCELLED) {
                // Si annulée depuis processing, libérer le stock
                StockService::releaseStock($order);
            } elseif ($oldStatus === self::STATUS_DELIVERED && $newStatus === self::STATUS_PROCESSING) {
                // Si retournée depuis delivered, libérer le stock
                StockService::releaseStock($order);
            } elseif ($oldStatus === self::STATUS_CANCELLED && ($newStatus === self::STATUS_PENDING || $newStatus === self::STATUS_PROCESSING)) {
                // Si réactivée depuis cancelled, réserver le stock
                StockService::reserveStock($order);
            } elseif ($newStatus === self::STATUS_DELIVERED) {
                // Si livrée, confirmer la vente (le stock est déjà réservé)
                StockService::confirmSale($order);
                // Marquer comme payée SEULEMENT si ce n'est pas un paiement à la livraison
                if ($order->payment_status !== self::PAYMENT_PAID && $order->payment_method !== 'cash_on_delivery') {
                    $order->changePaymentStatus(self::PAYMENT_PAID, 'Commande livrée');
                }
            }

            Log::info("Statut de commande {$order->order_number} changé de {$oldStatus} à {$newStatus}. Raison: {$reason}");
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors du changement de statut de commande {$order->order_number}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Change le statut de paiement d'une commande.
     *
     * @param Order $order
     * @param string $newPaymentStatus
     * @param string|null $reason
     * @return bool
     * @throws \Exception
     */
    public static function changePaymentStatus(Order $order, string $newPaymentStatus, string $reason = null): bool
    {
        $oldPaymentStatus = $order->payment_status;

        // Vérifier si le changement est valide
        if (!self::isValidPaymentChange($oldPaymentStatus, $newPaymentStatus)) {
            throw new \Exception("Changement de statut de paiement invalide: {$oldPaymentStatus} -> {$newPaymentStatus}");
        }

        try {
            DB::beginTransaction();

            $updateData = ['payment_status' => $newPaymentStatus];
            if ($newPaymentStatus === self::PAYMENT_PAID && is_null($order->paid_at)) {
                $updateData['paid_at'] = now();
            } elseif ($newPaymentStatus !== self::PAYMENT_PAID) {
                $updateData['paid_at'] = null;
            }
            $order->update($updateData);

            Log::info("Statut de paiement {$order->order_number} changé de {$oldPaymentStatus} à {$newPaymentStatus}. Raison: {$reason}");
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur lors du changement de statut de paiement {$order->order_number}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vérifie si une transition de statut de commande est valide.
     *
     * @param string $oldStatus
     * @param string $newStatus
     * @return bool
     */
    public static function isValidStatusChange(string $oldStatus, string $newStatus): bool
    {
        return isset(self::VALID_STATUS_TRANSITIONS[$oldStatus]) && in_array($newStatus, self::VALID_STATUS_TRANSITIONS[$oldStatus]);
    }

    /**
     * Vérifie si une transition de statut de paiement est valide.
     *
     * @param string $oldPaymentStatus
     * @param string $newPaymentStatus
     * @return bool
     */
    public static function isValidPaymentChange(string $oldPaymentStatus, string $newPaymentStatus): bool
    {
        return isset(self::VALID_PAYMENT_TRANSITIONS[$oldPaymentStatus]) && in_array($newPaymentStatus, self::VALID_PAYMENT_TRANSITIONS[$oldPaymentStatus]);
    }

    /**
     * Retourne les statuts de commande disponibles pour une transition.
     *
     * @param string $currentStatus
     * @return array
     */
    public static function getAvailableStatuses(string $currentStatus): array
    {
        $available = self::VALID_STATUS_TRANSITIONS[$currentStatus] ?? [];
        $labels = [];
        foreach ($available as $status) {
            $labels[$status] = [
                'label' => self::getStatusLabel($status),
                'class' => self::getStatusClass($status)
            ];
        }
        return $labels;
    }

    /**
     * Retourne les statuts de paiement disponibles pour une transition.
     *
     * @param string $currentPaymentStatus
     * @return array
     */
    public static function getAvailablePaymentStatuses(string $currentPaymentStatus): array
    {
        $available = self::VALID_PAYMENT_TRANSITIONS[$currentPaymentStatus] ?? [];
        $labels = [];
        foreach ($available as $status) {
            $labels[$status] = [
                'label' => self::getPaymentStatusLabel($status),
                'class' => self::getPaymentStatusClass($status)
            ];
        }
        return $labels;
    }

    /**
     * Retourne le label lisible pour un statut de commande.
     *
     * @param string $status
     * @return string
     */
    public static function getStatusLabel(string $status): string
    {
        $labels = [
            self::STATUS_PENDING => 'En cours de validation',
            self::STATUS_PROCESSING => 'En cours de livraison',
            self::STATUS_DELIVERED => 'Livrée',
            self::STATUS_CANCELLED => 'Annulée',
        ];
        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Retourne le label lisible pour un statut de paiement.
     *
     * @param string $paymentStatus
     * @return string
     */
    public static function getPaymentStatusLabel(string $paymentStatus): string
    {
        $labels = [
            self::PAYMENT_PENDING => 'En attente',
            self::PAYMENT_PAID => 'Payée',
            self::PAYMENT_FAILED => 'Échec',
            self::PAYMENT_REFUNDED => 'Remboursé',
        ];
        return $labels[$paymentStatus] ?? ucfirst($paymentStatus);
    }

    /**
     * Retourne la classe CSS pour un statut de commande.
     *
     * @param string $status
     * @return string
     */
    public static function getStatusClass(string $status): string
    {
        $classes = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'danger',
        ];
        return $classes[$status] ?? 'secondary';
    }

    /**
     * Retourne la classe CSS pour un statut de paiement.
     *
     * @param string $paymentStatus
     * @return string
     */
    public static function getPaymentStatusClass(string $paymentStatus): string
    {
        $classes = [
            self::PAYMENT_PENDING => 'warning',
            self::PAYMENT_PAID => 'success',
            self::PAYMENT_FAILED => 'danger',
            self::PAYMENT_REFUNDED => 'secondary',
        ];
        return $classes[$paymentStatus] ?? 'secondary';
    }
}
