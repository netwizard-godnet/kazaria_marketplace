<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::ordered()->get();
        return view('admin.brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'image' => 'required|image',
            'link_url' => 'nullable|url|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'brand_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $imagePath = 'images/' . $imageName;
        }

        Brand::create([
            'name' => $validated['name'] ?? null,
            'image_path' => $imagePath,
            'link_url' => $validated['link_url'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true,
        ]);

        return redirect()->back()->with('success', 'Marque créée avec succès.');
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'image' => 'nullable|image',
            'link_url' => 'nullable|url|max:2048',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($brand->image_path && File::exists(public_path($brand->image_path))) {
                File::delete(public_path($brand->image_path));
            }
            
            $image = $request->file('image');
            $imageName = 'brand_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            $brand->image_path = 'images/' . $imageName;
        }

        $brand->name = $validated['name'] ?? null;
        $brand->link_url = $validated['link_url'] ?? null;
        $brand->sort_order = $validated['sort_order'] ?? 0;
        $brand->is_active = array_key_exists('is_active', $validated) ? (bool) $validated['is_active'] : true;
        $brand->save();

        return redirect()->back()->with('success', 'Marque mise à jour avec succès.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->image_path && File::exists(public_path($brand->image_path))) {
            File::delete(public_path($brand->image_path));
        }
        $brand->delete();
        return redirect()->back()->with('success', 'Marque supprimée avec succès.');
    }

    public function toggleStatus(Brand $brand)
    {
        $brand->update(['is_active' => !$brand->is_active]);
        $status = $brand->is_active ? 'activée' : 'désactivée';
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Marque {$status} avec succès.",
                'brand' => $brand->fresh()
            ]);
        }
        
        return redirect()->back()->with('success', "Marque {$status} avec succès.");
    }
}
