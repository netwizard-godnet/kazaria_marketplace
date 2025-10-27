<?php

namespace App\Helpers;

class OrderHelper
{
    /**
     * Traduire le statut d'une commande en français
     */
    public static function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'En attente',
            'processing' => 'En cours',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
            'shipped' => 'Expédiée',
            'returned' => 'Retournée'
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Obtenir la couleur Bootstrap pour un statut
     */
    public static function getStatusColor($status)
    {
        $colors = [
            'pending' => 'warning',
            'processing' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'shipped' => 'primary',
            'returned' => 'secondary'
        ];

        return $colors[$status] ?? 'secondary';
    }

    /**
     * Traduire le statut de paiement en français
     */
    public static function getPaymentStatusLabel($status)
    {
        $labels = [
            'pending' => 'En attente',
            'paid' => 'Payé',
            'failed' => 'Échoué',
            'refunded' => 'Remboursé',
            'partially_refunded' => 'Partiellement remboursé'
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Obtenir la couleur Bootstrap pour un statut de paiement
     */
    public static function getPaymentStatusColor($status)
    {
        $colors = [
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            'partially_refunded' => 'secondary'
        ];

        return $colors[$status] ?? 'secondary';
    }

    /**
     * Obtenir tous les statuts de commande disponibles
     */
    public static function getAvailableStatuses()
    {
        return [
            'pending' => 'En attente',
            'processing' => 'En cours',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée'
        ];
    }

    /**
     * Obtenir tous les statuts de paiement disponibles
     */
    public static function getAvailablePaymentStatuses()
    {
        return [
            'pending' => 'En attente',
            'paid' => 'Payé',
            'failed' => 'Échoué',
            'refunded' => 'Remboursé'
        ];
    }
}
