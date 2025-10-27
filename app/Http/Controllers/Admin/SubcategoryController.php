<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        
        $subcategories = $query->orderBy('name')->paginate(15)->appends($request->query());
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
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,bmp,tiff,ico,avif,heic|max:5120',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['slug'] = Str::slug($request->name);

        $subcategory = Subcategory::create($data);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('subcategories', 'public');
            $subcategory->update(['image' => $imagePath]);
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

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['slug'] = Str::slug($request->name);

        $subcategory->update($data);

        if ($request->hasFile('image')) {
            if ($subcategory->image) {
                Storage::disk('public')->delete($subcategory->image);
            }
            $imagePath = $request->file('image')->store('subcategories', 'public');
            $subcategory->update(['image' => $imagePath]);
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