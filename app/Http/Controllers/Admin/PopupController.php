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
     * Afficher la liste des popups
     */
    public function index()
    {
        $popups = Popup::orderByDesc('is_active')
            ->orderByDesc('priority')
            ->latest('updated_at')
            ->paginate(20);

        return view('admin.popups.index', [
            'popups' => $popups,
            'frequencies' => $this->getFrequencies(),
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $popup = new Popup([
            'frequency' => 'once_per_session',
            'delay_seconds' => 0,
            'priority' => 0,
            'is_active' => false,
            'layout' => 'stacked',
            'width' => 300,
            'height' => 300,
        ]);

        return view('admin.popups.form', [
            'popup' => $popup,
            'frequencies' => $this->getFrequencies(),
            'devices' => $this->getDevices(),
            'layouts' => $this->getLayouts(),
            'pagePresets' => $this->getPagePresets(),
        ]);
    }

    /**
     * Enregistrer une nouvelle popup
     */
    public function store(PopupRequest $request)
    {
        $data = $this->prepareData($request);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('popups', 'public');
        }

        Popup::create($data);

        return redirect()
            ->route('admin.popups.index')
            ->with('success', 'Popup créée avec succès.');
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Popup $popup)
    {
        return view('admin.popups.form', [
            'popup' => $popup,
            'frequencies' => $this->getFrequencies(),
            'devices' => $this->getDevices(),
            'layouts' => $this->getLayouts(),
            'pagePresets' => $this->getPagePresets(),
        ]);
    }

    /**
     * Mettre à jour une popup
     */
    public function update(PopupRequest $request, Popup $popup)
    {
        $data = $this->prepareData($request);
        $data['updated_by'] = auth()->id();

        // Gestion de l'image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($popup->image && Storage::disk('public')->exists($popup->image)) {
                Storage::disk('public')->delete($popup->image);
            }
            $data['image'] = $request->file('image')->store('popups', 'public');
        } elseif ($request->boolean('remove_image')) {
            if ($popup->image && Storage::disk('public')->exists($popup->image)) {
                Storage::disk('public')->delete($popup->image);
            }
            $data['image'] = null;
        }

        $popup->update($data);

        return redirect()
            ->route('admin.popups.index')
            ->with('success', 'Popup mise à jour avec succès.');
    }

    /**
     * Supprimer une popup
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

    /**
     * Activer/Désactiver une popup
     */
    public function toggle(Popup $popup)
    {
        $popup->update([
            'is_active' => !$popup->is_active,
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.popups.index')
            ->with('success', 'Statut de la popup mis à jour.');
    }

    /**
     * Préparer les données pour la sauvegarde
     */
    protected function prepareData(Request $request): array
    {
        $data = $request->validated();

        // Slug
        $data['slug'] = $this->generateSlug(
            $data['slug'] ?? null,
            $data['title'] ?? null,
            $request->route('popup')?->id ?? null
        );

        // Valeurs par défaut
        $data['frequency'] = $data['frequency'] ?? 'once_per_session';
        $data['delay_seconds'] = (int) ($data['delay_seconds'] ?? 0);
        $data['priority'] = (int) ($data['priority'] ?? 0);
        $data['width'] = (int) ($data['width'] ?? 300);
        $data['height'] = (int) ($data['height'] ?? 300);
        $data['layout'] = $data['layout'] ?? 'stacked';
        $data['is_active'] = $request->boolean('is_active', false);

        // Pages d'affichage
        $data['display_pages'] = $this->resolvePages(
            $data['display_pages'] ?? [],
            $data['display_pages_custom'] ?? null
        );

        // Appareils
        $data['display_devices'] = $data['display_devices'] ?? [];

        // Dates
        $data['display_start'] = $data['display_start'] ?? null;
        $data['display_end'] = $data['display_end'] ?? null;

        // Nettoyer
        unset($data['display_pages_custom']);

        return $data;
    }

    /**
     * Générer un slug unique
     */
    protected function generateSlug(?string $slug, ?string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: ($title ?? 'popup-' . time()));
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

    /**
     * Résoudre les pages d'affichage
     */
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

    /**
     * Options de fréquence
     */
    protected function getFrequencies(): array
    {
        return [
            'once_per_session' => 'Une fois par session',
            'once_per_day' => 'Une fois par jour',
            'once_per_visit' => 'À chaque visite',
            'always' => 'Toujours afficher',
        ];
    }

    /**
     * Options d'appareils
     */
    protected function getDevices(): array
    {
        return [
            'desktop' => 'Ordinateur',
            'tablet' => 'Tablette',
            'mobile' => 'Mobile',
        ];
    }

    /**
     * Options de layout
     */
    protected function getLayouts(): array
    {
        return [
            'stacked' => 'Superposé (Image en arrière-plan)',
            'left-right' => 'Image à gauche, contenu à droite',
            'right-left' => 'Image à droite, contenu à gauche',
            'top-bottom' => 'Image en haut, contenu en bas',
        ];
    }

    /**
     * Presets de pages
     */
    protected function getPagePresets(): array
    {
        return [
            'home' => 'Page d\'accueil',
            'category' => 'Pages catégories',
            'product' => 'Pages produits',
            'cart' => 'Panier',
            'checkout' => 'Checkout',
            'custom' => 'URL personnalisée',
        ];
    }
}
