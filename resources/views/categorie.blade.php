@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <main class="container-fluid">
        <!-- SECTION BREADCRUMB -->
        <section class="bg-light py-2">
            <div class="container-fluid">
                <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb" class="">
                    <ol class="breadcrumb" class="">
                        <li class="breadcrumb-item mb-0"><a href="{{ route('accueil') }}" class="fs-7">Accueil</a></li>
                        <li class="breadcrumb-item mb-0 active fs-7" aria-current="page">{{ $category->name }}</li>
                    </ol>
                </nav>
            </div>
        </section>
        <!-- SECTION BREADCRUMB END -->

        <!-- SECTION MEILLEURES OFFRES -->
        <section class="multi-carousel pb-5 border-top" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Meilleures offres</h5>
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
                            <img src="{{ $categoriePub1Image }}" class="w-100 h-200px object-fit-cover" alt="Catégorie Pub 1">
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
                            <img src="{{ $categoriePub2Image }}" class="w-100 h-200px object-fit-cover" alt="Catégorie Pub 2">
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
                            <img src="{{ $categoriePub3Image }}" class="w-100 h-200px object-fit-cover" alt="Catégorie Pub 3">
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

        <!-- SECTION NOUVEAUTES -->
        <section class="multi-carousel py-5" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Nouveautés</h5>
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
                <h5 class="offcanvas-title" id="mobileFiltersLabel">Filtres</h5>
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
                            <img src="{{ $categoriePub4Image }}" class="w-100 h-300px object-fit-cover" alt="Catégorie Pub 4">
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
                            <img src="{{ $categoriePub5Image }}" class="w-100 h-300px object-fit-cover" alt="Catégorie Pub 5">
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
    </main>

@endsection
