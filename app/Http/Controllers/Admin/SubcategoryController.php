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

        $imageError = null;
        
        if ($request->hasFile('image')) {
            try {
                $file = $request->file('image');
                
                // Vérifier que le fichier est valide
                if (!$file->isValid()) {
                    $errorCode = $file->getError();
                    $errorMessage = $file->getErrorMessage();
                    
                    \Log::error('Fichier image invalide', [
                        'subcategory_id' => $subcategory->id,
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage
                    ]);
                    
                    // Messages d'erreur selon le code d'erreur PHP
                    $userFriendlyMessages = [
                        1 => 'Le fichier dépasse la taille maximale autorisée par le serveur.',
                        2 => 'Le fichier dépasse la taille maximale autorisée (5MB).',
                        3 => 'Le fichier n\'a été que partiellement téléchargé.',
                        4 => 'Aucun fichier n\'a été téléchargé.',
                        6 => 'Dossier temporaire manquant.',
                        7 => 'Échec de l\'écriture du fichier sur le disque.',
                        8 => 'Une extension PHP a arrêté le téléchargement du fichier.',
                    ];
                    
                    $imageError = $userFriendlyMessages[$errorCode] ?? 'Erreur lors du téléchargement du fichier : ' . $errorMessage;
                } else {
                    // S'assurer que le dossier existe
                    $subcategoriesDir = storage_path('app/public/subcategories');
                    try {
                        if (!file_exists($subcategoriesDir)) {
                            if (!File::makeDirectory($subcategoriesDir, 0755, true)) {
                                throw new \Exception('Impossible de créer le dossier subcategories');
                            }
                            \Log::info('Dossier subcategories créé', ['path' => $subcategoriesDir]);
                        }
                        
                        // Vérifier les permissions d'écriture
                        if (!is_writable($subcategoriesDir)) {
                            throw new \Exception('Le dossier subcategories n\'est pas accessible en écriture');
                        }
                    } catch (\Exception $dirException) {
                        \Log::error('Erreur création dossier subcategories', [
                            'subcategory_id' => $subcategory->id,
                            'error' => $dirException->getMessage(),
                            'path' => $subcategoriesDir
                        ]);
                        $imageError = 'Erreur lors de la création du dossier de stockage. Veuillez contacter l\'administrateur.';
                    }
                    
                    if (!$imageError) {
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
                                $imageError = 'L\'image a été enregistrée mais le fichier n\'a pas été trouvé sur le serveur.';
                            }
                        } else {
                            \Log::error('Échec de l\'enregistrement - store() retourne null', [
                                'subcategory_id' => $subcategory->id,
                                'file_name' => $file->getClientOriginalName(),
                                'file_size' => $file->getSize()
                            ]);
                            $imageError = 'Erreur lors de l\'enregistrement du fichier. Veuillez réessayer.';
                        }
                    }
                }
            } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
                \Log::error('Fichier trop volumineux', [
                    'subcategory_id' => $subcategory->id,
                    'error' => $e->getMessage()
                ]);
                $imageError = 'Le fichier est trop volumineux. Taille maximale : 5MB.';
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'enregistrement de l\'image', [
                    'subcategory_id' => $subcategory->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $imageError = 'Erreur lors de l\'enregistrement de l\'image : ' . $e->getMessage();
            }
        }

        // Vérifier si l'image a été enregistrée
        $subcategory->refresh();
        if ($request->hasFile('image') && !$subcategory->image) {
            return redirect()->back()
                ->withInput()
                ->with('error', $imageError ?? 'La sous-catégorie a été créée mais l\'image n\'a pas pu être enregistrée. Veuillez réessayer.');
        }
        
        // Afficher un avertissement si l'image a un problème mais la sous-catégorie a été créée
        if ($imageError && $subcategory->image) {
            return redirect()->route('admin.subcategories.index')
                ->with('success', 'Sous-catégorie créée avec succès.')
                ->with('warning', 'Attention : ' . $imageError);
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

        $imageError = null;
        
        if ($request->hasFile('image')) {
            try {
                $file = $request->file('image');
                
                // Vérifier que le fichier est valide
                if (!$file->isValid()) {
                    $errorCode = $file->getError();
                    $errorMessage = $file->getErrorMessage();
                    
                    \Log::error('Fichier image invalide lors de la mise à jour', [
                        'subcategory_id' => $subcategory->id,
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage
                    ]);
                    
                    // Messages d'erreur selon le code d'erreur PHP
                    $userFriendlyMessages = [
                        1 => 'Le fichier dépasse la taille maximale autorisée par le serveur.',
                        2 => 'Le fichier dépasse la taille maximale autorisée (5MB).',
                        3 => 'Le fichier n\'a été que partiellement téléchargé.',
                        4 => 'Aucun fichier n\'a été téléchargé.',
                        6 => 'Dossier temporaire manquant.',
                        7 => 'Échec de l\'écriture du fichier sur le disque.',
                        8 => 'Une extension PHP a arrêté le téléchargement du fichier.',
                    ];
                    
                    $imageError = $userFriendlyMessages[$errorCode] ?? 'Erreur lors du téléchargement du fichier : ' . $errorMessage;
                } else {
                    // S'assurer que le dossier existe
                    $subcategoriesDir = storage_path('app/public/subcategories');
                    try {
                        if (!file_exists($subcategoriesDir)) {
                            if (!File::makeDirectory($subcategoriesDir, 0755, true)) {
                                throw new \Exception('Impossible de créer le dossier subcategories');
                            }
                            \Log::info('Dossier subcategories créé', ['path' => $subcategoriesDir]);
                        }
                        
                        // Vérifier les permissions d'écriture
                        if (!is_writable($subcategoriesDir)) {
                            throw new \Exception('Le dossier subcategories n\'est pas accessible en écriture');
                        }
                    } catch (\Exception $dirException) {
                        \Log::error('Erreur création dossier subcategories', [
                            'subcategory_id' => $subcategory->id,
                            'error' => $dirException->getMessage(),
                            'path' => $subcategoriesDir
                        ]);
                        $imageError = 'Erreur lors de la création du dossier de stockage. Veuillez contacter l\'administrateur.';
                    }
                    
                    if (!$imageError) {
                        // Supprimer l'ancienne image si elle existe
                        try {
                            if ($subcategory->image && Storage::disk('public')->exists($subcategory->image)) {
                                Storage::disk('public')->delete($subcategory->image);
                            }
                        } catch (\Exception $deleteException) {
                            \Log::warning('Impossible de supprimer l\'ancienne image', [
                                'subcategory_id' => $subcategory->id,
                                'old_image' => $subcategory->image,
                                'error' => $deleteException->getMessage()
                            ]);
                            // Ne pas bloquer l'upload si la suppression échoue
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
                                $imageError = 'L\'image a été enregistrée mais le fichier n\'a pas été trouvé sur le serveur.';
                            }
                        } else {
                            \Log::error('Échec de la mise à jour - store() retourne null', [
                                'subcategory_id' => $subcategory->id,
                                'file_name' => $file->getClientOriginalName(),
                                'file_size' => $file->getSize()
                            ]);
                            $imageError = 'Erreur lors de l\'enregistrement du fichier. Veuillez réessayer.';
                        }
                    }
                }
            } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
                \Log::error('Fichier trop volumineux lors de la mise à jour', [
                    'subcategory_id' => $subcategory->id,
                    'error' => $e->getMessage()
                ]);
                $imageError = 'Le fichier est trop volumineux. Taille maximale : 5MB.';
            } catch (\Exception $e) {
                \Log::error('Erreur lors de la mise à jour de l\'image', [
                    'subcategory_id' => $subcategory->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $imageError = 'Erreur lors de l\'enregistrement de l\'image : ' . $e->getMessage();
            }
        }

        // Vérifier si l'image a été enregistrée
        $subcategory->refresh();
        if ($request->hasFile('image') && !$subcategory->image) {
            return redirect()->back()
                ->withInput()
                ->with('error', $imageError ?? 'La sous-catégorie a été mise à jour mais l\'image n\'a pas pu être enregistrée. Veuillez réessayer.');
        }
        
        // Afficher un avertissement si l'image a un problème mais la sous-catégorie a été mise à jour
        if ($imageError && $subcategory->image) {
            return redirect()->route('admin.subcategories.index')
                ->with('success', 'Sous-catégorie mise à jour avec succès.')
                ->with('warning', 'Attention : ' . $imageError);
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