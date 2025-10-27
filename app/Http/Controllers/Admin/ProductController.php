<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;

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

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('admin.products.index', compact('products'));
    }


    public function show(Product $product)
    {
        $product->load(['store', 'category', 'subcategory']);
        return view('admin.products.show', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:pending,approved,rejected',
            'is_active' => 'boolean',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'attributes' => 'nullable',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        // Gestion des images
        $currentImages = $product->images ?? [];

        // Supprimer les images marquées pour suppression
        if ($request->has('images_to_remove')) {
            $imagesToRemove = $request->images_to_remove;
            foreach ($imagesToRemove as $index) {
                if (isset($currentImages[$index])) {
                    // Supprimer le fichier du storage
                    $imagePath = storage_path('app/public/' . $currentImages[$index]);
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                    // Retirer du tableau
                    unset($currentImages[$index]);
                }
            }
            // Réindexer le tableau
            $currentImages = array_values($currentImages);
        }

        // Ajouter les nouvelles images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $currentImages[] = $path;
            }
        }

        $data['images'] = $currentImages;

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

    public function edit(Product $product)
    {
        $product->load(['store', 'category', 'subcategory', 'attributeValues.attribute']);
        $categories = Category::all();
        $attributes = Attribute::with('attributeValues')->ordered()->get();
        return view('admin.products.edit', compact('product', 'categories', 'attributes'));
    }

    public function deleteImage(Product $product, $index)
    {
        $images = $product->images ?? [];
        
        if (isset($images[$index])) {
            // Supprimer le fichier du storage
            $imagePath = storage_path('app/public/' . $images[$index]);
            if (file_exists($imagePath)) {
                unlink($imagePath);
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
}

