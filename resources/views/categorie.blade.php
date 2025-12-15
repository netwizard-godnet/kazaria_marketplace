@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
@php
    // Récupérer les personnalisations
    $isCustomized = $category->is_customized ?? false;
    $customColors = $category->custom_colors ?? [];
    $customBanners = $category->custom_banners ?? [];
    $customCarousels = $category->custom_carousels ?? [];
    
    // S'assurer que customColors est un tableau
    if (is_string($customColors)) {
        $customColors = json_decode($customColors, true) ?? [];
    }
    if (!is_array($customColors)) {
        $customColors = [];
    }
    
    // Couleurs par défaut
    $defaultColors = [
        'primary' => '#f04e27',
        'secondary' => '#333333',
        'background' => '#ffffff',
        'text' => '#333333',
        'accent' => '#f04e27',
    ];
    
    // Fusionner les couleurs personnalisées avec les couleurs par défaut
    $colors = array_merge($defaultColors, $customColors);
@endphp

@if($isCustomized)
<style>
    /* Styles personnalisés pour la catégorie */
    .category-custom-style {
        --category-primary: {{ $colors['primary'] ?? '#f04e27' }};
        --category-secondary: {{ $colors['secondary'] ?? '#333333' }};
        --category-background: {{ $colors['background'] ?? '#ffffff' }};
        --category-text: {{ $colors['text'] ?? '#333333' }};
        --category-accent: {{ $colors['accent'] ?? '#f04e27' }};
    }
    
    @if(isset($customColors['primary']) && !empty($customColors['primary']))
    .category-custom-style .breadcrumb-item a,
    .category-custom-style .breadcrumb-item.active {
        color: var(--category-primary) !important;
    }
    
    .category-custom-style .orange-color {
        color: var(--category-primary) !important;
    }
    
    .category-custom-style .blue-bg {
        background-color: var(--category-primary) !important;
    }
    @endif
    
    @if(isset($customColors['text']) && !empty($customColors['text']))
    .category-custom-style .section-title {
        color: var(--category-text) !important;
    }
    @endif
    
    @if(isset($customColors['background']) && !empty($customColors['background']))
    .category-custom-style main,
    .category-custom-style .bg-white,
    .category-custom-style section.bg-white {
        background-color: var(--category-background) !important;
    }
    
    .category-custom-style .container-fluid {
        background-color: var(--category-background) !important;
    }
    
    .category-custom-style body {
        background-color: var(--category-background) !important;
    }
    @endif
