<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Générer le sitemap XML
     */
    public function index()
    {
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
        $stores = Store::where('status', 'active')->get();
        foreach ($stores as $store) {
            $sitemap .= $this->addUrl(
                route('store.show', $store->slug), 
                '0.7', 
                'weekly',
                $store->updated_at
            );
        }
        
        $sitemap .= '</urlset>';
        
        return response($sitemap, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600'
        ]);
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
