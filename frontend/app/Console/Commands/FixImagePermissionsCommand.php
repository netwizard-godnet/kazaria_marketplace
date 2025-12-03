<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixImagePermissionsCommand extends Command
{
    protected $signature = 'images:fix-permissions';
    protected $description = 'Corrige les permissions des dossiers d\'images et vérifie l\'accessibilité';

    public function handle()
    {
        $this->info('🔧 Correction des permissions des images KAZARIA');
        
        // Dossiers à vérifier
        $directories = [
            storage_path('app/public'),
            storage_path('app/public/stores'),
            storage_path('app/public/stores/logos'),
            storage_path('app/public/stores/banners'),
            storage_path('app/public/products'),
            public_path('storage'),
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                File::makeDirectory($dir, 0755, true);
                $this->info("   ✅ Dossier créé: {$dir}");
            } else {
                $this->info("   ✅ Dossier existe: {$dir}");
            }
            
            // Corriger les permissions
            if (PHP_OS_FAMILY !== 'Windows') {
                chmod($dir, 0755);
                $this->info("   ✅ Permissions corrigées: {$dir}");
            }
        }
        
        // Vérifier le lien symbolique
        $publicStorage = public_path('storage');
        if (file_exists($publicStorage)) {
            if (is_link($publicStorage)) {
                $this->info('   ✅ Lien symbolique public/storage existe');
                $target = readlink($publicStorage);
                $this->info("   📁 Pointe vers: {$target}");
                
                if (file_exists($target)) {
                    $this->info('   ✅ Cible du lien accessible');
                } else {
                    $this->error('   ❌ Cible du lien non accessible');
                }
            } else {
                $this->warn('   ⚠️  public/storage existe mais n\'est pas un lien symbolique');
            }
        } else {
            $this->error('   ❌ Lien symbolique public/storage manquant');
            $this->info('   🔧 Création du lien symbolique...');
            $this->call('storage:link');
        }
        
        // Tester l'accessibilité des images
        $this->info('🧪 Test d\'accessibilité des images...');
        
        // Créer un fichier de test
        $testFile = storage_path('app/public/test.txt');
        file_put_contents($testFile, 'Test KAZARIA');
        
        $testUrl = asset('storage/test.txt');
        $this->info("   📄 Fichier de test créé: {$testUrl}");
        
        // Vérifier l'URL
        $headers = @get_headers($testUrl);
        if ($headers && strpos($headers[0], '200') !== false) {
            $this->info('   ✅ URL accessible via HTTP');
        } else {
            $this->error('   ❌ URL non accessible via HTTP');
            $this->warn('   💡 Vérifiez la configuration du serveur web');
        }
        
        // Nettoyer le fichier de test
        unlink($testFile);
        
        $this->info('✅ Correction des permissions terminée');
        return Command::SUCCESS;
    }
}
