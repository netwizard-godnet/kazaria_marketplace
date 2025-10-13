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
        $user = $request->user();
        $store = $user->store;

        $product = Product::where('id', $id)
            ->where('store_id', $store->id)
            ->firstOrFail();

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

            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $finalPrice,
                'old_price' => $oldPrice,
                'discount_percentage' => $discount,
                'stock' => $request->stock,
                'brand' => $request->brand,
                'model' => $request->model,
                'warranty' => $request->warranty,
            ]);

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
}
