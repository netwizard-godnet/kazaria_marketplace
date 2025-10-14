<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;
use App\Models\Store;

class OptimizeSeoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:optimize {--products : Optimiser les produits} {--categories : Optimiser les catégories} {--all : Tout optimiser}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimise le SEO des produits, catégories et boutiques avec des mots-clés ciblés';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Optimisation SEO KAZARIA');
        $this->newLine();

        if ($this->option('all') || $this->option('products')) {
            $this->optimizeProducts();
        }

        if ($this->option('all') || $this->option('categories')) {
            $this->optimizeCategories();
        }

        if (!$this->option('all') && !$this->option('products') && !$this->option('categories')) {
            $this->warn('⚠️  Aucune option sélectionnée. Utilisez --products, --categories ou --all');
            $this->newLine();
            $this->info('Usage :');
            $this->line('  php artisan seo:optimize --products     # Optimiser les produits');
            $this->line('  php artisan seo:optimize --categories   # Optimiser les catégories');
            $this->line('  php artisan seo:optimize --all          # Tout optimiser');
        }

        return Command::SUCCESS;
    }

    /**
     * Optimiser les produits
     */
    private function optimizeProducts()
    {
        $this->info('📦 Optimisation des produits...');
        $this->newLine();

        $products = Product::whereNull('meta_description')
            ->orWhere('meta_description', '')
            ->orWhereNull('meta_keywords')
            ->orWhere('meta_keywords', '')
            ->get();

        if ($products->count() === 0) {
            $this->info('   ✅ Tous les produits sont déjà optimisés');
            $this->newLine();
            return;
        }

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $optimized = 0;

        foreach ($products as $product) {
            // Générer meta description optimisée
            $metaDescription = $this->generateProductMetaDescription($product);
            
            // Générer meta keywords optimisés
            $metaKeywords = $this->generateProductMetaKeywords($product);

            // Mettre à jour le produit
            $product->update([
                'meta_description' => $metaDescription,
                'meta_keywords' => $metaKeywords,
            ]);

            $optimized++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("   ✅ {$optimized} produits optimisés");
        $this->newLine();
    }

    /**
     * Générer une meta description optimisée pour un produit
     */
    private function generateProductMetaDescription($product)
    {
        $price = number_format($product->price, 0, ',', ' ');
        $brand = $product->brand ?? 'qualité premium';
        
        $templates = [
            "Achetez {$product->name} à {$price} FCFA. {$brand} neuf et garanti. Livraison gratuite Abidjan, paiement Mobile Money. Stock disponible chez KAZARIA CI.",
            "{$product->name} au meilleur prix en Côte d'Ivoire : {$price} FCFA. {$brand} authentique, garantie fabricant. Livraison gratuite, retour 14 jours. Alternative fiable à Jumia.",
            "Commandez {$product->name} à Abidjan. Prix : {$price} FCFA. {$brand} 100% authentique. Livraison gratuite, service client 24/7. Meilleur prix CI garanti !",
        ];

        return $templates[array_rand($templates)];
    }

    /**
     * Générer des meta keywords optimisés pour un produit
     */
    private function generateProductMetaKeywords($product)
    {
        $keywords = [];

        // Nom du produit
        $keywords[] = strtolower($product->name);
        
        // Marque
        if ($product->brand) {
            $keywords[] = strtolower($product->brand) . ' côte d\'ivoire';
            $keywords[] = strtolower($product->brand) . ' abidjan';
        }

        // Catégorie
        if ($product->category) {
            $keywords[] = strtolower($product->category->name) . ' abidjan';
            $keywords[] = 'acheter ' . strtolower($product->category->name) . ' ci';
        }

        // Mots-clés génériques
        $keywords[] = 'livraison gratuite abidjan';
        $keywords[] = 'paiement mobile money';
        $keywords[] = 'authentique garanti';
        $keywords[] = 'meilleur prix ci';
        $keywords[] = 'alternative jumia';
        $keywords[] = 'marketplace côte d\'ivoire';

        // Mots-clés géolocalisés
        $locations = ['cocody', 'plateau', 'marcory', 'yopougon', 'adjamé'];
        $randomLocation = $locations[array_rand($locations)];
        $keywords[] = strtolower($product->name) . ' ' . $randomLocation;

        return implode(', ', array_unique($keywords));
    }

    /**
     * Optimiser les catégories
     */
    private function optimizeCategories()
    {
        $this->info('📁 Optimisation des catégories...');
        $this->newLine();

        $categories = Category::all();

        if ($categories->count() === 0) {
            $this->warn('   ⚠️  Aucune catégorie trouvée');
            $this->newLine();
            return;
        }

        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();

        $optimized = 0;

        foreach ($categories as $category) {
            // Générer meta description si elle n'existe pas
            if (empty($category->meta_description)) {
                $metaDescription = $this->generateCategoryMetaDescription($category);
                $metaKeywords = $this->generateCategoryMetaKeywords($category);

                $category->update([
                    'meta_description' => $metaDescription,
                    'meta_keywords' => $metaKeywords,
                ]);

                $optimized++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("   ✅ {$optimized} catégories optimisées");
        $this->newLine();
    }

    /**
     * Générer une meta description optimisée pour une catégorie
     */
    private function generateCategoryMetaDescription($category)
    {
        $templates = [
            "Achetez {$category->name} neufs et garantis à Abidjan. Large choix, meilleurs prix de Côte d'Ivoire. Livraison gratuite, paiement Mobile Money. Alternative fiable à Jumia.",
            "{$category->name} au meilleur prix en CI. Stock permanent, livraison gratuite Abidjan. Paiement sécurisé, garantie fabricant. Marketplace ivoirienne de confiance KAZARIA.",
            "Découvrez notre sélection de {$category->name} à Abidjan. Prix 15% moins chers, livraison express gratuite. Service client 24/7. Votre alternative locale à Jumia.",
        ];

        return $templates[array_rand($templates)];
    }

    /**
     * Générer des meta keywords optimisés pour une catégorie
     */
    private function generateCategoryMetaKeywords($category)
    {
        $name = strtolower($category->name);

        $keywords = [
            $name . ' abidjan',
            'acheter ' . $name . ' côte d\'ivoire',
            $name . ' pas cher ci',
            $name . ' livraison gratuite abidjan',
            'meilleur prix ' . $name . ' ci',
            $name . ' authentique abidjan',
            'marketplace ' . $name . ' ci',
            'boutique ' . $name . ' abidjan',
            'vente ' . $name . ' en ligne ci',
            'commander ' . $name . ' abidjan',
            'alternative jumia ' . $name,
            $name . ' cocody',
            $name . ' plateau',
            $name . ' marcory',
        ];

        return implode(', ', $keywords);
    }
}

