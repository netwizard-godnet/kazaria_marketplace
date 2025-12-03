<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixPermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige les permissions des dossiers critiques et teste l\'écriture';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Correction des permissions KAZARIA');
        $this->newLine();

        // 1. Créer les dossiers nécessaires
        $this->info('1. Création des dossiers nécessaires...');
        $directories = [
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/framework/cache',
            'storage/framework/cache/data',
            'storage/logs',
            'storage/app/public',
            'storage/app/public/stores/logos',
            'storage/app/public/stores/banners',
            'storage/app/public/products',
            'storage/app/public/profiles',
            'bootstrap/cache',
        ];

        foreach ($directories as $dir) {
            $path = base_path($dir);
            if (!file_exists($path)) {
                mkdir($path, 0775, true);
                $this->info("   ✅ Créé : {$dir}");
            } else {
                $this->line("   ℹ️  Existe : {$dir}");
            }
        }
        $this->newLine();

        // 2. Ajuster les permissions (Linux/Unix uniquement)
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->info('2. Ajustement des permissions...');
            
            $paths = [
                storage_path(),
                storage_path('framework'),
                storage_path('framework/sessions'),
                storage_path('framework/views'),
                storage_path('framework/cache'),
                storage_path('logs'),
                storage_path('app/public'),
                base_path('bootstrap/cache'),
            ];

            foreach ($paths as $path) {
                if (file_exists($path)) {
                    try {
                        chmod($path, 0775);
                        $this->info('   ✅ Permissions ajustées : ' . basename($path));
                    } catch (\Exception $e) {
                        $this->warn('   ⚠️  Impossible de modifier : ' . basename($path));
                        $this->line('      Erreur : ' . $e->getMessage());
                    }
                }
            }
        } else {
            $this->line('2. Système Windows détecté - Permissions non modifiées');
        }
        $this->newLine();

        // 3. Nettoyer le cache
        $this->info('3. Nettoyage du cache...');
        try {
            $this->call('cache:clear');
            $this->call('config:clear');
            $this->call('view:clear');
            $this->call('route:clear');
            $this->info('   ✅ Cache nettoyé');
        } catch (\Exception $e) {
            $this->warn('   ⚠️  Erreur lors du nettoyage : ' . $e->getMessage());
        }
        $this->newLine();

        // 4. Vérifier le lien symbolique
        $this->info('4. Vérification du lien symbolique storage...');
        $publicStorage = public_path('storage');
        if (is_link($publicStorage)) {
            $this->info('   ✅ Lien symbolique existe');
        } else {
            $this->warn('   ⚠️  Lien symbolique manquant - Création...');
            try {
                $this->call('storage:link');
                $this->info('   ✅ Lien créé');
            } catch (\Exception $e) {
                $this->error('   ❌ Échec : ' . $e->getMessage());
            }
        }
        $this->newLine();

        // 5. Tests d'écriture
        $this->info('5. Tests d\'écriture...');
        $testResults = $this->runWriteTests();
        
        $headers = ['Dossier', 'Statut', 'Détails'];
        $rows = [];
        
        $allOk = true;
        foreach ($testResults as $test) {
            $status = $test['success'] ? '✅ OK' : '❌ ÉCHEC';
            $rows[] = [$test['path'], $status, $test['message']];
            if (!$test['success']) {
                $allOk = false;
            }
        }
        
        $this->table($headers, $rows);
        $this->newLine();

        // 6. Recommandations
        $this->info('6. Recommandations :');
        
        if (!$allOk && PHP_OS_FAMILY !== 'Windows') {
            $this->newLine();
            $this->warn('⚠️  Certains dossiers ne sont pas inscriptibles !');
            $this->newLine();
            $this->line('📝 Sur votre serveur, exécutez :');
            $this->line('');
            $this->line('   # Ajuster les permissions');
            $this->line('   chmod -R 775 storage bootstrap/cache');
            $this->line('');
            $this->line('   # Ajuster le propriétaire (remplacez www-data par votre utilisateur web)');
            $this->line('   sudo chown -R www-data:www-data storage bootstrap/cache');
            $this->line('');
            $this->line('   # Ou utilisez le script fourni');
            $this->line('   bash fix-permissions.sh');
            $this->newLine();
        } else if ($allOk) {
            $this->newLine();
            $this->info('✅ Toutes les permissions sont correctes !');
            $this->newLine();
        }

        // 7. Informations système
        $this->info('7. Informations système :');
        $this->table(['Paramètre', 'Valeur'], [
            ['OS', PHP_OS_FAMILY],
            ['Version PHP', PHP_VERSION],
            ['Utilisateur PHP', get_current_user()],
            ['Environnement', app()->environment()],
            ['Storage inscriptible', is_writable(storage_path()) ? '✅ Oui' : '❌ Non'],
            ['Bootstrap/cache inscriptible', is_writable(base_path('bootstrap/cache')) ? '✅ Oui' : '❌ Non'],
        ]);
        
        $this->newLine();
        
        if ($allOk) {
            $this->info('🎉 Correction terminée avec succès !');
            return Command::SUCCESS;
        } else {
            $this->warn('⚠️  Correction partielle - Voir les recommandations ci-dessus');
            return Command::FAILURE;
        }
    }

    /**
     * Effectuer des tests d'écriture dans les dossiers critiques
     */
    private function runWriteTests(): array
    {
        $results = [];
        
        $criticalPaths = [
            'storage/framework/sessions' => storage_path('framework/sessions'),
            'storage/framework/views' => storage_path('framework/views'),
            'storage/framework/cache' => storage_path('framework/cache'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        foreach ($criticalPaths as $name => $path) {
            $testFile = $path . '/permission_test_' . time() . '.txt';
            
            try {
                // Tenter d'écrire un fichier
                if (file_put_contents($testFile, 'test') !== false) {
                    // Succès - supprimer le fichier de test
                    @unlink($testFile);
                    $results[] = [
                        'path' => $name,
                        'success' => true,
                        'message' => 'Écriture réussie'
                    ];
                } else {
                    $results[] = [
                        'path' => $name,
                        'success' => false,
                        'message' => 'Impossible d\'écrire'
                    ];
                }
            } catch (\Exception $e) {
                $results[] = [
                    'path' => $name,
                    'success' => false,
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}
