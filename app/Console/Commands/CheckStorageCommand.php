<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Store;

class CheckStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie l\'état du système de stockage et des images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Diagnostic du système de stockage KAZARIA');
        $this->newLine();

        // 1. Vérifier le lien symbolique
        $this->info('1. Lien symbolique public/storage :');
        $publicStorage = public_path('storage');
        
        if (file_exists($publicStorage)) {
            if (is_link($publicStorage)) {
                $target = readlink($publicStorage);
                $this->info("   ✅ Lien symbolique existe");
                $this->line("   📂 Cible : {$target}");
                
                if (file_exists($target)) {
                    $this->info("   ✅ La cible existe");
                } else {
                    $this->error("   ❌ La cible n'existe pas !");
                }
            } else {
                $this->warn("   ⚠️  public/storage existe mais n'est pas un lien symbolique");
            }
        } else {
            $this->error("   ❌ Le lien symbolique n'existe pas");
        }
        $this->newLine();

        // 2. Vérifier les dossiers
        $this->info('2. Dossiers de stockage :');
        $directories = [
            'storage/app/public' => storage_path('app/public'),
            'stores' => storage_path('app/public/stores'),
            'stores/logos' => storage_path('app/public/stores/logos'),
            'stores/banners' => storage_path('app/public/stores/banners'),
            'products' => storage_path('app/public/products'),
            'profiles' => storage_path('app/public/profiles'),
        ];

        $headers = ['Dossier', 'Statut', 'Fichiers'];
        $rows = [];

        foreach ($directories as $name => $path) {
            $status = file_exists($path) ? '✅ Existe' : '❌ Absent';
            $files = file_exists($path) ? count(glob($path . '/*')) : 0;
            $rows[] = [$name, $status, $files];
        }

        $this->table($headers, $rows);
        $this->newLine();

        // 3. Vérifier les permissions
        $this->info('3. Permissions :');
        $paths = [
            'storage' => storage_path(),
            'storage/app' => storage_path('app'),
            'storage/app/public' => storage_path('app/public'),
            'public' => public_path(),
        ];

        $headers = ['Chemin', 'Permissions', 'Inscriptible'];
        $rows = [];

        foreach ($paths as $name => $path) {
            if (file_exists($path)) {
                $perms = substr(sprintf('%o', fileperms($path)), -4);
                $writable = is_writable($path) ? '✅ Oui' : '❌ Non';
                $rows[] = [$name, $perms, $writable];
            } else {
                $rows[] = [$name, '❌ Absent', '❌ Non'];
            }
        }

        $this->table($headers, $rows);
        $this->newLine();

        // 4. Vérifier les images de boutiques
        $this->info('4. Images de boutiques :');
        $stores = Store::all();
        $this->line("   Nombre de boutiques : " . $stores->count());
        $this->newLine();

        $missingFiles = [];

        foreach ($stores as $store) {
            $this->line("   Boutique : {$store->name}");
            
            // Logo
            if ($store->logo) {
                $logoPath = storage_path('app/public/' . $store->logo);
                if (file_exists($logoPath)) {
                    $this->info("      ✅ Logo : {$store->logo}");
                } else {
                    $this->error("      ❌ Logo manquant : {$store->logo}");
                    $missingFiles[] = ['Boutique', $store->name, 'Logo', $store->logo];
                }
            }
            
            // Bannière
            if ($store->banner) {
                $bannerPath = storage_path('app/public/' . $store->banner);
                if (file_exists($bannerPath)) {
                    $this->info("      ✅ Bannière : {$store->banner}");
                } else {
                    $this->error("      ❌ Bannière manquante : {$store->banner}");
                    $missingFiles[] = ['Boutique', $store->name, 'Bannière', $store->banner];
                }
            }
            
            // Produits
            $products = $store->products;
            $missingProductImages = 0;
            foreach ($products as $product) {
                if ($product->images && is_array($product->images)) {
                    foreach ($product->images as $image) {
                        if (strpos($image, 'products/') === 0) {
                            $imagePath = storage_path('app/public/' . $image);
                            if (!file_exists($imagePath)) {
                                $missingProductImages++;
                                $missingFiles[] = ['Produit', $product->name, 'Image', $image];
                            }
                        }
                    }
                }
            }
            
            if ($missingProductImages > 0) {
                $this->warn("      ⚠️  {$missingProductImages} image(s) produit manquante(s)");
            } else if ($products->count() > 0) {
                $this->info("      ✅ Toutes les images produits OK");
            }
            
            $this->newLine();
        }

        // 5. Résumé des fichiers manquants
        if (count($missingFiles) > 0) {
            $this->newLine();
            $this->error('⚠️  Fichiers manquants détectés :');
            $this->table(['Type', 'Nom', 'Fichier', 'Chemin'], $missingFiles);
        }

        // 6. Recommandations
        $this->newLine();
        $this->info('📝 Recommandations :');
        
        if (!file_exists($publicStorage) || !is_link($publicStorage)) {
            $this->warn('   → Exécuter : php artisan storage:fix');
        }
        
        if (!file_exists(storage_path('app/public/stores'))) {
            $this->warn('   → Créer les dossiers manquants avec : php artisan storage:fix');
        }
        
        if (!is_writable(storage_path())) {
            $this->warn('   → Ajuster les permissions avec : chmod -R 775 storage');
            $this->warn('   → Ajuster le propriétaire avec : chown -R www-data:www-data storage');
        }

        if (count($missingFiles) > 0) {
            $this->warn('   → Restaurer les fichiers manquants depuis une sauvegarde');
        }

        if (count($missingFiles) === 0 && is_link($publicStorage)) {
            $this->newLine();
            $this->info('✅ Tout est en ordre !');
        }

        return Command::SUCCESS;
    }
}
