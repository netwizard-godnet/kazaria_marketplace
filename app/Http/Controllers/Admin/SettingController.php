<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        // S'assurer que les paramètres essentiels existent (bootstrap auto)
        if (!Setting::where('key', 'min_order_quantity')->exists()) {
            $this->createDefaultSettings();
        }

        // S'assurer que les médias par défaut existent dans storage/public
        $this->ensureDefaultMedia();

        $settings = Setting::orderBy('key')->get();

        // Remapping d'affichage (ne modifie pas la BDD)
        $displayGroupMap = [
            // Général
            'site_name' => 'general',
            'site_description' => 'general',
            'site_keywords' => 'general',
            'site_logo' => 'general',
            'site_favicon' => 'general',

            // Contact
            'contact_email' => 'contact',
            'contact_phone' => 'contact',
            'contact_address' => 'contact',

            // E‑commerce
            'min_order_quantity' => 'ecommerce',
            'free_shipping_threshold' => 'ecommerce',
            'shipping_cost' => 'ecommerce',
            'currency' => 'ecommerce',
            'currency_symbol' => 'ecommerce',

            // Deals du jour (forcer l'appartenance au même groupe)
            'deals_countdown_duration' => 'deals',
            'deals_min_discount' => 'deals',
            'deals_max_discount' => 'deals',
            'deals_categories' => 'deals',
            'deals_subcategories' => 'deals',
        ];

        // Ordre d'affichage par groupe
        $displayOrder = [
            'general' => ['site_name', 'site_description', 'site_keywords', 'site_logo', 'site_favicon'],
            'contact' => ['contact_email', 'contact_phone', 'contact_address'],
            'ecommerce' => ['currency', 'currency_symbol', 'min_order_quantity', 'shipping_cost', 'free_shipping_threshold'],
            // Toujours afficher les paramètres Deals ensemble et dans cet ordre
            'deals' => ['deals_countdown_duration', 'deals_min_discount', 'deals_max_discount', 'deals_categories', 'deals_subcategories'],
        ];

        // Construire les groupes pour la vue
        $grouped = [];
        foreach ($settings as $s) {
            $g = $displayGroupMap[$s->key] ?? $s->group ?? 'general';
            $grouped[$g] = $grouped[$g] ?? collect();
            $grouped[$g]->push($s);
        }

        // Appliquer l'ordre si défini
        $groups = collect($grouped)->map(function ($collection, $group) use ($displayOrder) {
            if (!empty($displayOrder[$group])) {
                $order = $displayOrder[$group];
                return $collection->sortBy(function ($item) use ($order) {
                    $pos = array_search($item->key, $order, true);
                    return $pos === false ? PHP_INT_MAX : $pos;
                })->values();
            }
            return $collection->sortBy('key')->values();
        });
        
        return view('admin.settings.index', compact('settings', 'groups'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:1024',
        ], [
            'logo.image' => 'Le logo doit être une image.',
            'logo.mimes' => 'Le logo doit être au format : jpeg, png, jpg, gif ou svg.',
            'logo.max' => 'Le logo ne doit pas dépasser 2 Mo.',
            'favicon.image' => 'Le favicon doit être une image.',
            'favicon.mimes' => 'Le favicon doit être au format : jpeg, png, jpg, gif, svg ou ico.',
            'favicon.max' => 'Le favicon ne doit pas dépasser 1 Mo.',
        ]);

        // Messages de succès pour les uploads
        $uploadMessages = [];

        // Traiter les paramètres
        foreach ($request->settings as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        // Gérer l'upload du logo
        if ($request->hasFile('logo')) {
            try {
                // Récupérer l'ancien logo AVANT la mise à jour
                $oldLogo = Setting::get('site_logo', '');
                
                $logo = $request->file('logo');
                $logoName = 'logo.' . $logo->getClientOriginalExtension();
                
                // Log pour debug
                \Log::info('Upload logo', [
                    'old_logo' => $oldLogo,
                    'new_logo_name' => $logoName,
                    'file_size' => $logo->getSize(),
                    'mime_type' => $logo->getMimeType()
                ]);
                
                // Stocker le fichier directement dans storage/app/public
                Storage::disk('public')->putFileAs('', $logo, $logoName);
                \Log::info('Logo stocké', ['filename' => $logoName]);
                
                // Mettre à jour en base
                Setting::set('site_logo', $logoName, 'string', 'general', 'Logo du site', true);
                
                // Nettoyer l'ancien logo s'il existe et est différent
                if ($oldLogo && $oldLogo !== $logoName && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                    \Log::info('Ancien logo supprimé', ['filename' => $oldLogo]);
                }
                
                $uploadMessages[] = 'Logo mis à jour avec succès.';
            } catch (\Exception $e) {
                \Log::error('Erreur upload logo', ['error' => $e->getMessage()]);
                $uploadMessages[] = 'Erreur lors de l\'upload du logo : ' . $e->getMessage();
            }
        }

        // Gérer l'upload du favicon
        if ($request->hasFile('favicon')) {
            try {
                // Récupérer l'ancien favicon AVANT la mise à jour
                $oldFavicon = Setting::get('site_favicon', '');
                
                $favicon = $request->file('favicon');
                $faviconName = 'favicon.' . $favicon->getClientOriginalExtension();
                
                // Log pour debug
                \Log::info('Upload favicon', [
                    'old_favicon' => $oldFavicon,
                    'new_favicon_name' => $faviconName,
                    'file_size' => $favicon->getSize(),
                    'mime_type' => $favicon->getMimeType()
                ]);
                
                // Stocker le fichier directement dans storage/app/public
                Storage::disk('public')->putFileAs('', $favicon, $faviconName);
                \Log::info('Favicon stocké', ['filename' => $faviconName]);
                
                // Mettre à jour en base
                Setting::set('site_favicon', $faviconName, 'string', 'general', 'Favicon du site', true);
                
                // Nettoyer l'ancien favicon s'il existe et est différent
                if ($oldFavicon && $oldFavicon !== $faviconName && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                    \Log::info('Ancien favicon supprimé', ['filename' => $oldFavicon]);
                }
                
                $uploadMessages[] = 'Favicon mis à jour avec succès.';
            } catch (\Exception $e) {
                \Log::error('Erreur upload favicon', ['error' => $e->getMessage()]);
                $uploadMessages[] = 'Erreur lors de l\'upload du favicon : ' . $e->getMessage();
            }
        }

        // Construire le message final
        $successMessage = 'Paramètres mis à jour avec succès.';
        if (!empty($uploadMessages)) {
            $successMessage .= ' ' . implode(' ', $uploadMessages);
        }

        return redirect()->back()->with('success', $successMessage);
    }

    public function reset()
    {
        Setting::truncate();
        $this->createDefaultSettings();
        
        return redirect()->back()->with('success', 'Paramètres réinitialisés aux valeurs par défaut.');
    }

    private function createDefaultSettings()
    {
        $defaultSettings = [
            // Général
            ['key' => 'site_name', 'value' => 'Kazaria', 'type' => 'string', 'group' => 'general', 'description' => 'Nom du site', 'is_public' => true],
            ['key' => 'site_description', 'value' => 'Marketplace en ligne', 'type' => 'string', 'group' => 'general', 'description' => 'Description du site', 'is_public' => true],
            ['key' => 'site_keywords', 'value' => 'marketplace, ecommerce, vente', 'type' => 'string', 'group' => 'general', 'description' => 'Mots-clés SEO', 'is_public' => true],
            ['key' => 'site_logo', 'value' => 'logo.png', 'type' => 'string', 'group' => 'general', 'description' => 'Logo du site', 'is_public' => true],
            ['key' => 'site_favicon', 'value' => 'favicon.ico', 'type' => 'string', 'group' => 'general', 'description' => 'Favicon du site', 'is_public' => true],
            
            // Contact
            ['key' => 'contact_email', 'value' => 'contact@kazaria.com', 'type' => 'string', 'group' => 'contact', 'description' => 'Email de contact', 'is_public' => true],
            ['key' => 'contact_phone', 'value' => '+225 07 00 00 00 00', 'type' => 'string', 'group' => 'contact', 'description' => 'Téléphone de contact', 'is_public' => true],
            ['key' => 'contact_address', 'value' => 'Abidjan, Côte d\'Ivoire', 'type' => 'string', 'group' => 'contact', 'description' => 'Adresse de contact', 'is_public' => true],
            
            // Réseaux sociaux
            ['key' => 'social_facebook', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'Page Facebook', 'is_public' => true],
            ['key' => 'social_twitter', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'Compte Twitter', 'is_public' => true],
            ['key' => 'social_instagram', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'Compte Instagram', 'is_public' => true],
            ['key' => 'social_linkedin', 'value' => '', 'type' => 'string', 'group' => 'social', 'description' => 'Page LinkedIn', 'is_public' => true],
            
            // E-commerce
            ['key' => 'min_order_quantity', 'value' => '1', 'type' => 'integer', 'group' => 'ecommerce', 'description' => 'Quantité minimale de commande', 'is_public' => true],
            ['key' => 'free_shipping_threshold', 'value' => '50000', 'type' => 'integer', 'group' => 'ecommerce', 'description' => 'Seuil de livraison gratuite (FCFA)', 'is_public' => true],
            ['key' => 'shipping_cost', 'value' => '5000', 'type' => 'integer', 'group' => 'ecommerce', 'description' => 'Coût de livraison (FCFA)', 'is_public' => true],
            ['key' => 'currency', 'value' => 'FCFA', 'type' => 'string', 'group' => 'ecommerce', 'description' => 'Devise', 'is_public' => true],
            ['key' => 'currency_symbol', 'value' => 'FCFA', 'type' => 'string', 'group' => 'ecommerce', 'description' => 'Symbole de la devise', 'is_public' => true],
            
            // Maintenance
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'maintenance', 'description' => 'Mode maintenance', 'is_public' => false],
            ['key' => 'maintenance_message', 'value' => 'Site en maintenance', 'type' => 'string', 'group' => 'maintenance', 'description' => 'Message de maintenance', 'is_public' => false],
        ];

        foreach ($defaultSettings as $setting) {
            // N'insère que les clés manquantes, ne remplace pas l'existant
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }

    /**
     * Copier les médias par défaut vers storage/public s'ils n'existent pas
     */
    private function ensureDefaultMedia(): void
    {
        // logo
        $logo = Setting::get('site_logo');
        if ($logo) {
            $path = ltrim($logo, '/');
            if (!Storage::disk('public')->exists($path)) {
                $publicCandidate = public_path($path);
                $fallbackCandidate = public_path('images/logo.png');
                if (is_file($publicCandidate)) {
                    Storage::disk('public')->put($path, file_get_contents($publicCandidate));
                } elseif (is_file($fallbackCandidate)) {
                    Storage::disk('public')->put($path, file_get_contents($fallbackCandidate));
                }
            }
        }

        // favicon
        $favicon = Setting::get('site_favicon');
        if ($favicon) {
            $path = ltrim($favicon, '/');
            if (!Storage::disk('public')->exists($path)) {
                $publicCandidate = public_path($path);
                $fallbackCandidate = public_path('favicon.ico');
                if (is_file($publicCandidate)) {
                    Storage::disk('public')->put($path, file_get_contents($publicCandidate));
                } elseif (is_file($fallbackCandidate)) {
                    Storage::disk('public')->put($path, file_get_contents($fallbackCandidate));
                }
            }
        }
    }
}
