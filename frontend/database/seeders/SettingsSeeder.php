<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Paramètres généraux
            ['key' => 'site_name', 'value' => 'Kazaria Marketplace', 'type' => 'string', 'group' => 'general', 'description' => 'Nom du site', 'is_public' => true],
            ['key' => 'site_email', 'value' => 'contact@kazaria.ci', 'type' => 'string', 'group' => 'general', 'description' => 'Email de contact', 'is_public' => true],
            ['key' => 'site_phone', 'value' => '+225 XX XX XX XX', 'type' => 'string', 'group' => 'general', 'description' => 'Téléphone de contact', 'is_public' => true],
            ['key' => 'site_address', 'value' => 'Abidjan, Côte d\'Ivoire', 'type' => 'string', 'group' => 'general', 'description' => 'Adresse du site', 'is_public' => true],
            ['key' => 'site_description', 'value' => 'La marketplace de référence en Côte d\'Ivoire', 'type' => 'string', 'group' => 'general', 'description' => 'Description du site', 'is_public' => true],
            
            // Paramètres de paiement
            ['key' => 'default_commission_rate', 'value' => '5.0', 'type' => 'float', 'group' => 'payment', 'description' => 'Taux de commission par défaut (%)', 'is_public' => false],
            ['key' => 'min_order_amount', 'value' => '1000', 'type' => 'float', 'group' => 'payment', 'description' => 'Montant minimum de commande (FCFA)', 'is_public' => true],
            ['key' => 'free_shipping_threshold', 'value' => '50000', 'type' => 'float', 'group' => 'payment', 'description' => 'Seuil de livraison gratuite (FCFA)', 'is_public' => true],
            ['key' => 'default_shipping_cost', 'value' => '2000', 'type' => 'float', 'group' => 'payment', 'description' => 'Coût de livraison par défaut (FCFA)', 'is_public' => true],
            
            // Paramètres CinetPay
            ['key' => 'cinetpay_api_key', 'value' => '', 'type' => 'string', 'group' => 'cinetpay', 'description' => 'Clé API CinetPay', 'is_public' => false],
            ['key' => 'cinetpay_site_id', 'value' => '', 'type' => 'string', 'group' => 'cinetpay', 'description' => 'ID du site CinetPay', 'is_public' => false],
            ['key' => 'cinetpay_currency', 'value' => 'XOF', 'type' => 'string', 'group' => 'cinetpay', 'description' => 'Devise CinetPay', 'is_public' => false],
            
            // Paramètres Stripe
            ['key' => 'stripe_public_key', 'value' => '', 'type' => 'string', 'group' => 'stripe', 'description' => 'Clé publique Stripe', 'is_public' => false],
            ['key' => 'stripe_secret_key', 'value' => '', 'type' => 'string', 'group' => 'stripe', 'description' => 'Clé secrète Stripe', 'is_public' => false],
            
            // Paramètres d'email
            ['key' => 'mail_from_name', 'value' => 'Kazaria Marketplace', 'type' => 'string', 'group' => 'email', 'description' => 'Nom de l\'expéditeur des emails', 'is_public' => false],
            ['key' => 'mail_from_address', 'value' => 'noreply@kazaria.ci', 'type' => 'string', 'group' => 'email', 'description' => 'Adresse email de l\'expéditeur', 'is_public' => false],
            ['key' => 'mail_support_address', 'value' => 'support@kazaria.ci', 'type' => 'string', 'group' => 'email', 'description' => 'Adresse email du support', 'is_public' => true],
            
            // Paramètres de sécurité
            ['key' => 'max_login_attempts', 'value' => '5', 'type' => 'integer', 'group' => 'security', 'description' => 'Nombre maximum de tentatives de connexion', 'is_public' => false],
            ['key' => 'session_timeout', 'value' => '120', 'type' => 'integer', 'group' => 'security', 'description' => 'Timeout de session (minutes)', 'is_public' => false],
            ['key' => 'password_min_length', 'value' => '8', 'type' => 'integer', 'group' => 'security', 'description' => 'Longueur minimale du mot de passe', 'is_public' => true],
            
            // Paramètres de maintenance
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'maintenance', 'description' => 'Mode maintenance', 'is_public' => false],
            ['key' => 'maintenance_message', 'value' => 'Le site est temporairement en maintenance. Nous reviendrons bientôt !', 'type' => 'string', 'group' => 'maintenance', 'description' => 'Message de maintenance', 'is_public' => true],
            
            // Paramètres de notification
            ['key' => 'email_notifications', 'value' => '1', 'type' => 'boolean', 'group' => 'notifications', 'description' => 'Activer les notifications par email', 'is_public' => false],
            ['key' => 'sms_notifications', 'value' => '0', 'type' => 'boolean', 'group' => 'notifications', 'description' => 'Activer les notifications SMS', 'is_public' => false],
            ['key' => 'push_notifications', 'value' => '1', 'type' => 'boolean', 'group' => 'notifications', 'description' => 'Activer les notifications push', 'is_public' => false],
        ];
        
        foreach ($settings as $setting) {
            \App\Models\Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
