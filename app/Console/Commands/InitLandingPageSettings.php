<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;

class InitLandingPageSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:init-landing-page';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialiser les paramètres de la landing page';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Initialisation des paramètres de la landing page...');
        
        // Créer ou mettre à jour les paramètres de landing page
        Setting::set('landing_page_enabled', '0', 'boolean', 'maintenance', 'Activer la landing page', false);
        $this->info('✓ Paramètre landing_page_enabled créé');
        
        Setting::set('landing_page_launch_date', '', 'string', 'maintenance', 'Date de lancement (format: Y-m-d H:i:s)', false);
        $this->info('✓ Paramètre landing_page_launch_date créé');
        
        $this->info('Paramètres de la landing page initialisés avec succès !');
        
        return Command::SUCCESS;
    }
}

