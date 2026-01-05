<?php

namespace App\Helpers;

use App\Models\Setting;

class SettingHelper
{
    /**
     * Obtenir une valeur de paramètre avec fallback
     */
    public static function get($key, $default = null)
    {
        return Setting::get($key, $default);
    }

    /**
     * Obtenir le symbole de la devise
     */
    public static function getCurrencySymbol()
    {
        return self::get('currency_symbol', 'FCFA');
    }

    /**
     * Obtenir la quantité minimale de commande
     */
    public static function getMinOrderQuantity()
    {
        return self::get('min_order_quantity', 1);
    }

    /**
     * Obtenir le coût de livraison
     */
    public static function getShippingCost()
    {
        return self::get('shipping_cost', 1500);
    }

    /**
     * Obtenir le seuil de livraison gratuite
     */
    public static function getFreeShippingThreshold()
    {
        return self::get('free_shipping_threshold', 50000);
    }

    /**
     * Vérifier si la livraison est gratuite pour un montant donné
     */
    public static function isFreeShipping($amount)
    {
        return $amount >= self::getFreeShippingThreshold();
    }

    /**
     * Calculer les frais de livraison pour un montant donné
     */
    public static function calculateShipping($amount)
    {
        return self::isFreeShipping($amount) ? 0 : self::getShippingCost();
    }

    /**
     * Formater un montant avec la devise
     */
    public static function formatPrice($amount)
    {
        return number_format($amount, 0, ',', ' ') . ' ' . self::getCurrencySymbol();
    }
}
