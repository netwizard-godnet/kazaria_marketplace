<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Subcategory::with('category');
        
        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        // Recherche par nom
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $subcategories = $query->orderBy('name')->paginate(15)->appends($request->except('page'));
        $categories = Category::active()->ordered()->get();
        
        return view('admin.subcategories.index', compact('subcategories', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::active()->ordered()->get();
        return view('admin.subcategories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log pour déboguer
        \Log::info('Store subcategory - Request data', [
            'has_file' => $request->hasFile('image'),
            'file_valid' => $request->hasFile('image') ? $request->file('image')->isValid() : false,
            'file_size' => $request->hasFile('image') ? $request->file('image')->getSize() : null,
            'file_mime' => $request->hasFile('image') ? $request->file('image')->getMimeType() : null,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,bmp,tiff,ico,avif,heic|max:5120',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['name', 'category_id', 'description', 'order']);
        $data['is_active'] = $request->has('is_active');
        $data['slug'] = Str::slug($request->name);

        $subcategory = Subcategory::create($data);

        if ($request->hasFile('image')) {
            try {
                $file = $request->file('image');
                
                // Vérifier que le fichier est valide
                if (!$file->isValid()) {
                    \Log::error('Fichier image invalide', [
                        'subcategory_id' => $subcategory->id,
                        'error' => $file->getError(),
                        'error_message' => $file->getErrorMessage()
                    ]);
                } else {
                    // S'assurer que le dossier existe
                    $subcategoriesDir = storage_path('app/public/subcategories');
                    if (!file_exists($subcategoriesDir)) {
                        File::makeDirectory($subcategoriesDir, 0755, true);
                        \Log::info('Dossier subcategories créé', ['path' => $subcategoriesDir]);
                    }
                    
                    $imagePath = $file->store('subcategories', 'public');
                    
                    if ($imagePath) {
                        $subcategory->update(['image' => $imagePath]);
                        
                        // Vérifier que le fichier existe après enregistrement
                        $fileExists = Storage::disk('public')->exists($imagePath);
                        
                        \Log::info('Image sous-catégorie enregistrée', [
                            'subcategory_id' => $subcategory->id,
                            'image_path' => $imagePath,
                            'file_exists' => $fileExists,
                            'full_path' => Storage::disk('public')->path($imagePath)
                        ]);
                        
                        if (!$fileExists) {
                            \Log::error('Le fichier n\'existe pas après enregistrement', [
                                'subcategory_id' => $subcategory->id,
                                'image_path' => $imagePath
                            ]);
                        }
                    } else {
                        \Log::error('Échec de l\'enregistrement - store() retourne null', [
                            'subcategory_id' => $subcategory->id
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'enregistrement de l\'image', [
                    'subcategory_id' => $subcategory->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            \Log::info('Aucun fichier image dans la requête', [
                'subcategory_id' => $subcategory->id,
                'all_files' => $request->allFiles()
            ]);
        }

        // Vérifier si l'image a été enregistrée
        $subcategory->refresh();
        if ($request->hasFile('image') && !$subcategory->image) {
            return redirect()->back()
                ->withInput()
                ->with('warning', 'La sous-catégorie a été créée mais l\'image n\'a pas pu être enregistrée. Veuillez vérifier les logs et réessayer.');
        }

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Sous-catégorie créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subcategory $subcategory)
    {
        $subcategory->load('category');
        return view('admin.subcategories.show', compact('subcategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subcategory $subcategory)
    {
        $categories = Category::active()->ordered()->get();
        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,bmp,tiff,ico,avif,heic|max:5120',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $data = $request->only(['name', 'category_id', 'description', 'order']);
        $data['is_active'] = $request->has('is_active');
        $data['slug'] = Str::slug($request->name);

        $subcategory->update($data);

        if ($request->hasFile('image')) {
            try {
                $file = $request->file('image');
                
                // Vérifier que le fichier est valide
                if (!$file->isValid()) {
                    \Log::error('Fichier image invalide lors de la mise à jour', [
                        'subcategory_id' => $subcategory->id,
                        'error' => $file->getError(),
                        'error_message' => $file->getErrorMessage()
                    ]);
                } else {
                    // S'assurer que le dossier existe
                    $subcategoriesDir = storage_path('app/public/subcategories');
                    if (!file_exists($subcategoriesDir)) {
                        File::makeDirectory($subcategoriesDir, 0755, true);
                        \Log::info('Dossier subcategories créé', ['path' => $subcategoriesDir]);
                    }
                    
                    // Supprimer l'ancienne image si elle existe
                    if ($subcategory->image && Storage::disk('public')->exists($subcategory->image)) {
                        Storage::disk('public')->delete($subcategory->image);
                    }
                    
                    $imagePath = $file->store('subcategories', 'public');
                    
                    if ($imagePath) {
                        $subcategory->update(['image' => $imagePath]);
                        
                        // Vérifier que le fichier existe après enregistrement
                        $fileExists = Storage::disk('public')->exists($imagePath);
                        
                        \Log::info('Image sous-catégorie mise à jour', [
                            'subcategory_id' => $subcategory->id,
                            'image_path' => $imagePath,
                            'file_exists' => $fileExists,
                            'full_path' => Storage::disk('public')->path($imagePath)
                        ]);
                        
                        if (!$fileExists) {
                            \Log::error('Le fichier n\'existe pas après mise à jour', [
                                'subcategory_id' => $subcategory->id,
                                'image_path' => $imagePath
                            ]);
                        }
                    } else {
                        \Log::error('Échec de la mise à jour - store() retourne null', [
                            'subcategory_id' => $subcategory->id
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la mise à jour de l\'image', [
                    'subcategory_id' => $subcategory->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Vérifier si l'image a été enregistrée
        $subcategory->refresh();
        if ($request->hasFile('image') && !$subcategory->image) {
            return redirect()->back()
                ->withInput()
                ->with('warning', 'La sous-catégorie a été mise à jour mais l\'image n\'a pas pu être enregistrée. Veuillez vérifier les logs et réessayer.');
        }

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Sous-catégorie mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subcategory $subcategory)
    {
        // Vérifier s'il y a des produits associés
        if ($subcategory->products()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer cette sous-catégorie car elle contient des produits.');
        }

        if ($subcategory->image) {
            Storage::disk('public')->delete($subcategory->image);
        }

        $subcategory->delete();

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Sous-catégorie supprimée avec succès.');
    }

    /**
     * Toggle the active status of the subcategory.
     */
    public function toggleStatus(Subcategory $subcategory)
    {
        $subcategory->update(['is_active' => !$subcategory->is_active]);
        
        $status = $subcategory->is_active ? 'activée' : 'désactivée';
        return redirect()->back()->with('success', "Sous-catégorie {$status} avec succès.");
    }
}