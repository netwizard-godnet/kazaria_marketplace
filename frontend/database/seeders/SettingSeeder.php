<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Général
            ['key' => 'site_name', 'value' => 'Kazaria', 'type' => 'string', 'group' => 'general', 'description' => 'Nom du site', 'is_public' => true],
            ['key' => 'site_description', 'value' => 'Marketplace en ligne', 'type' => 'string', 'group' => 'general', 'description' => 'Description du site', 'is_public' => true],
            ['key' => 'site_keywords', 'value' => 'marketplace, ecommerce, vente', 'type' => 'string', 'group' => 'general', 'description' => 'Mots-clés SEO', 'is_public' => true],
            ['key' => 'site_logo', 'value' => 'logo.png', 'type' => 'string', 'group' => 'general', 'description' => 'Logo du site', 'is_public' => true],
            ['key' => 'site_favicon', 'value' => 'favicon.ico', 'type' => 'string', 'group' => 'general', 'description' => 'Favicon du site', 'is_public' => true],
            
            // Contact
            ['key' => 'contact_email', 'value' => 'contact@kazaria.com', 'type' => 'string', 'group' => 'contact', 'description' => 'Email de contact', 'is_public' => true],
            ['key' => 'contact_phone', 'value' => '+225 07 00 00 00 00', 'type' => 'string', 'group' => 'contact', 'description' => 'Téléphone de contact', 'is_public' => true],
            ['key' => 'contact_address', 'value' => 'Abidjan, Côte d\'Ivoire', 'type' => 'string', 'group' => 'contact', 'description' => 'Adresse de contact', 'is_public' => true],
            
            // Réseaux sociaux
            ['key' => 'social_facebook', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'Page Facebook', 'is_public' => true],
            ['key' => 'social_twitter', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'Compte Twitter', 'is_public' => true],
            ['key' => 'social_instagram', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'Compte Instagram', 'is_public' => true],
            ['key' => 'social_linkedin', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'Page LinkedIn', 'is_public' => true],
            
            // E-commerce
            ['key' => 'min_order_quantity', 'value' => '1', 'type' => 'integer', 'group' => 'ecommerce', 'description' => 'Quantité minimale de commande', 'is_public' => true],
            ['key' => 'free_shipping_threshold', 'value' => '50000', 'type' => 'integer', 'group' => 'ecommerce', 'description' => 'Seuil de livraison gratuite (FCFA)', 'is_public' => true],
            ['key' => 'shipping_cost', 'value' => '5000', 'type' => 'integer', 'group' => 'ecommerce', 'description' => 'Coût de livraison (FCFA)', 'is_public' => true],
            ['key' => 'currency', 'value' => 'FCFA', 'type' => 'string', 'group' => 'ecommerce', 'description' => 'Devise', 'is_public' => true],
            ['key' => 'currency_symbol', 'value' => 'FCFA', 'type' => 'string', 'group' => 'ecommerce', 'description' => 'Symbole de la devise', 'is_public' => true],
            
            // Maintenance
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'maintenance', 'description' => 'Mode maintenance', 'is_public' => false],
            ['key' => 'maintenance_message', 'value' => 'Site en maintenance', 'type' => 'string', 'group' => 'maintenance', 'description' => 'Message de maintenance', 'is_public' => false],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