</style>
@endif

    <main class="container-fluid {{ $isCustomized ? 'category-custom-style' : '' }}">
        <!-- SECTION BREADCRUMB ET TITRE -->
        <section class="bg-white py-3 border-bottom">
            <div class="container-fluid">
                <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2" style="--bs-breadcrumb-item-color: #f04e27;">
                        <li class="breadcrumb-item">
                            <a href="{{ route('accueil') }}" class="text-decoration-none" style="color: #f04e27;">Accueil</a>
                        </li>
                        @if(isset($subcategory) && $subcategory)
                            <li class="breadcrumb-item">
                                <a href="{{ route('categorie', $category->slug) }}" class="text-decoration-none" style="color: #f04e27;">{{ $category->name }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page" style="color: #f04e27;">{{ $subcategory->name }}</li>
                        @else
                            <li class="breadcrumb-item active" aria-current="page" style="color: #f04e27;">{{ $category->name }}</li>
                        @endif
                    </ol>
                </nav>
                <h1 class="fw-bold mb-0" style="font-size: 1.6rem; color: #333;">
                    @if(isset($subcategory) && $subcategory)
                        {{ $subcategory->name }}
                    @else
                        {{ $category->name }}
                    @endif
                </h1>
            </div>
        </section>
        <!-- SECTION BREADCRUMB ET TITRE END -->

        @php
            // Déterminer si la personnalisation est activée
            $isCustomized = $category->is_customized ?? false;
            $customLayout = $category->custom_layout ?? [];
            $customBanners = $category->custom_banners ?? [];
            $customCarousels = $category->custom_carousels ?? [];
            
            // Définir l'ordre par défaut des sections
            $defaultSections = [
                'custom_carousel_top' => ['enabled' => false, 'order' => 0.5, 'title' => 'Carrousel personnalisé (haut)'],
                'best_offers' => ['enabled' => true, 'order' => 1, 'title' => 'Meilleures offres'],
                'custom_banners_top' => ['enabled' => false, 'order' => 1.5, 'title' => 'Bannières personnalisées (haut)'],
                'banners_top' => ['enabled' => true, 'order' => 2, 'title' => 'Bannières supérieures'],
                'new_products' => ['enabled' => true, 'order' => 3, 'title' => 'Nouveautés'],
                'custom_carousel_middle' => ['enabled' => false, 'order' => 3.5, 'title' => 'Carrousel personnalisé (milieu)'],
                'products_list' => ['enabled' => true, 'order' => 4, 'title' => 'Liste des produits'],
                'banners_bottom' => ['enabled' => true, 'order' => 5, 'title' => 'Bannières inférieures'],
                'custom_banners_bottom' => ['enabled' => false, 'order' => 5.5, 'title' => 'Bannières personnalisées (bas)'],
                'custom_carousel_bottom' => ['enabled' => false, 'order' => 6, 'title' => 'Carrousel personnalisé (bas)'],
            ];
            
            // Ajouter les sections personnalisées si elles existent
            if ($isCustomized && !empty($customBanners)) {
                foreach ($customBanners as $index => $banner) {
                    $key = 'custom_banner_' . $index;
                    $defaultSections[$key] = [
                        'enabled' => $banner['enabled'] ?? true,
                        'order' => $banner['order'] ?? (10 + $index),
                        'title' => $banner['title'] ?? ('Bannière #' . ($index + 1)),
                        'banner_data' => $banner,
                        'is_custom' => true,
                        'type' => 'banner'
                    ];
                }
            }
            
            if ($isCustomized && !empty($customCarousels)) {
                foreach ($customCarousels as $index => $carousel) {
                    $key = 'custom_carousel_' . $index;
                    $defaultSections[$key] = [
                        'enabled' => $carousel['enabled'] ?? true,
                        'order' => $carousel['order'] ?? (20 + $index),
                        'title' => $carousel['title'] ?? ('Carrousel #' . ($index + 1)),
                        'carousel_data' => $carousel,
                        'is_custom' => true,
                        'type' => 'carousel'
                    ];
                }
            }
            
            // Fusionner les sections par défaut avec les sections personnalisées
            $sections = $defaultSections;
            if ($isCustomized && is_array($customLayout) && !empty($customLayout)) {
                foreach ($customLayout as $key => $config) {
                    if (isset($sections[$key])) {
                        $sections[$key] = array_merge($sections[$key], $config);
                    }
                }
            }
            
            // Trier les sections par ordre
            uasort($sections, function($a, $b) {
                return ($a['order'] ?? 999) <=> ($b['order'] ?? 999);
            });
        @endphp

        @foreach($sections as $sectionKey => $sectionConfig)
            @if(($sectionConfig['enabled'] ?? true))
                @if($sectionKey === 'best_offers')
                    <!-- SECTION MEILLEURES OFFRES -->
                    <section class="multi-carousel pb-5 border-top" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
                        <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                            <h4 class="section-title mb-0 me-4">{{ $sectionConfig['title'] ?? 'Meilleures offres' }}</h4>
                        </div>
                        <div class="multi-carousel-track d-flex">
                            @forelse ($bestOffers as $product)
                            <div class="multi-carousel-item px-2">
                                @include('components.product-card', ['product' => $product])
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-muted text-center py-4">Aucune offre disponible pour le moment.</p>
                            </div>
                            @endforelse
                        </div>
                        @if($bestOffers->count() > 0)
                        <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
                        <div class="multi-carousel-dots text-center mt-2"></div>
                        @endif
                    </section>
                    <!-- SECTION MEILLEURES OFFRES END -->
                @elseif($sectionKey === 'banners_top')
                    <!-- SECTION AFFICHES -->
                    <section class="py-5">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <!-- Catégorie pub 1 -->
                                @php
                                    $categoriePub1 = App\Models\Banner::getCategoriePub1();
                                    $categoriePub1Image = $categoriePub1 ? $categoriePub1->image_url : null;
                                @endphp
                                @if($categoriePub1 && $categoriePub1Image)
                                    <div class="{{ $categoriePub1->visibility_classes ?? '' }}">
                                        @if($categoriePub1->link_url)
                                            <a href="{{ $categoriePub1->link_url }}" target="_blank" rel="noopener" class="d-block">
                                        @endif
                                    <img src="{{ $categoriePub1Image }}" class="w-100 object-fit-cover" alt="Catégorie Pub 1">
                                        @if($categoriePub1->link_url)</a>@endif
                                    </div>
                                @else
                                    <div class="w-100 h-200px bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted fa-3x"></i>
                                    </div>
                                @endif
                                <!-- Catégorie pub 1 end -->
                            </div>
                            <div class="col-md-4">
                                <!-- Catégorie pub 2 -->
                                @php
                                    $categoriePub2 = App\Models\Banner::getCategoriePub2();
                                    $categoriePub2Image = $categoriePub2 ? $categoriePub2->image_url : null;
                                @endphp
                                @if($categoriePub2 && $categoriePub2Image)
                                    <div class="{{ $categoriePub2->visibility_classes ?? '' }}">
                                        @if($categoriePub2->link_url)
                                            <a href="{{ $categoriePub2->link_url }}" target="_blank" rel="noopener" class="d-block">
                                        @endif
                                    <img src="{{ $categoriePub2Image }}" class="w-100 object-fit-cover" alt="Catégorie Pub 2">
                                        @if($categoriePub2->link_url)</a>@endif
                                    </div>
                                @else
                                    <div class="w-100 h-200px bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted fa-3x"></i>
                                    </div>
                                @endif
                                <!-- Catégorie pub 2 end -->
                            </div>
                            <div class="col-md-4">
                                <!-- Catégorie pub 3 -->
                                @php
                                    $categoriePub3 = App\Models\Banner::getCategoriePub3();
                                    $categoriePub3Image = $categoriePub3 ? $categoriePub3->image_url : null;
                                @endphp
                                @if($categoriePub3 && $categoriePub3Image)
                                    <div class="{{ $categoriePub3->visibility_classes ?? '' }}">
                                        @if($categoriePub3->link_url)
                                            <a href="{{ $categoriePub3->link_url }}" target="_blank" rel="noopener" class="d-block">
                                        @endif
                                    <img src="{{ $categoriePub3Image }}" class="w-100 object-fit-cover" alt="Catégorie Pub 3">
                                        @if($categoriePub3->link_url)</a>@endif
                                    </div>
                                @else
                                    <div class="w-100 h-200px bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted fa-3x"></i>
                                    </div>
                                @endif
                                <!-- Catégorie pub 3 end -->
                            </div>
                        </div>
                    </section>
                    <!-- SECTION AFFICHES END -->
                @elseif($sectionKey === 'new_products')
                    <!-- SECTION NOUVEAUTES -->
                    <section class="multi-carousel py-5" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
                            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                                <h4 class="section-title mb-0 me-4">{{ $sectionConfig['title'] ?? 'Nouveautés' }}</h4>
                        </div>
                        <div class="multi-carousel-track d-flex">
                            @forelse ($newProducts as $product)
                            <div class="multi-carousel-item px-2">
                                @include('components.product-card', ['product' => $product])
                            </div>
                            @empty
                            <div class="col-12">
                                <p class="text-muted text-center py-4">Aucune nouveauté disponible pour le moment.</p>
                            </div>
                            @endforelse
                        </div>
                        @if($newProducts->count() > 0)
                        <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
                        <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
                        <div class="multi-carousel-dots text-center mt-2"></div>
                        @endif
                    </section>
                    <!-- SECTION NOUVEAUTES END -->
                @elseif($sectionKey === 'products_list')
                    @php
                        $hasAttributeFilters = collect(request('attributes', []))->flatten()->filter()->isNotEmpty();
                        $activeFilters = 0;
                        if(request()->filled('subcategory')) $activeFilters++;
                        if(request()->filled('min_price')) $activeFilters++;
                        if(request()->filled('max_price')) $activeFilters++;
                        if(request()->filled('min_rating')) $activeFilters++;
                        if($hasAttributeFilters) $activeFilters++;
                    @endphp
                    <!-- SECTION -->
                    <section class="py-5">
                        <div class="d-sm-none mb-3">
                            <button class="btn blue-bg text-white w-100 d-flex align-items-center justify-content-between"
                                type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileFilters" aria-controls="mobileFilters">
                                <span>Filtrer les résultats<i class="bi bi-funnel ms-2"></i></span>
                                @if($activeFilters > 0)
                                    <span class="badge bg-white text-dark">{{ $activeFilters }} actif(s)</span>
                                @endif
                            </button>
                        </div>
                        <div class="row g-4 align-items-start">
                            <div class="col-lg-3 col-xl-2 d-none d-lg-block">
                                @include('components.category-filter-form', [
                                    'category' => $category,
                                    'priceRange' => $priceRange ?? null,
                                    'attributes' => $attributes ?? null,
                                    'formId' => 'desktopFilterForm',
                                    'inputPrefix' => 'desktop-',
                                    'wrapperClass' => 'sticky-top'
                                ])
                            </div>
                            <div class="col-12 col-lg-9 col-xl-10">
                                <div id="productResults">
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <div class="bg-light d-flex align-items-center justify-content-between mb-4 border-bottom p-2">
                                                <p class="mb-0 me-4">{{ $category->name }} ({{ $products->total() }} résultats)</p>
                                                <div class="">
                                                    <form method="GET" action="{{ route('categorie', $category->slug) }}" class="d-inline" id="categorySortForm">
                                                        @foreach(request()->except(['sort', 'order']) as $key => $value)
                                                            @if(is_array($value))
                                                                @foreach($value as $subKey => $subValue)
                                                                    @if(is_array($subValue))
                                                                        @foreach($subValue as $item)
                                                                            <input type="hidden" name="{{ $key }}[{{ $subKey }}][]" value="{{ $item }}">
                                                                        @endforeach
                                                                    @else
                                                                        <input type="hidden" name="{{ $key }}[{{ $subKey }}]" value="{{ $subValue }}">
                                                                    @endif
                                                                @endforeach
                                                            @else
                                                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                            @endif
                                                        @endforeach
                                                        <select name="sort" class="form-select form-select-sm d-inline-block w-auto">
                                                            <option value="">Trier par...</option>
                                                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                                                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                                                            <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Meilleures notes</option>
                                                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Popularité</option>
                                                            <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>Nouveautés</option>
                                                        </select>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @forelse ($products as $product)
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="px-1">
                                                @include('components.product-card', ['product' => $product])
                                            </div>
                                        </div>
                                        @empty
                                        <div class="col-12">
                                            <div class="alert alert-info text-center">
                                                Aucun produit disponible dans cette catégorie pour le moment.
                                            </div>
                                        </div>
                                        @endforelse
                                    </div>
                                    
                                    <!-- Pagination -->
                                    @if($products->hasPages())
                                    <div class="d-flex justify-content-center mt-4">
                                        {{ $products->links('pagination.custom') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>
                    <div class="offcanvas offcanvas-start filter-offcanvas" tabindex="-1" id="mobileFilters" aria-labelledby="mobileFiltersLabel">
                        <div class="offcanvas-header">
                                    <h5 class="offcanvas-title section-title" id="mobileFiltersLabel">Filtres</h5>
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('components.category-filter-form', [
                                'category' => $category,
                                'priceRange' => $priceRange ?? null,
                                'attributes' => $attributes ?? null,
                                'formId' => 'mobileFilterForm',
                                'inputPrefix' => 'mobile-',
                                'wrapperClass' => 'mb-0'
                            ])
                        </div>
                    </div>
                    <!-- SECTION END -->
                @elseif($sectionKey === 'banners_bottom')
                    <!-- SECTION AFFICHES -->
                    <section class="py-5">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <!-- Catégorie pub 4 -->
                                @php
                                    $categoriePub4 = App\Models\Banner::getCategoriePub4();
                                    $categoriePub4Image = $categoriePub4 ? $categoriePub4->image_url : null;
                                @endphp
                                @if($categoriePub4 && $categoriePub4Image)
                                    <div class="{{ $categoriePub4->visibility_classes ?? '' }}">
                                        @if($categoriePub4->link_url)
                                            <a href="{{ $categoriePub4->link_url }}" target="_blank" rel="noopener" class="d-block">
                                        @endif
                                    <img src="{{ $categoriePub4Image }}" class="w-100 object-fit-cover" alt="Catégorie Pub 4">
                                        @if($categoriePub4->link_url)</a>@endif
                                    </div>
                                @else
                                    <div class="w-100 h-300px bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted fa-3x"></i>
                                    </div>
                                @endif
                                <!-- Catégorie pub 4 end -->
                            </div>
                            <div class="col-md-4">
                                <!-- Catégorie pub 5 -->
                                @php
                                    $categoriePub5 = App\Models\Banner::getCategoriePub5();
                                    $categoriePub5Image = $categoriePub5 ? $categoriePub5->image_url : null;
                                @endphp
                                @if($categoriePub5 && $categoriePub5Image)
                                    <div class="{{ $categoriePub5->visibility_classes ?? '' }}">
                                        @if($categoriePub5->link_url)
                                            <a href="{{ $categoriePub5->link_url }}" target="_blank" rel="noopener" class="d-block">
                                        @endif
                                    <img src="{{ $categoriePub5Image }}" class="w-100 object-fit-cover" alt="Catégorie Pub 5">
                                        @if($categoriePub5->link_url)</a>@endif
                                    </div>
                                @else
                                    <div class="w-100 h-300px bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted fa-3x"></i>
                                    </div>
                                @endif
                                <!-- Catégorie pub 5 end -->
                            </div>
                        </div>
                    </section>
                    <!-- SECTION AFFICHES END -->
                @elseif(strpos($sectionKey, 'custom_banner_') === 0 && isset($sectionConfig['banner_data']))
                    @php
                        $banner = $sectionConfig['banner_data'];
                    @endphp
                    <!-- BANNIÈRE PERSONNALISÉE -->
                    <section class="py-5">
                        <div class="row g-3">
                            <div class="{{ $banner['columns'] ?? 'col-12' }}">
                                @if(isset($banner['image']) && $banner['image'])
                                    <div class="{{ $banner['visibility_classes'] ?? '' }}">
                                        @if(isset($banner['link_url']) && $banner['link_url'])
                                            <a href="{{ $banner['link_url'] }}" target="{{ $banner['link_target'] ?? '_blank' }}" rel="noopener" class="d-block">
                                        @endif
                                        <img src="{{ str_starts_with($banner['image'], 'http') ? $banner['image'] : (str_starts_with($banner['image'], 'images/') ? asset($banner['image']) : asset('storage/' . $banner['image'])) }}" 
                                             class="w-100 {{ $banner['image_class'] ?? 'object-fit-cover' }}" 
                                             alt="{{ $banner['alt'] ?? 'Bannière personnalisée' }}"
                                             style="{{ isset($banner['height']) ? 'height: ' . $banner['height'] . ';' : '' }}">
                                        @if(isset($banner['link_url']) && $banner['link_url'])</a>@endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                    <!-- BANNIÈRE PERSONNALISÉE END -->
                @elseif(strpos($sectionKey, 'custom_carousel_') === 0 && isset($sectionConfig['carousel_data']))
                    @php
                        $carousel = $sectionConfig['carousel_data'];
                        $carouselImages = $carousel['images'] ?? [];
                    @endphp
                    @if(!empty($carouselImages))
                        <!-- CARROUSEL PERSONNALISÉ -->
                        <section class="multi-carousel py-5" 
                                 data-multi-carousel 
                                 data-slides-to-show="{{ $carousel['slides_to_show'] ?? 6 }}" 
                                 data-slides-lg="{{ $carousel['slides_lg'] ?? 4 }}" 
                                 data-slides-md="{{ $carousel['slides_md'] ?? 3 }}" 
                                 data-slides-sm="{{ $carousel['slides_sm'] ?? 2 }}" 
                                 data-slides-xs="{{ $carousel['slides_xs'] ?? 2 }}" 
                                 data-gap="{{ $carousel['gap'] ?? 0 }}" 
                                 data-autoplay="{{ $carousel['autoplay'] ?? 'true' }}" 
                                 data-autoplay-speed="{{ $carousel['autoplay_speed'] ?? 2000 }}" 
                                 data-pause-on-hover="{{ $carousel['pause_on_hover'] ?? 'true' }}">
                            @if(isset($carousel['title']) && $carousel['title'])
                                <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                                    <h4 class="section-title mb-0 me-4">{{ $carousel['title'] }}</h4>
                                </div>
                            @endif
                            <div class="multi-carousel-track d-flex">
                                @foreach($carouselImages as $image)
                                    <div class="multi-carousel-item px-2">
                                        @if(isset($image['link_url']) && $image['link_url'])
                                            <a href="{{ $image['link_url'] }}" target="{{ $image['link_target'] ?? '_blank' }}" rel="noopener" class="d-block">
                                        @endif
                                        <img src="{{ str_starts_with($image['url'], 'http') ? $image['url'] : (str_starts_with($image['url'], 'images/') ? asset($image['url']) : asset('storage/' . $image['url'])) }}" 
                                             class="w-100 {{ $image['class'] ?? 'object-fit-cover' }}" 
                                             alt="{{ $image['alt'] ?? 'Image carrousel' }}"
                                             style="{{ isset($image['height']) ? 'height: ' . $image['height'] . ';' : '' }}">
                                        @if(isset($image['link_url']) && $image['link_url'])</a>@endif
                                    </div>
                                @endforeach
                            </div>
                            @if(count($carouselImages) > 0)
                                <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
                                <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
                                <div class="multi-carousel-dots text-center mt-2"></div>
                            @endif
                        </section>
                        <!-- CARROUSEL PERSONNALISÉ END -->
                    @endif
                @endif
            @endif
        @endforeach
    </main>

@endsection
