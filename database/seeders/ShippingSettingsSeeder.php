<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class ShippingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'shipping_cost',
                'value' => '5000',
                'type' => 'float',
                'group' => 'ecommerce',
                'description' => 'Coût de livraison par défaut (FCFA)',
                'is_public' => true
            ],
            [
                'key' => 'free_shipping_threshold',
                'value' => '50000',
                'type' => 'float',
                'group' => 'ecommerce',
                'description' => 'Seuil de livraison gratuite (FCFA)',
                'is_public' => true
            ],
            [
                'key' => 'min_order_quantity',
                'value' => '1',
                'type' => 'integer',
                'group' => 'ecommerce',
                'description' => 'Quantité minimale de commande',
                'is_public' => true
            ],
            [
                'key' => 'currency_symbol',
                'value' => 'FCFA',
                'type' => 'string',
                'group' => 'ecommerce',
                'description' => 'Symbole de la devise',
                'is_public' => true
            ]
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Paramètres de livraison créés avec succès !');
    }
}
