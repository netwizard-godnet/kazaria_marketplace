<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        // Bandeau supérieur (gif header)
        $headerBanner = Banner::where('banner_type', 'header_gif')->first();
        
        // Récupérer les bannières d'accueil
        $homepageBanner1 = Banner::where('banner_type', 'homepage_banner_1')->first();
        $homepageBanner2 = Banner::where('banner_type', 'homepage_banner_2')->first();
        $publicite1 = Banner::where('banner_type', 'publicite_1')->first();
        $publicite2 = Banner::where('banner_type', 'publicite_2')->first();
        $publicite3 = Banner::where('banner_type', 'publicite_3')->first();
        $publicite4 = Banner::where('banner_type', 'publicite_4')->first();
        $publicite5 = Banner::where('banner_type', 'publicite_5')->first();
        
        // Récupérer toutes les images du carousel boutique
        $boutiqueCarouselImages = Banner::getBoutiqueCarouselImages();
        
        // Récupérer les publicités boutique
        $boutiquePub1 = Banner::where('banner_type', 'boutique_pub_1')->first();
        $boutiquePub2 = Banner::where('banner_type', 'boutique_pub_2')->first();
        $boutiquePub3 = Banner::where('banner_type', 'boutique_pub_3')->first();
        $boutiquePub4 = Banner::where('banner_type', 'boutique_pub_4')->first();
        $boutiquePub5 = Banner::where('banner_type', 'boutique_pub_5')->first();
        
        // Récupérer les publicités catégorie
        $categoriePub1 = Banner::where('banner_type', 'categorie_pub_1')->first();
        $categoriePub2 = Banner::where('banner_type', 'categorie_pub_2')->first();
        $categoriePub3 = Banner::where('banner_type', 'categorie_pub_3')->first();
        $categoriePub4 = Banner::where('banner_type', 'categorie_pub_4')->first();
        $categoriePub5 = Banner::where('banner_type', 'categorie_pub_5')->first();
        
        // Récupérer les autres bannières
        $banners = Banner::where('banner_type', null)
                        ->orWhere('banner_type', '!=', 'homepage_banner_1')
                        ->where('banner_type', '!=', 'homepage_banner_2')
                        ->where('banner_type', '!=', 'publicite_1')
                        ->where('banner_type', '!=', 'publicite_2')
                        ->where('banner_type', '!=', 'publicite_3')
                        ->where('banner_type', '!=', 'publicite_4')
                        ->where('banner_type', '!=', 'publicite_5')
                        ->where('banner_type', 'not like', 'boutique_carousel_%')
                        ->where('banner_type', '!=', 'boutique_pub_1')
                        ->where('banner_type', '!=', 'boutique_pub_2')
                        ->where('banner_type', '!=', 'boutique_pub_3')
                        ->where('banner_type', '!=', 'boutique_pub_4')
                        ->where('banner_type', '!=', 'boutique_pub_5')
                        ->where('banner_type', '!=', 'categorie_pub_1')
                        ->where('banner_type', '!=', 'categorie_pub_2')
                        ->where('banner_type', '!=', 'categorie_pub_3')
                        ->where('banner_type', '!=', 'categorie_pub_4')
                        ->where('banner_type', '!=', 'categorie_pub_5')
                        ->orderBy('placement')
                        ->orderBy('sort_order')
                        ->paginate(15);
        
        return view('admin.banners.index', compact(
            'banners',
            'headerBanner',
            'homepageBanner1',
            'homepageBanner2',
            'publicite1',
            'publicite2',
            'publicite3',
            'publicite4',
            'publicite5',
            'boutiqueCarouselImages',
            'boutiquePub1',
            'boutiquePub2',
            'boutiquePub3',
            'boutiquePub4',
            'boutiquePub5',
            'categoriePub1',
            'categoriePub2',
            'categoriePub3',
            'categoriePub4',
            'categoriePub5'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'required|file',
            'placement' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ], $this->bannerMetaRules()));

        $path = $request->file('image')->store('banners', 'public');

        Banner::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'image_path' => $path,
            'link_url' => $request->input('link_url'),
            'placement' => $request->placement,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => (bool) $request->is_active,
            'show_on_desktop' => $request->boolean('show_on_desktop', true),
            'show_on_mobile' => $request->boolean('show_on_mobile', true),
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ]);

        return redirect()->back()->with('success', 'Bannière créée avec succès.');
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate(array_merge([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|file',
            'placement' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ], $this->bannerMetaRules()));

        if ($request->hasFile('image')) {
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $banner->image_path = $request->file('image')->store('banners', 'public');
        }

        $banner->fill([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'link_url' => $request->input('link_url'),
            'placement' => $request->placement,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => (bool) $request->is_active,
            'show_on_desktop' => $request->boolean('show_on_desktop', true),
            'show_on_mobile' => $request->boolean('show_on_mobile', true),
            'starts_at' => $request->starts_at,
            'ends_at' => $request->ends_at,
        ])->save();

        return redirect()->back()->with('success', 'Bannière mise à jour avec succès.');
    }

    public function show(Banner $banner)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'banner' => $banner
            ]);
        }
        
        return view('admin.banners.show', compact('banner'));
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }
        $banner->delete();
        return redirect()->back()->with('success', 'Bannière supprimée avec succès.');
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);
        
        $status = $banner->is_active ? 'activée' : 'désactivée';
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Bannière {$status} avec succès.",
                'banner' => $banner->fresh()
            ]);
        }
        
        return redirect()->back()->with('success', "Bannière {$status} avec succès.");
    }

    /**
     * Mettre à jour la bannière gif du header
     */
    public function updateHeaderGif(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
            'is_active' => 'nullable|boolean',
        ], $this->bannerMetaRules()));

        $banner = Banner::where('banner_type', 'header_gif')->first();

        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'header_gif';
            $banner->placement = 'header';
            $banner->title = 'Bannière Header';
            $banner->sort_order = 0;
            $banner->is_active = true;
        }

        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            @unlink(public_path($banner->image_path));
        }

        $image = $request->file('image');
        $imageName = 'header_gif_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);

        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->is_active = $request->has('is_active') ? (bool) $request->is_active : true;
        $banner->save();

        return redirect()->back()->with('success', 'Bannière du header mise à jour avec succès.');
    }

    /**
     * Gérer la première bannière d'accueil
     */
    public function updateHomepageBanner1(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la bannière homepage_banner_1
        $banner = Banner::where('banner_type', 'homepage_banner_1')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'homepage_banner_1';
            $banner->placement = 'homepage';
            $banner->title = 'Bannière Accueil 1';
            $banner->is_active = true;
            $banner->sort_order = 1;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'homepage_banner_1_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Bannière d\'accueil mise à jour avec succès.');
    }

    /**
     * Gérer la deuxième bannière d'accueil
     */
    public function updateHomepageBanner2(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la bannière homepage_banner_2
        $banner = Banner::where('banner_type', 'homepage_banner_2')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'homepage_banner_2';
            $banner->placement = 'homepage';
            $banner->title = 'Bannière Accueil 2';
            $banner->is_active = true;
            $banner->sort_order = 2;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'homepage_banner_2_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Deuxième bannière d\'accueil mise à jour avec succès.');
    }

    /**
     * Gérer la première publicité
     */
    public function updatePublicite1(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité publicite_1
        $banner = Banner::where('banner_type', 'publicite_1')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'publicite_1';
            $banner->placement = 'homepage';
            $banner->title = 'Publicité 1';
            $banner->is_active = true;
            $banner->sort_order = 3;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'publicite_1_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Publicité 1 mise à jour avec succès.');
    }

    /**
     * Gérer la deuxième publicité
     */
    public function updatePublicite2(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité publicite_2
        $banner = Banner::where('banner_type', 'publicite_2')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'publicite_2';
            $banner->placement = 'homepage';
            $banner->title = 'Publicité 2';
            $banner->is_active = true;
            $banner->sort_order = 4;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'publicite_2_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Publicité 2 mise à jour avec succès.');
    }

    /**
     * Gérer la troisième publicité
     */
    public function updatePublicite3(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité publicite_3
        $banner = Banner::where('banner_type', 'publicite_3')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'publicite_3';
            $banner->placement = 'homepage';
            $banner->title = 'Publicité 3';
            $banner->is_active = true;
            $banner->sort_order = 5;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'publicite_3_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Publicité 3 mise à jour avec succès.');
    }

    /**
     * Gérer la quatrième publicité
     */
    public function updatePublicite4(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité publicite_4
        $banner = Banner::where('banner_type', 'publicite_4')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'publicite_4';
            $banner->placement = 'homepage';
            $banner->title = 'Publicité 4';
            $banner->is_active = true;
            $banner->sort_order = 6;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'publicite_4_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Publicité 4 mise à jour avec succès.');
    }

    /**
     * Gérer la cinquième publicité
     */
    public function updatePublicite5(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité publicite_5
        $banner = Banner::where('banner_type', 'publicite_5')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'publicite_5';
            $banner->placement = 'homepage';
            $banner->title = 'Publicité 5';
            $banner->is_active = true;
            $banner->sort_order = 7;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'publicite_5_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Publicité 5 mise à jour avec succès.');
    }

    /**
     * Gérer la première image du carousel boutique
     */
    public function updateBoutiqueCarousel1(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer l'image carousel boutique 1
        $banner = Banner::where('banner_type', 'boutique_carousel_1')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'boutique_carousel_1';
            $banner->placement = 'boutique';
            $banner->title = 'Carousel Boutique 1';
            $banner->is_active = true;
            $banner->sort_order = 8;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'boutique_carousel_1_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Carousel Boutique 1 mis à jour avec succès.');
    }

    /**
     * Gérer la deuxième image du carousel boutique
     */
    public function updateBoutiqueCarousel2(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer l'image carousel boutique 2
        $banner = Banner::where('banner_type', 'boutique_carousel_2')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'boutique_carousel_2';
            $banner->placement = 'boutique';
            $banner->title = 'Carousel Boutique 2';
            $banner->is_active = true;
            $banner->sort_order = 9;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'boutique_carousel_2_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Carousel Boutique 2 mis à jour avec succès.');
    }

    /**
     * Gérer la troisième image du carousel boutique
     */
    public function updateBoutiqueCarousel3(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer l'image carousel boutique 3
        $banner = Banner::where('banner_type', 'boutique_carousel_3')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'boutique_carousel_3';
            $banner->placement = 'boutique';
            $banner->title = 'Carousel Boutique 3';
            $banner->is_active = true;
            $banner->sort_order = 10;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'boutique_carousel_3_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Carousel Boutique 3 mis à jour avec succès.');
    }

    /**
     * Ajouter une nouvelle image au carousel boutique
     */
    public function addBoutiqueCarouselImage(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver le prochain numéro d'ordre
        $nextOrder = Banner::where('banner_type', 'like', 'boutique_carousel_%')->max('sort_order') + 1;
        $nextNumber = Banner::where('banner_type', 'like', 'boutique_carousel_%')->count() + 1;

        // Créer la nouvelle image
        $banner = new Banner();
        $banner->banner_type = 'boutique_carousel_' . $nextNumber;
        $banner->placement = 'boutique';
        $banner->title = 'Carousel Boutique ' . $nextNumber;
        $banner->is_active = true;
        $banner->sort_order = $nextOrder;

        // Sauvegarder l'image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'boutique_carousel_' . $nextNumber . '_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Image ajoutée au carousel avec succès.');
    }

    /**
     * Supprimer une image du carousel boutique
     */
    public function removeBoutiqueCarouselImage(Banner $banner)
    {
        // Vérifier que c'est bien une image du carousel boutique
        if (!str_starts_with($banner->banner_type, 'boutique_carousel_')) {
            return redirect()->back()->with('error', 'Cette image ne fait pas partie du carousel boutique.');
        }

        // Supprimer l'image du serveur
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Supprimer de la base de données
        $banner->delete();

        return redirect()->back()->with('success', 'Image supprimée du carousel avec succès.');
    }

    /**
     * Mettre à jour une image du carousel boutique
     */
    public function updateBoutiqueCarouselImage(Request $request, Banner $banner)
    {
        // Vérifier que c'est bien une image du carousel boutique
        if (!str_starts_with($banner->banner_type, 'boutique_carousel_')) {
            return redirect()->back()->with('error', 'Cette image ne fait pas partie du carousel boutique.');
        }

        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = $banner->banner_type . '_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Image du carousel mise à jour avec succès.');
    }

    /**
     * Gérer la première publicité boutique
     */
    public function updateBoutiquePub1(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité boutique_pub_1
        $banner = Banner::where('banner_type', 'boutique_pub_1')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'boutique_pub_1';
            $banner->placement = 'boutique';
            $banner->title = 'Boutique Pub 1';
            $banner->is_active = true;
            $banner->sort_order = 12;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'boutique_pub_1_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Boutique Pub 1 mise à jour avec succès.');
    }

    /**
     * Gérer la deuxième publicité boutique
     */
    public function updateBoutiquePub2(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité boutique_pub_2
        $banner = Banner::where('banner_type', 'boutique_pub_2')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'boutique_pub_2';
            $banner->placement = 'boutique';
            $banner->title = 'Boutique Pub 2';
            $banner->is_active = true;
            $banner->sort_order = 13;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'boutique_pub_2_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Boutique Pub 2 mise à jour avec succès.');
    }

    /**
     * Gérer la troisième publicité boutique
     */
    public function updateBoutiquePub3(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité boutique_pub_3
        $banner = Banner::where('banner_type', 'boutique_pub_3')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'boutique_pub_3';
            $banner->placement = 'boutique';
            $banner->title = 'Boutique Pub 3';
            $banner->is_active = true;
            $banner->sort_order = 14;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'boutique_pub_3_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Boutique Pub 3 mise à jour avec succès.');
    }

    /**
     * Gérer la quatrième publicité boutique
     */
    public function updateBoutiquePub4(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité boutique_pub_4
        $banner = Banner::where('banner_type', 'boutique_pub_4')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'boutique_pub_4';
            $banner->placement = 'boutique';
            $banner->title = 'Boutique Pub 4';
            $banner->is_active = true;
            $banner->sort_order = 15;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'boutique_pub_4_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Boutique Pub 4 mise à jour avec succès.');
    }

    /**
     * Gérer la cinquième publicité boutique
     */
    public function updateBoutiquePub5(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité boutique_pub_5
        $banner = Banner::where('banner_type', 'boutique_pub_5')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'boutique_pub_5';
            $banner->placement = 'boutique';
            $banner->title = 'Boutique Pub 5';
            $banner->is_active = true;
            $banner->sort_order = 16;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'boutique_pub_5_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Boutique Pub 5 mise à jour avec succès.');
    }

    /**
     * Gérer la première publicité catégorie
     */
    public function updateCategoriePub1(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité categorie_pub_1
        $banner = Banner::where('banner_type', 'categorie_pub_1')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'categorie_pub_1';
            $banner->placement = 'categorie';
            $banner->title = 'Catégorie Pub 1';
            $banner->is_active = true;
            $banner->sort_order = 17;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'categorie_pub_1_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Catégorie Pub 1 mise à jour avec succès.');
    }

    /**
     * Gérer la deuxième publicité catégorie
     */
    public function updateCategoriePub2(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité categorie_pub_2
        $banner = Banner::where('banner_type', 'categorie_pub_2')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'categorie_pub_2';
            $banner->placement = 'categorie';
            $banner->title = 'Catégorie Pub 2';
            $banner->is_active = true;
            $banner->sort_order = 18;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'categorie_pub_2_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Catégorie Pub 2 mise à jour avec succès.');
    }

    /**
     * Gérer la troisième publicité catégorie
     */
    public function updateCategoriePub3(Request $request)
    {
        $request->validate(array_merge([
            'image' => 'required|file',
        ], $this->bannerMetaRules()));

        // Trouver ou créer la publicité categorie_pub_3
        $banner = Banner::where('banner_type', 'categorie_pub_3')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'categorie_pub_3';
            $banner->placement = 'categorie';
            $banner->title = 'Catégorie Pub 3';
            $banner->is_active = true;
            $banner->sort_order = 19;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'categorie_pub_3_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $this->applyBannerMeta($banner, $request);
        $banner->save();

        return redirect()->back()->with('success', 'Catégorie Pub 3 mise à jour avec succès.');
    }

    /**
     * Gérer la quatrième publicité catégorie
     */
    public function updateCategoriePub4(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file',
        ]);

        // Trouver ou créer la publicité categorie_pub_4
        $banner = Banner::where('banner_type', 'categorie_pub_4')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'categorie_pub_4';
            $banner->placement = 'categorie';
            $banner->title = 'Catégorie Pub 4';
            $banner->is_active = true;
            $banner->sort_order = 20;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'categorie_pub_4_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $banner->save();

        return redirect()->back()->with('success', 'Catégorie Pub 4 mise à jour avec succès.');
    }

    /**
     * Gérer la cinquième publicité catégorie
     */
    public function updateCategoriePub5(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file',
        ]);

        // Trouver ou créer la publicité categorie_pub_5
        $banner = Banner::where('banner_type', 'categorie_pub_5')->first();
        
        if (!$banner) {
            $banner = new Banner();
            $banner->banner_type = 'categorie_pub_5';
            $banner->placement = 'categorie';
            $banner->title = 'Catégorie Pub 5';
            $banner->is_active = true;
            $banner->sort_order = 21;
        }

        // Supprimer l'ancienne image si elle existe
        if ($banner->image_path && file_exists(public_path($banner->image_path))) {
            unlink(public_path($banner->image_path));
        }

        // Sauvegarder la nouvelle image dans le dossier public/images
        $image = $request->file('image');
        $imageName = 'categorie_pub_5_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images'), $imageName);
        
        $banner->image_path = 'images/' . $imageName;
        $banner->save();

        return redirect()->back()->with('success', 'Catégorie Pub 5 mise à jour avec succès.');
    }

    private function bannerMetaRules(): array
    {
        return [
            'link_url' => 'nullable|url|max:2048',
            'show_on_desktop' => 'nullable|boolean',
            'show_on_mobile' => 'nullable|boolean',
        ];
    }

    private function applyBannerMeta(Banner $banner, Request $request): void
    {
        $banner->link_url = $request->input('link_url');
        $banner->show_on_desktop = $request->boolean('show_on_desktop', true);
        $banner->show_on_mobile = $request->boolean('show_on_mobile', true);
    }
}
