<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateSeoCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seo:generate {--force : Force la régénération même si les fichiers existent}';

    /**
     * The console command description.
     */
    protected $description = 'Génère les fichiers SEO (sitemap, robots.txt) et optimise les métadonnées';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Génération des fichiers SEO pour KAZARIA...');

        // Générer le sitemap
        $this->generateSitemap();
        
        // Optimiser le robots.txt
        $this->optimizeRobotsTxt();
        
        // Vérifier les métadonnées
        $this->checkSeoMetadata();
        
        // Statistiques
        $this->showSeoStats();

        $this->info('✅ Génération SEO terminée avec succès !');
    }

    /**
     * Générer le sitemap XML
     */
    private function generateSitemap()
    {
        $this->info('📄 Génération du sitemap XML...');
        
        $sitemapPath = public_path('sitemap.xml');
        
        if (File::exists($sitemapPath) && !$this->option('force')) {
            $this->warn('Le sitemap existe déjà. Utilisez --force pour le régénérer.');
            return;
        }

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Page d'accueil
        $sitemap .= $this->addUrl(config('app.url'), '1.0', 'daily');
        
        // Pages statiques
        $staticPages = [
            ['url' => route('contact'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['url' => route('help-faq'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['url' => route('suivre-commande'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => route('expedition-livraison'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => route('politique-retour'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => route('comment-commander'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['url' => route('agences-points-relais'), 'priority' => '0.6', 'changefreq' => 'monthly'],
        ];
        
        foreach ($staticPages as $page) {
            $sitemap .= $this->addUrl($page['url'], $page['priority'], $page['changefreq']);
        }
        
        // Catégories
        $categories = Category::active()->get();
        foreach ($categories as $category) {
            $sitemap .= $this->addUrl(
                route('categorie', $category->slug), 
                '0.9', 
                'weekly',
                $category->updated_at
            );
        }
        
        // Produits
        $products = Product::active()->get();
        foreach ($products as $product) {
            $sitemap .= $this->addUrl(
                route('product-page', $product->slug), 
                '0.8', 
                'weekly',
                $product->updated_at
            );
        }
        
        // Boutiques
        $stores = Store::kycValidated()->get();
        foreach ($stores as $store) {
            $sitemap .= $this->addUrl(
                route('store.show', $store->slug), 
                '0.7', 
                'weekly',
                $store->updated_at
            );
        }
        
        $sitemap .= '</urlset>';
        
        File::put($sitemapPath, $sitemap);
        
        $this->info("✅ Sitemap généré : {$sitemapPath}");
        $this->info("   - " . ($categories->count() + $products->count() + $stores->count() + count($staticPages) + 1) . " URLs incluses");
    }

    /**
     * Optimiser le robots.txt
     */
    private function optimizeRobotsTxt()
    {
        $this->info('🤖 Optimisation du robots.txt...');
        
        $robotsPath = public_path('robots.txt');
        
        if (File::exists($robotsPath) && !$this->option('force')) {
            $this->warn('Le robots.txt existe déjà. Utilisez --force pour le régénérer.');
            return;
        }

        $robots = "User-agent: *\n";
        $robots .= "Allow: /\n\n";
        $robots .= "# Sitemap\n";
        $robots .= "Sitemap: " . config('app.url') . "/sitemap.xml\n\n";
        $robots .= "# Crawl-delay pour éviter la surcharge\n";
        $robots .= "Crawl-delay: 1\n\n";
        
        // Interdire l'accès aux dossiers sensibles
        $disallowPaths = config('seo.robots.disallow', []);
        foreach ($disallowPaths as $path) {
            $robots .= "Disallow: {$path}\n";
        }
        
        $robots .= "\n";
        
        // Autoriser les fichiers statiques
        $allowPaths = config('seo.robots.allow', []);
        foreach ($allowPaths as $path) {
            $robots .= "Allow: {$path}\n";
        }
        
        File::put($robotsPath, $robots);
        
        $this->info("✅ Robots.txt optimisé : {$robotsPath}");
    }

    /**
     * Vérifier les métadonnées SEO
     */
    private function checkSeoMetadata()
    {
        $this->info('🔍 Vérification des métadonnées SEO...');
        
        $issues = [];
        
        // Vérifier les produits sans description
        $productsWithoutDescription = Product::whereNull('description')->count();
        if ($productsWithoutDescription > 0) {
            $issues[] = "{$productsWithoutDescription} produits sans description";
        }
        
        // Vérifier les catégories sans description
        $categoriesWithoutDescription = Category::whereNull('description')->count();
        if ($categoriesWithoutDescription > 0) {
            $issues[] = "{$categoriesWithoutDescription} catégories sans description";
        }
        
        // Vérifier les produits sans images
        $productsWithoutImages = Product::where(function($query) {
            $query->whereNull('image')
                  ->orWhere('image', '');
        })->count();
        if ($productsWithoutImages > 0) {
            $issues[] = "{$productsWithoutImages} produits sans images";
        }
        
        if (empty($issues)) {
            $this->info('✅ Aucun problème SEO détecté');
        } else {
            $this->warn('⚠️  Problèmes SEO détectés :');
            foreach ($issues as $issue) {
                $this->warn("   - {$issue}");
            }
        }
    }

    /**
     * Afficher les statistiques SEO
     */
    private function showSeoStats()
    {
        $this->info('📊 Statistiques SEO :');
        
        $categories = Category::active()->count();
        $products = Product::active()->count();
        $stores = Store::kycValidated()->count();
        
        $this->table(
            ['Type', 'Nombre', 'SEO Status'],
            [
                ['Catégories', $categories, $categories > 0 ? '✅' : '❌'],
                ['Produits', $products, $products > 0 ? '✅' : '❌'],
                ['Boutiques', $stores, $stores > 0 ? '✅' : '❌'],
            ]
        );
    }

    /**
     * Ajouter une URL au sitemap
     */
    private function addUrl($url, $priority = '0.5', $changefreq = 'monthly', $lastmod = null)
    {
        $lastmod = $lastmod ? $lastmod->format('Y-m-d') : now()->format('Y-m-d');
        
        return '<url>' .
            '<loc>' . htmlspecialchars($url) . '</loc>' .
            '<lastmod>' . $lastmod . '</lastmod>' .
            '<changefreq>' . $changefreq . '</changefreq>' .
            '<priority>' . $priority . '</priority>' .
        '</url>';
    }
}
