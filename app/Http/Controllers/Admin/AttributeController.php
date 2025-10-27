<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $attributes = Attribute::with('attributeValues')->ordered()->get();
        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.attributes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:select,checkbox,radio',
            'is_filterable' => 'boolean',
            'order' => 'integer|min:0',
            'values' => 'array',
            'values.*' => 'string|max:255'
        ]);

        $attribute = Attribute::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'is_filterable' => $request->has('is_filterable'),
            'order' => $request->order ?? 0
        ]);

        // Créer les valeurs d'attribut si fournies
        if ($request->has('values') && is_array($request->values)) {
            foreach ($request->values as $index => $value) {
                if (!empty(trim($value))) {
                    AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => trim($value),
                        'slug' => Str::slug(trim($value)),
                        'order' => $index
                    ]);
                }
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribut créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Attribute $attribute)
    {
        $attribute->load('attributeValues');
        return view('admin.attributes.show', compact('attribute'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attribute $attribute)
    {
        $attribute->load('attributeValues');
        return view('admin.attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:select,checkbox,radio',
            'is_filterable' => 'boolean',
            'order' => 'integer|min:0',
            'values' => 'array',
            'values.*' => 'string|max:255'
        ]);

        $attribute->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'is_filterable' => $request->has('is_filterable'),
            'order' => $request->order ?? 0
        ]);

        // Supprimer les anciennes valeurs
        $attribute->attributeValues()->delete();

        // Créer les nouvelles valeurs d'attribut si fournies
        if ($request->has('values') && is_array($request->values)) {
            foreach ($request->values as $index => $value) {
                if (!empty(trim($value))) {
                    AttributeValue::create([
                        'attribute_id' => $attribute->id,
                        'value' => trim($value),
                        'slug' => Str::slug(trim($value)),
                        'order' => $index
                    ]);
                }
            }
        }

        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribut mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return redirect()->route('admin.attributes.index')
            ->with('success', 'Attribut supprimé avec succès.');
    }
}
