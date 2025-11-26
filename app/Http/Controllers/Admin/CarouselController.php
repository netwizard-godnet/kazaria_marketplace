<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarouselSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarouselController extends Controller
{
    public function index()
    {
        $slides = CarouselSlide::orderBy('sort_order')->paginate(15);
        return view('admin.carousel.index', compact('slides'));
    }

    public function show(CarouselSlide $slide)
    {
        // Si c'est une requête AJAX, retourner JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'slide' => $slide
            ]);
        }
        
        return view('admin.carousel.show', compact('slide'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'required|file',
            'link_url' => 'nullable|url|max:2048',
            'button_text' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $path = $request->file('image')->store('carousel', 'public');

        CarouselSlide::create([
            'title' => $request->title,
            'description' => $request->description,
            'image_path' => $path,
            'link_url' => $request->link_url,
            'button_text' => $request->button_text,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => (bool) $request->is_active,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ]);

        return redirect()->back()->with('success', 'Slide créé avec succès.');
    }

    public function update(Request $request, CarouselSlide $slide)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|file',
            'link_url' => 'nullable|url|max:2048',
            'button_text' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($request->hasFile('image')) {
            if ($slide->image_path) {
                Storage::disk('public')->delete($slide->image_path);
            }
            $slide->image_path = $request->file('image')->store('carousel', 'public');
        }

        $slide->fill([
            'title' => $request->title,
            'description' => $request->description,
            'link_url' => $request->link_url,
            'button_text' => $request->button_text,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => (bool) $request->is_active,
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ])->save();

        return redirect()->back()->with('success', 'Slide mis à jour avec succès.');
    }

    public function destroy(CarouselSlide $slide)
    {
        if ($slide->image_path) {
            Storage::disk('public')->delete($slide->image_path);
        }
        $slide->delete();
        return redirect()->back()->with('success', 'Slide supprimé avec succès.');
    }
}
