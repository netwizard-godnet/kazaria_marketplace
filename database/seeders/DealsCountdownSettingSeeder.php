<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class DealsCountdownSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Durée du countdown (en minutes)
        Setting::updateOrCreate(
            ['key' => 'deals_countdown_duration'],
            ['value' => '60', 'group' => 'deals'] // Par défaut: 60 minutes (1 heure)
        );
        
        // Catégories pour les deals (IDs séparés par des virgules, vide = toutes)
        Setting::updateOrCreate(
            ['key' => 'deals_categories'],
            ['value' => '', 'group' => 'deals'] // Par défaut: toutes les catégories
        );
        
        // Sous-catégories pour les deals (IDs séparés par des virgules, vide = toutes)
        Setting::updateOrCreate(
            ['key' => 'deals_subcategories'],
            ['value' => '', 'group' => 'deals'] // Par défaut: toutes les sous-catégories
        );
        
        // Pourcentage de remise minimum (ex: 10%)
        Setting::updateOrCreate(
            ['key' => 'deals_min_discount'],
            ['value' => '10', 'group' => 'deals'] // Par défaut: 10%
        );
        
        // Pourcentage de remise maximum (ex: 25%)
        Setting::updateOrCreate(
            ['key' => 'deals_max_discount'],
            ['value' => '25', 'group' => 'deals'] // Par défaut: 25%
        );
    }
}
