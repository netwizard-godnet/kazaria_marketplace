<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Ajouter un nouveau produit
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez avoir une boutique pour ajouter des produits'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'warranty' => 'nullable|string|max:100',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'attributes' => 'nullable|array',
            'tags' => 'nullable|string',
        ]);

        try {
            // Générer le slug
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $count = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            // Upload des images
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $images[] = $path;
                }
            }

            // Calculer price, old_price et discount
            $finalPrice = $request->price; // Prix normal par défaut
            $oldPrice = null;
            $discount = 0;
            
            if ($request->promo_price && $request->promo_price < $request->price) {
                // Si prix promo fourni
                // price devient le prix actuel (promo)
                // old_price devient l'ancien prix (normal)
                $finalPrice = $request->promo_price;
                $oldPrice = $request->price;
                $discount = round((($request->price - $request->promo_price) / $request->price) * 100, 2);
            } elseif ($request->discount && $request->discount > 0) {
                // Si pourcentage fourni, calculer le prix actuel
                $discount = $request->discount;
                $oldPrice = $request->price;
                $finalPrice = $request->price * (1 - $discount / 100);
            }

            // Créer le produit
            $product = Product::create([
                'store_id' => $store->id,
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description,
                'price' => $finalPrice,
                'old_price' => $oldPrice,
                'discount_percentage' => $discount,
                'stock' => $request->stock,
                'brand' => $request->brand,
                'model' => $request->model,
                'warranty' => $request->warranty,
                'images' => $images,
                'attributes' => $request->attributes ?? [],
                'tags' => $request->tags ? explode(',', $request->tags) : [],
                'rating' => 0,
                'reviews_count' => 0,
                'views' => 0,
            ]);

            // Attacher à la catégorie de la boutique
            if ($store->subcategory_id) {
                // Si la boutique a une sous-catégorie, on attache le produit à cette sous-catégorie
                $product->subcategories()->attach($store->subcategory_id, [
                    'is_primary' => true,
                    'order' => 0
                ]);
                
                // Attacher aussi à la catégorie parente
                $product->categories()->attach($store->category_id, [
                    'is_primary' => false,
                    'order' => 0
                ]);
            } else {
                // Si la boutique n'a qu'une catégorie, on attache le produit à cette catégorie
                $product->categories()->attach($store->category_id, [
                    'is_primary' => true,
                    'order' => 0
                ]);
            }

            // Mettre à jour le compteur de produits de la boutique
            $store->increment('total_products');

            return response()->json([
                'success' => true,
                'message' => 'Produit ajouté avec succès',
                'product' => $product
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur ajout produit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout du produit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les détails d'un produit
     */
    public function show($id, Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        $product = Product::where('id', $id)
            ->where('store_id', $store->id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'product' => $product
        ]);
    }

    /**
     * Mettre à jour un produit
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user() ?? $request->user();
        $store = $user->store;

        $product = Product::where('id', $id)
            ->where('store_id', $store->id)
            ->firstOrFail();

        // Debug: Afficher les données reçues
        \Log::info('=== DONNÉES REÇUES POUR MISE À JOUR ===', ['all' => $request->all()]);
        \Log::info('Méthode HTTP: ' . $request->method());
        \Log::info('Content-Type: ' . $request->header('Content-Type'));
        \Log::info('Headers complets', ['headers' => $request->headers->all()]);
        \Log::info('Raw input: ' . $request->getContent());
        \Log::info('Champs spécifiques', [
            'name' => $request->get('name') ?: 'MANQUANT',
            'description' => $request->get('description') ?: 'MANQUANT',
            'price' => $request->get('price') ?: 'MANQUANT',
            'stock' => $request->get('stock') ?: 'MANQUANT',
            'brand' => $request->get('brand') ?: 'MANQUANT',
            'model' => $request->get('model') ?: 'MANQUANT',
            'warranty' => $request->get('warranty') ?: 'MANQUANT',
            'promo_price' => $request->get('promo_price') ?: 'MANQUANT',
            'discount' => $request->get('discount') ?: 'MANQUANT',
        ]);
        
        // Debug: Vérifier les données brutes
        \Log::info('=== DEBUG DONNÉES BRUTES ===');
        \Log::info('Request input', ['input' => $request->input()]);
        \Log::info('Request files', ['files' => $request->file()]);
        \Log::info('Request has name: ' . ($request->has('name') ? 'OUI' : 'NON'));
        \Log::info('Request has description: ' . ($request->has('description') ? 'OUI' : 'NON'));
        \Log::info('Request has price: ' . ($request->has('price') ? 'OUI' : 'NON'));
        \Log::info('Request has stock: ' . ($request->has('stock') ? 'OUI' : 'NON'));
        
        // Validation optimisée
        // Nettoyer les données avant validation
        $data = $request->all();
        
        // Nettoyer les champs obligatoires (supprimer les espaces)
        $data['name'] = trim($data['name'] ?? '');
        $data['description'] = trim($data['description'] ?? '');
        $data['price'] = trim($data['price'] ?? '');
        $data['stock'] = trim($data['stock'] ?? '');
        
        // S'assurer que les champs obligatoires ne sont pas des chaînes vides après trim
        if (empty($data['name'])) {
            $data['name'] = null;
        }
        if (empty($data['description'])) {
            $data['description'] = null;
        }
        if (empty($data['price'])) {
            $data['price'] = null;
        }
        if (empty($data['stock'])) {
            $data['stock'] = null;
        }
        
        // Convertir les chaînes vides en null pour les champs optionnels (avec vérification d'existence)
        $data['brand'] = isset($data['brand']) && $data['brand'] === '' ? null : ($data['brand'] ?? null);
        $data['model'] = isset($data['model']) && $data['model'] === '' ? null : ($data['model'] ?? null);
        $data['warranty'] = isset($data['warranty']) && $data['warranty'] === '' ? null : ($data['warranty'] ?? null);
        $data['promo_price'] = isset($data['promo_price']) && $data['promo_price'] === '' ? null : ($data['promo_price'] ?? null);
        $data['discount'] = isset($data['discount']) && $data['discount'] === '' ? null : ($data['discount'] ?? null);
        
        // Debug: Afficher les données nettoyées
        \Log::info('=== DONNÉES NETTOYÉES ===', [
            'name' => $data['name'],
            'description' => $data['description'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'brand' => $data['brand'],
            'model' => $data['model'],
            'warranty' => $data['warranty'],
            'promo_price' => $data['promo_price'],
            'discount' => $data['discount'],
        ]);
        
        // Remplacer les données dans la requête
        $request->merge($data);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:5',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'warranty' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'promo_price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'images_to_delete' => 'nullable|array',
            'images_to_delete.*' => 'nullable|string',
            'tags' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
        ], [
            'name.required' => 'Le nom du produit est obligatoire',
            'description.required' => 'La description est obligatoire',
            'description.min' => 'La description doit contenir au moins 5 caractères',
            'price.required' => 'Le prix est obligatoire',
            'price.numeric' => 'Le prix doit être un nombre',
            'price.min' => 'Le prix doit être supérieur à 0',
            'stock.required' => 'Le stock est obligatoire',
            'stock.integer' => 'Le stock doit être un nombre entier',
            'stock.min' => 'Le stock ne peut pas être négatif',
            'image.image' => 'Le fichier doit être une image',
            'image.mimes' => 'L\'image doit être au format JPG, PNG ou GIF',
            'image.max' => 'L\'image ne doit pas dépasser 5MB',
            'promo_price.numeric' => 'Le prix promo doit être un nombre',
            'promo_price.min' => 'Le prix promo ne peut pas être négatif',
            'discount.numeric' => 'La réduction doit être un nombre',
            'discount.min' => 'La réduction ne peut pas être négative',
            'discount.max' => 'La réduction ne peut pas dépasser 100%',
        ]);

        try {
            // Calculer price, old_price et discount
            $finalPrice = $request->price; // Prix normal par défaut
            $oldPrice = null;
            $discount = 0;
            
            if ($request->promo_price && $request->promo_price < $request->price) {
                // Si prix promo fourni
                // price devient le prix actuel (promo)
                // old_price devient l'ancien prix (normal)
                $finalPrice = $request->promo_price;
                $oldPrice = $request->price;
                $discount = round((($request->price - $request->promo_price) / $request->price) * 100, 2);
            } elseif ($request->discount && $request->discount > 0) {
                // Si pourcentage fourni, calculer le prix actuel
                $discount = $request->discount;
                $oldPrice = $request->price;
                $finalPrice = $request->price * (1 - $discount / 100);
            }

            // Gestion des images
            $updateData = [
                'name' => $request->name,
                'description' => $request->description,
                'price' => $finalPrice,
                'old_price' => $oldPrice,
                'discount_percentage' => $discount,
                'stock' => $request->stock,
                'brand' => $request->brand,
                'model' => $request->model,
                'warranty' => $request->warranty,
                'is_active' => $request->boolean('is_active'),
                'tags' => $request->tags ? explode(',', str_replace(' ', '', $request->tags)) : [],
                'category_id' => $request->category_id,
                'subcategory_id' => $request->subcategory_id ?: null,
            ];

            // Gestion de la suppression d'image
            if ($request->has('remove_image') && $request->remove_image) {
                // Supprimer l'ancienne image du stockage
                if ($product->image && \Storage::disk('public')->exists($product->image)) {
                    \Storage::disk('public')->delete($product->image);
                }
                $updateData['image'] = null;
            }

            // Gestion de la nouvelle image principale
            $newMainImage = null;
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image si elle existe
                if ($product->image && \Storage::disk('public')->exists($product->image)) {
                    \Storage::disk('public')->delete($product->image);
                }
                
                // Stocker la nouvelle image
                $imagePath = $request->file('image')->store('products', 'public');
                $updateData['image'] = $imagePath;
                $newMainImage = $imagePath; // Sauvegarder pour l'ajouter au tableau images
            }

            // Gestion complète des images
            // 1. Construire la liste complète des images actuelles
            $allCurrentImages = [];
            
            // Ajouter l'image principale si elle existe
            if ($product->image) {
                $allCurrentImages[] = $product->image;
            }
            
            // Ajouter les autres images (sans dupliquer l'image principale)
            if ($product->images && is_array($product->images)) {
                foreach ($product->images as $img) {
                    if ($img !== $product->image && $img) {
                        $allCurrentImages[] = $img;
                    }
                }
            }
            
            // 2. Supprimer les images marquées pour suppression
            $imagesToDelete = $request->input('images_to_delete', []);
            if (!empty($imagesToDelete)) {
                foreach ($imagesToDelete as $imageUrl) {
                    $key = array_search($imageUrl, $allCurrentImages);
                    if ($key !== false) {
                        // Supprimer le fichier du stockage
                        if (\Storage::disk('public')->exists($imageUrl)) {
                            \Storage::disk('public')->delete($imageUrl);
                        }
                        unset($allCurrentImages[$key]);
                    }
                }
            }
            
            // 3. Ajouter les nouvelles images supplémentaires
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('products', 'public');
                    $allCurrentImages[] = $imagePath;
                }
            }
            
            // 4. Si une nouvelle image principale est ajoutée
            if ($newMainImage) {
                // Supprimer l'ancienne image principale du tableau
                $key = array_search($product->image, $allCurrentImages);
                if ($key !== false) {
                    unset($allCurrentImages[$key]);
                }
                // Ajouter la nouvelle au début
                array_unshift($allCurrentImages, $newMainImage);
            }
            
            // 5. Séparer l'image principale du reste
            // Réindexer le tableau
            $allCurrentImages = array_values($allCurrentImages);
            
            if (!empty($allCurrentImages)) {
                // La première image devient l'image principale
                $updateData['image'] = $allCurrentImages[0];
                // Le reste va dans images
                $updateData['images'] = array_slice($allCurrentImages, 1);
            } else {
                // Pas d'images
                $updateData['image'] = null;
                $updateData['images'] = [];
            }

            $product->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Produit mis à jour avec succès',
                'product' => $product
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour produit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un produit
     */
    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $store = $user->store;

        $product = Product::where('id', $id)
            ->where('store_id', $store->id)
            ->firstOrFail();

        try {
            // Supprimer les images
            if ($product->images && is_array($product->images)) {
                foreach ($product->images as $image) {
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }

            // Décrémenter le compteur
            $store->decrement('total_products');

            // Supprimer le produit
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur suppression produit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload d'images supplémentaires
     */
    public function uploadImages(Request $request, $id)
    {
        $user = $request->user();
        $store = $user->store;

        $product = Product::where('id', $id)
            ->where('store_id', $store->id)
            ->firstOrFail();

        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            $currentImages = $product->images ?? [];
            
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('products', 'public');
                    $currentImages[] = $path;
                }
            }

            $product->update(['images' => $currentImages]);

            return response()->json([
                'success' => true,
                'message' => 'Images ajoutées avec succès',
                'images' => $currentImages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une image
     */
    public function deleteImage(Request $request, $id)
    {
        $user = $request->user();
        $store = $user->store;

        $product = Product::where('id', $id)
            ->where('store_id', $store->id)
            ->firstOrFail();

        $request->validate([
            'image_path' => 'required|string',
        ]);

        try {
            $currentImages = $product->images ?? [];
            $imagePath = $request->image_path;

            // Supprimer du storage
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            // Retirer du tableau
            $currentImages = array_values(array_filter($currentImages, function($img) use ($imagePath) {
                return $img !== $imagePath;
            }));

            $product->update(['images' => $currentImages]);

            return response()->json([
                'success' => true,
                'message' => 'Image supprimée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un produit spécifique
     */
    public function getProduct(Request $request, $id)
    {
        $user = auth()->user() ?? $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez avoir une boutique pour accéder aux produits'
            ], 403);
        }

        try {
            $product = $store->products()
                ->with(['category', 'subcategory'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->old_price ?: $product->price, // Prix original ou prix actuel si pas de promo
                    'promo_price' => $product->price, // Prix actuel (promo)
                    'stock' => $product->stock,
                    'discount' => $product->discount_percentage,
                    'brand' => $product->brand,
                    'model' => $product->model,
                    'warranty' => $product->warranty,
                    'status' => $product->is_active ? 'active' : 'inactive',
                    'category_id' => $product->category_id,
                    'subcategory_id' => $product->subcategory_id,
                    'image' => $product->image,
                    'images' => is_array($product->images) ? array_unique($product->images, SORT_REGULAR) : $product->images,
                    'tags' => $product->tags,
                    'attributes' => $product->attributes,
                    'created_at' => $product->created_at,
                    'updated_at' => $product->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Produit non trouvé'
            ], 404);
        }
    }

    /**
     * Récupérer la liste des produits
     */
    public function getProducts(Request $request)
    {
        $user = auth()->user() ?? $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez avoir une boutique pour accéder aux produits'
            ], 403);
        }

        try {
            $products = $store->products()
                ->with(['category', 'subcategory'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'products' => $products->map(function($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->nom,
                        'description' => $product->description,
                        'price' => $product->price,
                        'promo_price' => $product->promo_price,
                        'stock' => $product->stock,
                        'discount' => $product->discount,
                        'brand' => $product->brand,
                        'model' => $product->model,
                        'warranty' => $product->warranty,
                        'status' => $product->status,
                        'category_id' => $product->category_id,
                        'subcategory_id' => $product->subcategory_id,
                        'image' => $product->image,
                        'images' => $product->images,
                        'tags' => $product->tags,
                        'attributes' => $product->attributes,
                        'created_at' => $product->created_at,
                        'updated_at' => $product->updated_at
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des produits'
            ], 500);
        }
    }

    /**
     * Créer un nouveau produit
     */
    public function createProduct(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Mettre à jour un produit
     */
    public function updateProduct(Request $request, $id)
    {
        return $this->update($request, $id);
    }

    /**
     * Supprimer un produit
     */
    public function deleteProduct(Request $request, $id)
    {
        return $this->destroy($id, $request);
    }
}
