<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:fix {--force : Force la réparation même si le lien existe}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Répare le système de stockage et crée les dossiers nécessaires';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Réparation du système de stockage KAZARIA');
        $this->newLine();

        // 1. Vérifier et nettoyer public/storage
        $this->info('1. Nettoyage du dossier public/storage...');
        $publicStorage = public_path('storage');
        
        if (file_exists($publicStorage)) {
            if (is_link($publicStorage)) {
                if ($this->option('force')) {
                    unlink($publicStorage);
                    $this->info('   ✅ Lien symbolique supprimé');
                } else {
                    $this->warn('   ⚠️  Lien symbolique existe déjà (utilisez --force pour recréer)');
                    return Command::SUCCESS;
                }
            } else {
                $backup = public_path('storage_backup_' . time());
                rename($publicStorage, $backup);
                $this->warn("   ✅ Dossier sauvegardé dans : {$backup}");
            }
        }
        $this->newLine();

        // 2. Créer les dossiers nécessaires
        $this->info('2. Création des dossiers de stockage...');
        $directories = [
            'stores',
            'stores/logos',
            'stores/banners',
            'products',
            'profiles',
        ];

        foreach ($directories as $dir) {
            $path = storage_path('app/public/' . $dir);
            if (!file_exists($path)) {
                mkdir($path, 0775, true);
                $this->info("   ✅ Créé : {$dir}/");
            } else {
                $this->line("   ℹ️  Existe déjà : {$dir}/");
            }
        }
        $this->newLine();

        // 3. Créer le lien symbolique
        $this->info('3. Création du lien symbolique...');
        try {
            $this->call('storage:link');
            $this->info('   ✅ Lien symbolique créé');
        } catch (\Exception $e) {
            $this->error('   ❌ Erreur : ' . $e->getMessage());
            
            // Alternative manuelle
            $this->info('   ℹ️  Tentative de création manuelle...');
            $target = storage_path('app/public');
            
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = 'mklink /D "' . $publicStorage . '" "' . $target . '"';
                exec($cmd, $output, $return);
                if ($return === 0) {
                    $this->info('   ✅ Lien créé manuellement');
                } else {
                    $this->error('   ❌ Échec de la création manuelle');
                }
            } else {
                symlink($target, $publicStorage);
                $this->info('   ✅ Lien créé manuellement');
            }
        }
        $this->newLine();

        // 4. Ajuster les permissions
        $this->info('4. Ajustement des permissions...');
        $paths = [
            storage_path(),
            storage_path('app'),
            storage_path('app/public'),
            storage_path('framework'),
            storage_path('logs'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                if (PHP_OS_FAMILY !== 'Windows') {
                    chmod($path, 0775);
                    $this->info('   ✅ Permissions ajustées : ' . basename($path));
                } else {
                    $this->line('   ℹ️  Permissions (Windows) : ' . basename($path));
                }
            }
        }
        $this->newLine();

        // 5. Créer .gitignore
        $this->info('5. Configuration Git...');
        $gitignorePath = storage_path('app/public/.gitignore');
        if (!file_exists($gitignorePath)) {
            file_put_contents($gitignorePath, "*\n!.gitignore\n");
            $this->info('   ✅ Fichier .gitignore créé');
        } else {
            $this->line('   ℹ️  Fichier .gitignore existe déjà');
        }
        $this->newLine();

        // 6. Vérification finale
        $this->newLine();
        $this->info('Vérification finale :');
        
        if (is_link(public_path('storage'))) {
            $this->info('   ✅ Lien symbolique : OK');
        } else {
            $this->error('   ❌ Lien symbolique : ÉCHEC');
        }

        $testFile = storage_path('app/public/test_' . time() . '.txt');
        file_put_contents($testFile, 'Test');
        $publicTestFile = public_path('storage/' . basename($testFile));
        
        if (file_exists($publicTestFile)) {
            $this->info('   ✅ Accès public : OK');
            unlink($testFile);
        } else {
            $this->error('   ❌ Accès public : ÉCHEC');
            if (file_exists($testFile)) {
                unlink($testFile);
            }
        }

        $this->newLine();
        $this->info('✅ Réparation terminée !');
        
        if (app()->environment('production')) {
            $this->newLine();
            $this->warn('📝 Sur le serveur de production, pensez à :');
            $this->line('   - Vérifier les permissions : chmod -R 775 storage bootstrap/cache');
            $this->line('   - Vérifier le propriétaire : chown -R www-data:www-data storage');
        }

        return Command::SUCCESS;
    }
}
