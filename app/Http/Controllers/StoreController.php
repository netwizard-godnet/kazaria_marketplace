<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class StoreController extends Controller
{
    /**
     * Afficher le formulaire de création de boutique
     */
    public function create()
    {
        $categories = Category::with('subcategories')->get();
        return view('store.create', compact('categories'));
    }

    /**
     * Enregistrer une nouvelle boutique
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:stores,name',
            'description' => 'required|string|min:50',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:stores,email',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'dfe_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'commerce_register' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'facebook' => 'nullable|url',
            'instagram' => 'nullable|url',
            'twitter' => 'nullable|url',
            'website' => 'nullable|url',
        ]);

        try {
            DB::beginTransaction();

            $user = $request->user();

            // Vérifier si l'utilisateur a déjà une boutique
            if ($user->store()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà une boutique'
                ], 400);
            }

            $storeData = [
                'user_id' => $user->id,
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
                'status' => 'active', // Activation automatique (validation admin sera ajoutée plus tard)
                'is_verified' => true, // Vérification automatique pour le moment
            ];

            // Upload du logo
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('stores/logos', 'public');
                $storeData['logo'] = $logoPath;
            }

            // Upload de la bannière
            if ($request->hasFile('banner')) {
                $bannerPath = $request->file('banner')->store('stores/banners', 'public');
                $storeData['banner'] = $bannerPath;
            }

            // Upload du document DFE
            if ($request->hasFile('dfe_document')) {
                $dfePath = $request->file('dfe_document')->store('stores/documents', 'public');
                $storeData['dfe_document'] = $dfePath;
            }

            // Upload du registre de commerce
            if ($request->hasFile('commerce_register')) {
                $commercePath = $request->file('commerce_register')->store('stores/documents', 'public');
                $storeData['commerce_register'] = $commercePath;
            }

            // Liens sociaux
            $socialLinks = [];
            if ($request->facebook) $socialLinks['facebook'] = $request->facebook;
            if ($request->instagram) $socialLinks['instagram'] = $request->instagram;
            if ($request->twitter) $socialLinks['twitter'] = $request->twitter;
            if ($request->website) $socialLinks['website'] = $request->website;
            
            if (!empty($socialLinks)) {
                $storeData['social_links'] = $socialLinks;
            }

            // Créer la boutique
            $store = Store::create($storeData);

            // Mettre à jour l'utilisateur en tant que vendeur
            $user->update(['is_seller' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Félicitations ! Votre boutique a été créée avec succès.',
                'store_id' => $store->id,
                'redirect' => route('store.dashboard')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur création boutique: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la boutique: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Page d'attente de validation
     */
    public function pending()
    {
        $user = auth()->user();
        $store = $user->store;

        if (!$store) {
            return redirect()->route('store.create');
        }

        if ($store->status === 'active') {
            return redirect()->route('store.dashboard');
        }

        return view('store.pending', compact('store'));
    }

    /**
     * Dashboard de la boutique
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return redirect()->route('store.create');
        }

        if ($store->status !== 'active') {
            return redirect()->route('store.pending');
        }

        // Statistiques
        $stats = [
            'total_products' => $store->products()->count(),
            'total_orders' => 0, // À implémenter plus tard avec les vraies commandes
            'pending_orders' => 0,
            'total_sales' => $store->total_sales,
            'total_revenue' => $store->total_sales * (1 - $store->commission_rate / 100),
        ];

        // Récupérer les catégories pour le formulaire de modification
        $categories = Category::all();

        return view('store.dashboard', compact('store', 'stats', 'categories'));
    }

    /**
     * Afficher les détails de la boutique
     */
    public function show($slug, Request $request)
    {
        $store = Store::where('slug', $slug)
            ->where('status', 'active')
            ->with(['category', 'subcategory'])
            ->firstOrFail();
        
        // Métadonnées SEO
        $seoData = \App\Http\Controllers\SeoController::getStoreSeo($store);
        foreach ($seoData as $key => $value) {
            $seoKey = 'seo' . ucfirst($key);
            view()->share($seoKey, $value);
        }
        
        // Requête de base pour les produits
        $query = $store->products();
        
        // Filtres
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        if ($request->has('in_stock') && $request->in_stock == '1') {
            $query->where('stock', '>', 0);
        }
        
        // Tri
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('views', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
        
        $products = $query->paginate(20)->appends($request->except('page'));

        return view('store.show', compact('store', 'products'));
    }

    /**
     * Formulaire de modification de la boutique
     */
    public function edit()
    {
        $user = auth()->user();
        $store = $user->store;
        $categories = Category::all();

        return view('store.edit', compact('store', 'categories'));
    }

    /**
     * Mettre à jour la boutique
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        $request->validate([
            'name' => 'required|string|max:255|unique:stores,name,' . $store->id,
            'description' => 'required|string|min:50',
            'category_id' => 'required|exists:categories,id',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:stores,email,' . $store->id,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            $updateData = [
                'name' => $request->name,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
            ];

            // Upload du nouveau logo
            if ($request->hasFile('logo')) {
                // Supprimer l'ancien logo
                if ($store->logo) {
                    Storage::disk('public')->delete($store->logo);
                }
                $updateData['logo'] = $request->file('logo')->store('stores/logos', 'public');
            }

            // Upload de la nouvelle bannière
            if ($request->hasFile('banner')) {
                // Supprimer l'ancienne bannière
                if ($store->banner) {
                    Storage::disk('public')->delete($store->banner);
                }
                $updateData['banner'] = $request->file('banner')->store('stores/banners', 'public');
            }

            // Liens sociaux
            $socialLinks = $store->social_links ?? [];
            if ($request->has('facebook')) $socialLinks['facebook'] = $request->facebook;
            if ($request->has('instagram')) $socialLinks['instagram'] = $request->instagram;
            if ($request->has('twitter')) $socialLinks['twitter'] = $request->twitter;
            if ($request->has('website')) $socialLinks['website'] = $request->website;
            
            $updateData['social_links'] = $socialLinks;

            $store->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Boutique mise à jour avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour boutique: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Récupérer les statistiques de la boutique
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        // Rafraîchir les données depuis la base
        $store->refresh();

        $stats = [
            'total_products' => $store->products()->count(),
            'total_orders' => 0, // À implémenter
            'pending_orders' => 0, // À implémenter
            'total_sales' => $store->total_sales,
            'total_revenue' => $store->total_sales * (1 - $store->commission_rate / 100),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * API: Récupérer les commandes récentes
     */
    public function getRecentOrders(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $limit = $request->get('limit', 5);

        // Pour le moment, retourner un tableau vide
        // Plus tard, on récupérera les vraies commandes
        return response()->json([
            'success' => true,
            'orders' => []
        ]);
    }

    /**
     * API: Récupérer les produits de la boutique
     */
    public function getProducts(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $products = $store->products()
            ->select('id', 'name', 'price', 'stock', 'images')
            ->get()
            ->map(function($product) {
                // Gérer l'affichage de l'image
                $imageUrl = asset('images/produit.jpg'); // Image par défaut
                
                if ($product->images && is_array($product->images) && count($product->images) > 0) {
                    $firstImage = $product->images[0];
                    
                    // Vérifier si c'est une URL externe
                    if (filter_var($firstImage, FILTER_VALIDATE_URL)) {
                        $imageUrl = $firstImage;
                    }
                    // Vérifier si c'est un chemin storage (commence par "products/")
                    elseif (strpos($firstImage, 'products/') === 0) {
                        $imageUrl = asset('storage/' . $firstImage);
                    }
                    // Sinon, c'est dans public/images
                    else {
                        $imageUrl = asset('images/' . $firstImage);
                    }
                }
                
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image' => $imageUrl
                ];
            });

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

    /**
     * API: Récupérer les commandes de la boutique
     */
    public function getOrders(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        // Pour le moment, retourner un tableau vide
        // Plus tard, on récupérera les vraies commandes
        return response()->json([
            'success' => true,
            'orders' => []
        ]);
    }

    /**
     * API: Mettre à jour les informations de la boutique
     */
    public function updateStore(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|max:1000',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $store->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'address' => $request->address,
                'city' => $request->city,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Boutique mise à jour avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour boutique: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Upload du logo de la boutique
     */
    public function uploadLogo(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                
                // Créer un nom de fichier unique
                $filename = 'store_logo_' . $store->id . '_' . time() . '.' . $logo->getClientOriginalExtension();
                
                // Créer le dossier s'il n'existe pas
                $uploadPath = storage_path('app/public/stores/logos');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                
                // Supprimer l'ancien logo si il existe
                if ($store->logo && file_exists(storage_path('app/public/' . $store->logo))) {
                    unlink(storage_path('app/public/' . $store->logo));
                }
                
                // Déplacer le fichier
                $logo->move($uploadPath, $filename);
                
                // Mettre à jour le chemin du logo dans la base de données
                $logoPath = 'stores/logos/' . $filename;
                $store->update(['logo' => $logoPath]);

                return response()->json([
                    'success' => true,
                    'message' => 'Logo mis à jour avec succès',
                    'logo_url' => asset('storage/' . $logoPath)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Aucun fichier reçu'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Erreur upload logo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Upload de la bannière de la boutique
     */
    public function uploadBanner(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'banner' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->hasFile('banner')) {
                $banner = $request->file('banner');
                
                // Créer un nom de fichier unique
                $filename = 'store_banner_' . $store->id . '_' . time() . '.' . $banner->getClientOriginalExtension();
                
                // Créer le dossier s'il n'existe pas
                $uploadPath = storage_path('app/public/stores/banners');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                
                // Supprimer l'ancienne bannière si elle existe
                if ($store->banner && file_exists(storage_path('app/public/' . $store->banner))) {
                    unlink(storage_path('app/public/' . $store->banner));
                }
                
                // Déplacer le fichier
                $banner->move($uploadPath, $filename);
                
                // Mettre à jour le chemin de la bannière dans la base de données
                $bannerPath = 'stores/banners/' . $filename;
                $store->update(['banner' => $bannerPath]);

                return response()->json([
                    'success' => true,
                    'message' => 'Bannière mise à jour avec succès',
                    'banner_url' => asset('storage/' . $bannerPath)
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Aucun fichier reçu'
            ], 400);

        } catch (\Exception $e) {
            \Log::error('Erreur upload bannière: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Mettre à jour les liens sociaux de la boutique
     */
    public function updateSocialLinks(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $socialLinks = $store->social_links ?? [];
            
            if ($request->has('facebook')) $socialLinks['facebook'] = $request->facebook;
            if ($request->has('instagram')) $socialLinks['instagram'] = $request->instagram;
            if ($request->has('twitter')) $socialLinks['twitter'] = $request->twitter;
            if ($request->has('website')) $socialLinks['website'] = $request->website;

            $store->update(['social_links' => $socialLinks]);

            return response()->json([
                'success' => true,
                'message' => 'Liens sociaux mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour liens sociaux: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Basculer le statut de la boutique
     */
    public function toggleStatus(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'status' => 'required|in:pending,active,suspended,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Statut invalide'
            ], 422);
        }

        try {
            $store->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Statut de la boutique mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur changement statut boutique: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Supprimer la boutique
     */
    public function deleteStore(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        try {
            // Supprimer les fichiers de la boutique
            if ($store->logo && file_exists(storage_path('app/public/' . $store->logo))) {
                unlink(storage_path('app/public/' . $store->logo));
            }
            
            if ($store->banner && file_exists(storage_path('app/public/' . $store->banner))) {
                unlink(storage_path('app/public/' . $store->banner));
            }

            // Supprimer les produits et leurs images
            foreach ($store->products as $product) {
                if ($product->images && is_array($product->images)) {
                    foreach ($product->images as $image) {
                        if (strpos($image, 'products/') === 0 && file_exists(storage_path('app/public/' . $image))) {
                            unlink(storage_path('app/public/' . $image));
                        }
                    }
                }
            }

            // Supprimer la boutique (cascade supprimera les produits)
            $store->delete();

            // Mettre à jour le statut vendeur de l'utilisateur
            $user->update(['is_seller' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Boutique supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur suppression boutique: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }
}
