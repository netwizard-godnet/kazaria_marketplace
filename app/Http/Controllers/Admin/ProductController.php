<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\ProductVariation;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['store', 'category', 'subcategory']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('price', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filtre par tendance
        if ($request->filled('trending')) {
            $query->where('is_trending', $request->trending === 'yes');
        }

        $products = $query->latest()->paginate(60)->withQueryString();

        return view('admin.products.index', compact('products'));
    }


    public function show(Product $product)
    {
        $product->load([
            'store', 
            'category', 
            'subcategory',
            'attributeValues.attribute',
            'variations.attributeValues.attribute',
            'reviews' => function($query) {
                $query->latest()->limit(5);
            }
        ]);
        
        // Calculer le nombre total d'avis et la note moyenne si nécessaire
        if (!$product->reviews_count && $product->reviews) {
            $product->reviews_count = $product->reviews->count();
        }
        
        return view('admin.products.show', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'store_id' => 'nullable|exists:stores,id',
            'status' => 'required|in:pending,approved,rejected',
            'is_active' => 'boolean',
            'is_trending' => 'boolean',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'warranty' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'attributes' => 'nullable|array',
            'enable_variations' => 'nullable|boolean',
            'variations' => 'nullable|array',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.attributes' => 'nullable|array',
            'variations.*.price' => 'required_with:variations|numeric|min:0',
            'variations.*.promo_price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'required_with:variations|integer|min:0',
            'variations.*.sku' => 'nullable|string|max:255',
            'variations.*.is_default' => 'nullable|boolean',
            'variations_to_delete' => 'nullable|array',
            'variations_to_delete.*' => 'exists:product_variations,id',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'subcategories' => 'nullable|array',
            'subcategories.*' => 'exists:subcategories,id',
        ]);

        $data = $request->only([
            'name', 'description', 'stock', 'category_id', 
            'subcategory_id', 'store_id', 'brand', 'model', 'warranty',
            'meta_description', 'meta_keywords', 'status'
        ]);
        
        // Gérer store_id : si vide, mettre null
        if (empty($data['store_id'])) {
            $data['store_id'] = null;
        }
        
        $data['is_active'] = $request->has('is_active');
        $data['is_trending'] = $request->has('is_trending');
        
        // Gestion des prix : convertir prix normal et prix promo en price, old_price et discount
        $normalPrice = $request->price;
        $promoPrice = $request->promo_price;
        
        if ($promoPrice && $promoPrice > 0 && $promoPrice < $normalPrice) {
            // Produit en promo : price = prix promo (prix actuel), old_price = prix normal (ancien prix)
            $data['price'] = $promoPrice;
            $data['old_price'] = $normalPrice;
            $data['discount_percentage'] = round((($normalPrice - $promoPrice) / $normalPrice) * 100, 2);
        } else {
            // Pas de promo : price = prix normal, pas d'old_price
            $data['price'] = $normalPrice;
            $data['old_price'] = null;
            $data['discount_percentage'] = $request->discount_percentage ?: null;
        }

        // Gestion des images
        $currentImages = $product->images ?? [];

        // Supprimer les images marquées pour suppression
        if ($request->has('images_to_remove')) {
            $imagesToRemove = $request->images_to_remove;
            foreach ($imagesToRemove as $index) {
                if (isset($currentImages[$index])) {
                    // Supprimer le fichier du storage
                    $imagePath = storage_path('app/public/' . $currentImages[$index]);
                    if (File::exists($imagePath)) {
                        File::delete($imagePath);
                    }
                    // Retirer du tableau
                    unset($currentImages[$index]);
                }
            }
            // Réindexer le tableau
            $currentImages = array_values($currentImages);
        }

        // Ajouter les nouvelles images
        $newImages = [];
        if ($request->hasFile('images')) {
            $uploadedImages = $request->file('images');
            $mainNewImageIndex = $request->input('main_new_image_index');
            
            // Convertir en tableau indexé
            $uploadedImagesArray = [];
            foreach ($uploadedImages as $index => $img) {
                if ($img && $img->isValid()) {
                    $uploadedImagesArray[$index] = $img;
                }
            }
            
            // Réorganiser les nouvelles images si une principale est définie
            if ($mainNewImageIndex !== null && $mainNewImageIndex !== '' && isset($uploadedImagesArray[$mainNewImageIndex])) {
                $mainImage = $uploadedImagesArray[$mainNewImageIndex];
                unset($uploadedImagesArray[$mainNewImageIndex]);
                $uploadedImagesArray = array_merge([$mainImage], array_values($uploadedImagesArray));
            } else {
                $uploadedImagesArray = array_values($uploadedImagesArray);
            }
            
            // Uploader toutes les nouvelles images
            foreach ($uploadedImagesArray as $img) {
                $newImages[] = $img->store('products', 'public');
            }
        }

        // Gérer la réorganisation : déterminer quelle image est principale
        $mainExistingImageIndex = $request->input('main_existing_image_index');
        $hasNewMainImage = $request->input('main_new_image_index') !== null && $request->input('main_new_image_index') !== '';
        
        // Si une nouvelle image est principale, la mettre en premier de toutes les images
        if ($hasNewMainImage && !empty($newImages)) {
            $finalImages = array_merge([$newImages[0]], $currentImages, array_slice($newImages, 1));
        }
        // Si une image existante est principale, la mettre en premier
        elseif ($mainExistingImageIndex !== null && isset($currentImages[$mainExistingImageIndex])) {
            $mainImage = $currentImages[$mainExistingImageIndex];
            unset($currentImages[$mainExistingImageIndex]);
            $finalImages = array_merge([$mainImage], array_values($currentImages), $newImages);
        }
        // Sinon, garder l'ordre actuel (existantes + nouvelles)
        else {
            $finalImages = array_merge($currentImages, $newImages);
        }

        $data['images'] = $finalImages;
        // Définir l'image principale (première image du tableau)
        if (!empty($finalImages)) {
            $data['image'] = $finalImages[0];
        }
        
        // Gestion des tags (séparer par virgules)
        if ($request->filled('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
            $data['tags'] = array_filter($tags); // Supprimer les valeurs vides
        }

        // Validation : au moins une catégorie doit être sélectionnée
        if (!$request->has('categories') || empty($request->categories)) {
            if (!$request->has('category_id') || empty($request->category_id)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['categories' => 'Veuillez sélectionner au moins une catégorie.']);
            }
        }

        $product->update($data);

        // Gestion des attributs
        if ($request->has('attributes')) {
            $attributesData = $request->input('attributes');
            $attributeValueIds = [];
            foreach ($attributesData as $attributeId => $valueIds) {
                if (is_array($valueIds)) {
                    $attributeValueIds = array_merge($attributeValueIds, $valueIds);
                } else {
                    $attributeValueIds[] = $valueIds;
                }
            }
            $product->attributeValues()->sync($attributeValueIds);
        } else {
            $product->attributeValues()->detach();
        }

        // Gestion des variations de produits
        // Supprimer les variations marquées pour suppression
        if ($request->has('variations_to_delete')) {
            ProductVariation::whereIn('id', $request->variations_to_delete)
                ->where('product_id', $product->id)
                ->delete();
        }

        // Mettre à jour ou créer les variations
        if ($request->has('enable_variations') && $request->enable_variations && $request->has('variations')) {
            $variations = $request->input('variations', []);
            $hasDefault = false;
            $variationIds = [];
            
            foreach ($variations as $index => $variationData) {
                // Vérifier que la variation a au moins un attribut
                if (empty($variationData['attributes']) || !is_array($variationData['attributes'])) {
                    continue;
                }
                
                // Calculer les prix
                $variationPrice = $variationData['price'] ?? 0;
                $variationPromoPrice = $variationData['promo_price'] ?? null;
                $variationOldPrice = null;
                $variationDiscount = null;
                
                if ($variationPromoPrice && $variationPromoPrice > 0 && $variationPromoPrice < $variationPrice) {
                    $variationOldPrice = $variationPrice;
                    $variationPrice = $variationPromoPrice;
                    $variationDiscount = round((($variationOldPrice - $variationPrice) / $variationOldPrice) * 100, 2);
                }
                
                // Générer le SKU si non fourni
                $sku = $variationData['sku'] ?? null;
                if (!$sku && isset($variationData['id'])) {
                    $existingVariation = ProductVariation::find($variationData['id']);
                    $sku = $existingVariation ? $existingVariation->sku : ProductVariation::generateSku(
                        $product->id, 
                        array_values($variationData['attributes'])
                    );
                } elseif (!$sku) {
                    $sku = ProductVariation::generateSku(
                        $product->id, 
                        array_values($variationData['attributes'])
                    );
                }
                
                // Vérifier si c'est une mise à jour ou création
                if (isset($variationData['id']) && $variationData['id']) {
                    // Mettre à jour la variation existante
                    $variation = ProductVariation::where('id', $variationData['id'])
                        ->where('product_id', $product->id)
                        ->first();
                    
                    if ($variation) {
                        $variation->update([
                            'sku' => $sku,
                            'price' => $variationPrice,
                            'old_price' => $variationOldPrice,
                            'discount_percentage' => $variationDiscount,
                            'stock' => $variationData['stock'] ?? 0,
                            'is_default' => !$hasDefault && ($variationData['is_default'] ?? false),
                            'is_active' => true,
                            'order' => $index
                        ]);
                        
                        // Mettre à jour les attributs
                        $attributeValueIds = array_values(array_filter($variationData['attributes']));
                        if (!empty($attributeValueIds)) {
                            $variation->attributeValues()->sync($attributeValueIds);
                        }
                        
                        $variationIds[] = $variation->id;
                        if ($variation->is_default) {
                            $hasDefault = true;
                        }
                    }
                } else {
                    // Créer une nouvelle variation
                    $variation = ProductVariation::create([
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'price' => $variationPrice,
                        'old_price' => $variationOldPrice,
                        'discount_percentage' => $variationDiscount,
                        'stock' => $variationData['stock'] ?? 0,
                        'is_default' => !$hasDefault && ($variationData['is_default'] ?? false),
                        'is_active' => true,
                        'order' => $index
                    ]);
                    
                    // Lier les valeurs d'attributs
                    $attributeValueIds = array_values(array_filter($variationData['attributes']));
                    if (!empty($attributeValueIds)) {
                        $variation->attributeValues()->attach($attributeValueIds);
                    }
                    
                    $variationIds[] = $variation->id;
                    if ($variation->is_default) {
                        $hasDefault = true;
                    }
                }
            }
            
            // S'il n'y a pas de variation par défaut, définir la première comme défaut
            if (!$hasDefault && !empty($variations)) {
                $firstVariation = $product->variations()->orderBy('order')->first();
                if ($firstVariation) {
                    $firstVariation->update(['is_default' => true]);
                }
            }
            
            // Supprimer les variations qui ne sont plus dans la liste (si enable_variations est activé)
            if (!empty($variationIds)) {
                ProductVariation::where('product_id', $product->id)
                    ->whereNotIn('id', $variationIds)
                    ->delete();
            }
        } elseif ($request->has('enable_variations') && !$request->enable_variations) {
            // Si les variations sont désactivées, supprimer toutes les variations
            ProductVariation::where('product_id', $product->id)->delete();
        }

        // Gestion des catégories multiples (many-to-many)
        if ($request->has('categories') && is_array($request->categories)) {
            // Si categories[] est présent (même vide), on synchronise avec ce qui est fourni
            $categoriesData = [];
            foreach ($request->categories as $index => $categoryId) {
                if (!empty($categoryId)) { // Ignorer les valeurs vides
                    $categoriesData[$categoryId] = [
                        'is_primary' => $index === 0, // Première catégorie = principale
                        'order' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            $product->categories()->sync($categoriesData);
        } elseif ($request->has('category_id') && !empty($request->category_id)) {
            // Compatibilité avec l'ancien système : si category_id est fourni mais pas categories[]
            $product->categories()->sync([
                $request->category_id => [
                    'is_primary' => true,
                    'order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        // Gestion des sous-catégories multiples (many-to-many)
        if ($request->has('subcategories') && is_array($request->subcategories)) {
            // Si subcategories[] est présent (même vide), on synchronise avec ce qui est fourni
            $subcategoriesData = [];
            foreach ($request->subcategories as $index => $subcategoryId) {
                if (!empty($subcategoryId)) { // Ignorer les valeurs vides
                    $subcategoriesData[$subcategoryId] = [
                        'is_primary' => $index === 0, // Première sous-catégorie = principale
                        'order' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            $product->subcategories()->sync($subcategoriesData);
        } elseif ($request->has('subcategory_id') && !empty($request->subcategory_id)) {
            // Compatibilité avec l'ancien système : si subcategory_id est fourni mais pas subcategories[]
            $product->subcategories()->sync([
                $request->subcategory_id => [
                    'is_primary' => true,
                    'order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->with('success', 'Produit supprimé avec succès.');
    }

    public function approve(Product $product)
    {
        $product->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Produit approuvé avec succès.');
    }

    public function reject(Product $product)
    {
        $product->update(['status' => 'rejected']);
        return redirect()->back()->with('success', 'Produit rejeté avec succès.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'activé' : 'désactivé';
        return redirect()->back()->with('success', "Produit {$status} avec succès.");
    }

    public function toggleTrending(Product $product)
    {
        $product->update(['is_trending' => !$product->is_trending]);
        $status = $product->is_trending ? 'marqué comme tendance' : 'retiré des tendances';
        return redirect()->back()->with('success', "Produit {$status} avec succès.");
    }

    public function edit(Product $product)
    {
        $product->load([
            'store', 
            'category', 
            'subcategory',
            'categories',
            'subcategories',
            'attributeValues.attribute',
            'variations.attributeValues.attribute'
        ]);
        $categories = Category::all();
        $attributes = Attribute::with('attributeValues')->ordered()->get();
        $stores = \App\Models\Store::with('user')->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories', 'attributes', 'stores'));
    }

    public function deleteImage(Product $product, $index)
    {
        $images = $product->images ?? [];
        
        if (isset($images[$index])) {
            // Supprimer le fichier du storage
            $imagePath = storage_path('app/public/' . $images[$index]);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
            
            // Retirer du tableau
            unset($images[$index]);
            $images = array_values($images); // Réindexer
            
            // Mettre à jour le produit
            $product->update(['images' => $images]);
            
            return response()->json(['success' => true, 'message' => 'Image supprimée avec succès']);
        }
        
        return response()->json(['success' => false, 'message' => 'Image non trouvée'], 404);
    }

    public function create()
    {
        $categories = Category::all();
        $attributes = Attribute::with('attributeValues')->ordered()->get();
        $stores = \App\Models\Store::with('user')->orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'attributes', 'stores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'store_id' => 'nullable|exists:stores,id',
            'status' => 'required|in:pending,approved,rejected',
            'is_active' => 'boolean',
            'is_trending' => 'boolean',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'warranty' => 'nullable|string|max:100',
            'tags' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'attributes' => 'nullable|array',
            'enable_variations' => 'nullable|boolean',
            'variations' => 'nullable|array',
            'variations.*.attributes' => 'nullable|array',
            'variations.*.price' => 'required_with:variations|numeric|min:0',
            'variations.*.promo_price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'required_with:variations|integer|min:0',
            'variations.*.sku' => 'nullable|string|max:255',
            'variations.*.is_default' => 'nullable|boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'subcategories' => 'nullable|array',
            'subcategories.*' => 'exists:subcategories,id',
        ]);

        $data = $request->only([
            'name', 'description', 'stock', 'category_id', 
            'subcategory_id', 'store_id', 'brand', 'model', 'warranty',
            'meta_description', 'meta_keywords'
        ]);
        
        // Gérer store_id : si vide, mettre null
        if (empty($data['store_id'])) {
            $data['store_id'] = null;
        }

        // Gestion des prix : convertir prix normal et prix promo en price, old_price et discount
        $normalPrice = $request->price;
        $promoPrice = $request->promo_price;
        
        if ($promoPrice && $promoPrice > 0 && $promoPrice < $normalPrice) {
            // Produit en promo : price = prix promo (prix actuel), old_price = prix normal (ancien prix)
            $data['price'] = $promoPrice;
            $data['old_price'] = $normalPrice;
            $data['discount_percentage'] = round((($normalPrice - $promoPrice) / $normalPrice) * 100, 2);
        } else {
            // Pas de promo : price = prix normal, pas d'old_price
            $data['price'] = $normalPrice;
            $data['old_price'] = null;
            $data['discount_percentage'] = $request->discount_percentage ?: null;
        }

        // Gestion des images - Au moins une image est requise
        $images = [];
        if ($request->hasFile('images')) {
            $uploadedImages = $request->file('images');
            $mainImageIndex = (int) $request->input('main_image_index', 0);
            
            // Convertir en tableau indexé
            $uploadedImagesArray = [];
            foreach ($uploadedImages as $index => $img) {
                if ($img && $img->isValid()) {
                    $uploadedImagesArray[$index] = $img;
                }
            }
            
            // Réorganiser pour mettre l'image principale en première position
            if ($mainImageIndex > 0 && isset($uploadedImagesArray[$mainImageIndex])) {
                // Extraire l'image principale
                $mainImage = $uploadedImagesArray[$mainImageIndex];
                unset($uploadedImagesArray[$mainImageIndex]);
                
                // Mettre l'image principale en premier
                $uploadedImagesArray = array_merge([$mainImage], $uploadedImagesArray);
            } else {
                // Si pas d'index spécifique ou index invalide, utiliser le tableau tel quel
                $uploadedImagesArray = array_values($uploadedImagesArray);
            }
            
            // Uploader toutes les images dans l'ordre
            foreach ($uploadedImagesArray as $img) {
                $images[] = $img->store('products', 'public');
            }
        }
        
        if (empty($images)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['images' => 'Au moins une image est requise pour le produit.']);
        }
        
        $data['images'] = $images;
        // Définir l'image principale (première image du tableau)
        $data['image'] = $images[0];

        // Gestion des tags (séparer par virgules)
        if ($request->filled('tags')) {
            $tags = array_map('trim', explode(',', $request->tags));
            $data['tags'] = array_filter($tags); // Supprimer les valeurs vides
        }

        // Statut et actif
        $data['is_active'] = $request->has('is_active');
        $data['is_trending'] = $request->has('is_trending');
        $data['status'] = $request->status ?? 'pending';

        // Validation : au moins une catégorie doit être sélectionnée
        if (!$request->has('categories') || empty($request->categories)) {
            if (!$request->has('category_id') || empty($request->category_id)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['categories' => 'Veuillez sélectionner au moins une catégorie.']);
            }
        }

        // Créer le produit
        $product = Product::create($data);

        // Gestion des attributs
        if ($request->has('attributes')) {
            $attributesData = $request->input('attributes');
            $attributeValueIds = [];
            foreach ($attributesData as $attributeId => $valueIds) {
                if (is_array($valueIds)) {
                    $attributeValueIds = array_merge($attributeValueIds, $valueIds);
                } else {
                    $attributeValueIds[] = $valueIds;
                }
            }
            if (!empty($attributeValueIds)) {
                $product->attributeValues()->attach($attributeValueIds);
            }
        }

        // Gestion des variations de produits
        if ($request->has('enable_variations') && $request->enable_variations && $request->has('variations')) {
            $variations = $request->input('variations', []);
            $hasDefault = false;
            
            foreach ($variations as $index => $variationData) {
                // Vérifier que la variation a au moins un attribut
                if (empty($variationData['attributes']) || !is_array($variationData['attributes'])) {
                    continue;
                }
                
                // Calculer les prix
                $variationPrice = $variationData['price'] ?? 0;
                $variationPromoPrice = $variationData['promo_price'] ?? null;
                $variationOldPrice = null;
                $variationDiscount = null;
                
                if ($variationPromoPrice && $variationPromoPrice > 0 && $variationPromoPrice < $variationPrice) {
                    $variationOldPrice = $variationPrice;
                    $variationPrice = $variationPromoPrice;
                    $variationDiscount = round((($variationOldPrice - $variationPrice) / $variationOldPrice) * 100, 2);
                }
                
                // Générer le SKU si non fourni
                $sku = $variationData['sku'] ?? ProductVariation::generateSku(
                    $product->id, 
                    array_values($variationData['attributes'])
                );
                
                // Créer la variation
                $variation = ProductVariation::create([
                    'product_id' => $product->id,
                    'sku' => $sku,
                    'price' => $variationPrice,
                    'old_price' => $variationOldPrice,
                    'discount_percentage' => $variationDiscount,
                    'stock' => $variationData['stock'] ?? 0,
                    'is_default' => !$hasDefault && ($variationData['is_default'] ?? false),
                    'is_active' => true,
                    'order' => $index
                ]);
                
                if ($variation->is_default) {
                    $hasDefault = true;
                }
                
                // Lier les valeurs d'attributs à la variation
                $attributeValueIds = array_values(array_filter($variationData['attributes']));
                if (!empty($attributeValueIds)) {
                    $variation->attributeValues()->attach($attributeValueIds);
                }
            }
            
            // S'il n'y a pas de variation par défaut, définir la première comme défaut
            if (!$hasDefault && !empty($variations)) {
                $firstVariation = $product->variations()->first();
                if ($firstVariation) {
                    $firstVariation->update(['is_default' => true]);
                }
            }
        }

        // Gestion des catégories multiples (many-to-many)
        if ($request->has('categories') && is_array($request->categories)) {
            // Si categories[] est présent (même vide), on synchronise avec ce qui est fourni
            $categoriesData = [];
            foreach ($request->categories as $index => $categoryId) {
                if (!empty($categoryId)) { // Ignorer les valeurs vides
                    $categoriesData[$categoryId] = [
                        'is_primary' => $index === 0, // Première catégorie = principale
                        'order' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            $product->categories()->sync($categoriesData);
        } elseif ($request->has('category_id') && !empty($request->category_id)) {
            // Compatibilité avec l'ancien système : si category_id est fourni mais pas categories[]
            $product->categories()->sync([
                $request->category_id => [
                    'is_primary' => true,
                    'order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        // Gestion des sous-catégories multiples (many-to-many)
        if ($request->has('subcategories') && is_array($request->subcategories)) {
            // Si subcategories[] est présent (même vide), on synchronise avec ce qui est fourni
            $subcategoriesData = [];
            foreach ($request->subcategories as $index => $subcategoryId) {
                if (!empty($subcategoryId)) { // Ignorer les valeurs vides
                    $subcategoriesData[$subcategoryId] = [
                        'is_primary' => $index === 0, // Première sous-catégorie = principale
                        'order' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            $product->subcategories()->sync($subcategoriesData);
        } elseif ($request->has('subcategory_id') && !empty($request->subcategory_id)) {
            // Compatibilité avec l'ancien système : si subcategory_id est fourni mais pas subcategories[]
            $product->subcategories()->sync([
                $request->subcategory_id => [
                    'is_primary' => true,
                    'order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produit ajouté avec succès.');
    }
}

