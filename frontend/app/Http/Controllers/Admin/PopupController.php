<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PopupRequest;
use App\Models\Popup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PopupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $popups = Popup::orderByDesc('is_active')
            ->orderByDesc('priority')
            ->latest('updated_at')
            ->paginate(15);

        return view('admin.popups.index', array_merge([
            'popups' => $popups,
        ], $this->formOptions()));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $popup = new Popup([
            'frequency' => 'once_per_session',
            'delay_seconds' => 0,
            'priority' => 0,
            'is_active' => false,
            'layout' => 'left-right',
        ]);

        return view('admin.popups.create', $this->formOptions(compact('popup')));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PopupRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = $this->generateSlug($data['slug'] ?? null, $data['title']);
        $data['display_pages'] = $this->resolvePages(
            $data['display_pages'] ?? [],
            $data['display_pages_custom'] ?? null
        );
        $data['display_devices'] = $data['display_devices'] ?? [];
        $data['is_active'] = $request->boolean('is_active');
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('popups', 'public');
        } elseif (!empty($data['image_path'] ?? null)) {
            $data['image'] = $data['image_path'];
        }

        unset($data['image_path'], $data['display_pages_custom']);

        Popup::create($data);

        return redirect()
            ->route('admin.popups.index')
            ->with('success', 'Popup créée avec succès.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Popup $popup)
    {
        return view('admin.popups.edit', $this->formOptions(compact('popup')));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PopupRequest $request, Popup $popup)
    {
        $data = $request->validated();
        $data['slug'] = $this->generateSlug($data['slug'] ?? null, $data['title'], $popup->id);
        $data['display_pages'] = $this->resolvePages(
            $data['display_pages'] ?? [],
            $data['display_pages_custom'] ?? null
        );
        $data['display_devices'] = $data['display_devices'] ?? [];
        $data['is_active'] = $request->boolean('is_active');
        $data['updated_by'] = auth()->id();

        if ($request->hasFile('image')) {
            if ($popup->image && Storage::disk('public')->exists($popup->image)) {
                Storage::disk('public')->delete($popup->image);
            }

            $data['image'] = $request->file('image')->store('popups', 'public');
        } elseif (!empty($data['image_path'] ?? null)) {
            $data['image'] = $data['image_path'];
        } elseif ($request->boolean('remove_image')) {
            if ($popup->image && Storage::disk('public')->exists($popup->image)) {
                Storage::disk('public')->delete($popup->image);
            }
            $data['image'] = null;
        } else {
            $data['image'] = $popup->image;
        }

        unset($data['image_path'], $data['display_pages_custom']);

        $popup->update($data);

        return redirect()
            ->route('admin.popups.index')
            ->with('success', 'Popup mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Popup $popup)
    {
        if ($popup->image && Storage::disk('public')->exists($popup->image)) {
            Storage::disk('public')->delete($popup->image);
        }

        $popup->delete();

        return redirect()
            ->route('admin.popups.index')
            ->with('success', 'Popup supprimée avec succès.');
    }

    public function toggle(Popup $popup)
    {
        $popup->update([
            'is_active' => !$popup->is_active,
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.popups.index')
            ->with('success', 'Statut mis à jour.');
    }

    protected function formOptions(array $data = []): array
    {
        $frequencies = [
            'once_per_session' => 'Une fois par session',
            'once_per_day' => 'Une fois par jour',
            'once_per_visit' => 'À chaque visite',
            'always' => 'Toujours',
        ];

        $devices = [
            'desktop' => 'Ordinateur',
            'tablet' => 'Tablette',
            'mobile' => 'Mobile',
        ];

        $pagePresets = [
            'home' => 'Page d\'accueil',
            'category' => 'Pages catégories',
            'product' => 'Pages produits',
            'cart' => 'Panier',
            'checkout' => 'Checkout',
            'custom' => 'URL personnalisée',
        ];

        $layouts = [
            'stacked' => 'Superposé (Image au-dessus, contenu en dessous)',
            'left-right' => 'Image à gauche, contenu à droite',
            'right-left' => 'Image à droite, contenu à gauche',
            'top-bottom' => 'Image en haut, contenu en bas',
        ];

        return array_merge($data, [
            'frequencies' => $frequencies,
            'devices' => $devices,
            'pagePresets' => $pagePresets,
            'layouts' => $layouts,
        ]);
    }

    protected function generateSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $title);
        $candidate = $base;
        $counter = 1;

        while (
            Popup::where('slug', $candidate)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $base . '-' . $counter++;
        }

        return $candidate;
    }

    protected function resolvePages(array $pages, ?string $custom = null): array
    {
        $extra = collect(explode(',', (string) $custom))
            ->map(fn ($value) => trim($value))
            ->filter();

        return collect($pages)
            ->merge($extra)
            ->reject(fn ($value) => $value === 'custom' || $value === null || $value === '')
            ->unique()
            ->values()
            ->all();
    }
}
