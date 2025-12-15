<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::with(['subcategories', 'products']);

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Tri
        $sortBy = $request->get('sort_by', 'order');
        $sortDirection = $request->get('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $categories = $query->paginate(12);

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,bmp,tiff,ico,avif,heic|max:5120',
            'is_active' => 'boolean',
            'is_customized' => 'boolean',
            'custom_layout' => 'nullable|json',
            'custom_banners' => 'nullable|json',
            'custom_carousels' => 'nullable|json',
            'custom_colors' => 'nullable|json',
            'order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['is_customized'] = $request->has('is_customized');
        $data['slug'] = Str::slug($request->name);
        
        // Traiter le custom_layout
        if ($request->has('is_customized') && $request->filled('section_titles')) {
            // Reconstruire le layout à partir des données du formulaire
            $customLayout = [];
            $sectionTitles = $request->input('section_titles', []);
            $sectionOrders = $request->input('section_orders', []);
            $sectionEnabled = $request->input('section_enabled', []);
            
            foreach ($sectionTitles as $key => $title) {
                $customLayout[$key] = [
                    'enabled' => isset($sectionEnabled[$key]),
                    'order' => isset($sectionOrders[$key]) ? (int)$sectionOrders[$key] : 999,
                    'title' => $title ?: null,
                ];
            }
            
            $data['custom_layout'] = $customLayout;
        } elseif ($request->filled('custom_layout')) {
            // Si c'est déjà du JSON (fallback)
            $data['custom_layout'] = json_decode($request->custom_layout, true);
        } else {
            // Si la personnalisation est désactivée, réinitialiser le layout
            $data['custom_layout'] = null;
        }
        
        // Traiter les bannières personnalisées
        if ($request->filled('custom_banners')) {
            $data['custom_banners'] = is_string($request->custom_banners) 
                ? json_decode($request->custom_banners, true) 
                : $request->custom_banners;
        } elseif (!$request->has('is_customized')) {
            $data['custom_banners'] = null;
        }
        
        // Traiter les carrousels personnalisés
        if ($request->filled('custom_carousels')) {
            $data['custom_carousels'] = is_string($request->custom_carousels) 
                ? json_decode($request->custom_carousels, true) 
                : $request->custom_carousels;
        } elseif (!$request->has('is_customized')) {
            $data['custom_carousels'] = null;
        }
        
        // Traiter les couleurs personnalisées
        if ($request->filled('custom_colors')) {
            $data['custom_colors'] = is_string($request->custom_colors) 
                ? json_decode($request->custom_colors, true) 
                : $request->custom_colors;
        } elseif (!$request->has('is_customized')) {
            $data['custom_colors'] = null;
        }

        $category = Category::create($data);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $category->update(['image' => $imagePath]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie créée avec succès.');
    }

    public function show(Category $category)
    {
        $category->load(['subcategories' => function($query) {
            $query->orderBy('order')->orderBy('name');
        }, 'products' => function($query) {
            $query->latest()->take(10);
        }]);
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,bmp,tiff,ico,avif,heic|max:5120',
            'is_active' => 'boolean',
            'is_customized' => 'boolean',
            'custom_layout' => 'nullable|json',
            'custom_banners' => 'nullable|json',
            'custom_carousels' => 'nullable|json',
            'custom_colors' => 'nullable|json',
            'order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['is_customized'] = $request->has('is_customized');
        $data['slug'] = Str::slug($request->name);
        
        // Traiter le custom_layout
        if ($request->has('is_customized') && $request->filled('section_titles')) {
            // Reconstruire le layout à partir des données du formulaire
            $customLayout = [];
            $sectionTitles = $request->input('section_titles', []);
            $sectionOrders = $request->input('section_orders', []);
            $sectionEnabled = $request->input('section_enabled', []);
            
            foreach ($sectionTitles as $key => $title) {
                $customLayout[$key] = [
                    'enabled' => isset($sectionEnabled[$key]),
                    'order' => isset($sectionOrders[$key]) ? (int)$sectionOrders[$key] : 999,
                    'title' => $title ?: null,
                ];
            }
            
            $data['custom_layout'] = $customLayout;
        } elseif ($request->filled('custom_layout')) {
            // Si c'est déjà du JSON (fallback)
            $data['custom_layout'] = json_decode($request->custom_layout, true);
        } else {
            // Si la personnalisation est désactivée, réinitialiser le layout
            $data['custom_layout'] = null;
        }
        
        // Traiter les bannières personnalisées
        $oldBanners = $category->custom_banners ?? [];
        if ($request->filled('custom_banners')) {
            $newBanners = is_string($request->custom_banners) 
                ? json_decode($request->custom_banners, true) 
                : $request->custom_banners;
            
            // Supprimer les images des bannières qui ne sont plus utilisées
            $oldBannerImages = collect($oldBanners)->pluck('image')->filter()->toArray();
            $newBannerImages = collect($newBanners)->pluck('image')->filter()->toArray();
            $imagesToDelete = array_diff($oldBannerImages, $newBannerImages);
            
            foreach ($imagesToDelete as $imagePath) {
                if ($imagePath && !str_starts_with($imagePath, 'data:') && Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
            
            $data['custom_banners'] = $newBanners;
        } elseif (!$request->has('is_customized')) {
            // Supprimer toutes les images des bannières si la personnalisation est désactivée
            foreach ($oldBanners as $banner) {
                if (isset($banner['image']) && !str_starts_with($banner['image'], 'data:') && Storage::disk('public')->exists($banner['image'])) {
                    Storage::disk('public')->delete($banner['image']);
                }
            }
            $data['custom_banners'] = null;
        }
        
        // Traiter les carrousels personnalisés
        $oldCarousels = $category->custom_carousels ?? [];
        if ($request->filled('custom_carousels')) {
            $newCarousels = is_string($request->custom_carousels) 
                ? json_decode($request->custom_carousels, true) 
                : $request->custom_carousels;
            
            // Supprimer les images des carrousels qui ne sont plus utilisées
            $oldCarouselImages = collect($oldCarousels)->pluck('images')->flatten()->pluck('url')->filter()->toArray();
            $newCarouselImages = collect($newCarousels)->pluck('images')->flatten()->pluck('url')->filter()->toArray();
            $imagesToDelete = array_diff($oldCarouselImages, $newCarouselImages);
            
            foreach ($imagesToDelete as $imagePath) {
                if ($imagePath && !str_starts_with($imagePath, 'data:') && Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
            
            $data['custom_carousels'] = $newCarousels;
        } elseif (!$request->has('is_customized')) {
            // Supprimer toutes les images des carrousels si la personnalisation est désactivée
            foreach ($oldCarousels as $carousel) {
                if (isset($carousel['images']) && is_array($carousel['images'])) {
                    foreach ($carousel['images'] as $image) {
                        if (isset($image['url']) && !str_starts_with($image['url'], 'data:') && Storage::disk('public')->exists($image['url'])) {
                            Storage::disk('public')->delete($image['url']);
                        }
                    }
                }
            }
            $data['custom_carousels'] = null;
        }
        
        // Traiter les couleurs personnalisées
        if ($request->filled('custom_colors')) {
            $data['custom_colors'] = is_string($request->custom_colors) 
                ? json_decode($request->custom_colors, true) 
                : $request->custom_colors;
        } elseif (!$request->has('is_customized')) {
            $data['custom_colors'] = null;
        }

        $category->update($data);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $imagePath = $request->file('image')->store('categories', 'public');
            $category->update(['image' => $imagePath]);
        }

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'Impossible de supprimer une catégorie qui contient des produits.');
        }

        if ($category->subcategories()->count() > 0) {
            return redirect()->back()->with('error', 'Impossible de supprimer une catégorie qui contient des sous-catégories.');
        }

        // Supprimer l'image principale
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        // Supprimer les images des bannières personnalisées
        if ($category->custom_banners) {
            foreach ($category->custom_banners as $banner) {
                if (isset($banner['image']) && !str_starts_with($banner['image'], 'data:') && Storage::disk('public')->exists($banner['image'])) {
                    Storage::disk('public')->delete($banner['image']);
                }
            }
        }

        // Supprimer les images des carrousels personnalisés
        if ($category->custom_carousels) {
            foreach ($category->custom_carousels as $carousel) {
                if (isset($carousel['images']) && is_array($carousel['images'])) {
                    foreach ($carousel['images'] as $image) {
                        if (isset($image['url']) && !str_starts_with($image['url'], 'data:') && Storage::disk('public')->exists($image['url'])) {
                            Storage::disk('public')->delete($image['url']);
                        }
                    }
                }
            }
        }

        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Catégorie supprimée avec succès.');
    }

    public function toggleStatus(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);
        $status = $category->is_active ? 'activée' : 'désactivée';
        return redirect()->back()->with('success', "Catégorie {$status} avec succès.");
    }

    public function getSubcategories($categoryId)
    {
        $subcategories = Subcategory::where('category_id', $categoryId)->get();
        
        return response()->json([
            'success' => true,
            'subcategories' => $subcategories->map(function($subcategory) {
                return [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                ];
            })
        ]);
    }

    /**
     * Upload une image pour une bannière ou un carrousel personnalisé
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,jpg,png,gif,webp,svg,bmp,tiff,ico,avif,heic|max:5120',
            'type' => 'required|in:banner,carousel',
        ]);

        try {
            $file = $request->file('image');
            $type = $request->input('type');
            
            // Déterminer le dossier de stockage selon le type
            $folder = $type === 'banner' ? 'categories/banners' : 'categories/carousels';
            
            // Stocker le fichier
            $path = $file->store($folder, 'public');
            
            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => asset('storage/' . $path),
                'message' => 'Image uploadée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une image uploadée
     */
    public function deleteImage(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        try {
            $path = $request->input('path');
            
            // Vérifier que le chemin est dans le storage public
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Image supprimée avec succès'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Image non trouvée'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }
}

