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
                        <li class="breadcrumb-item mb-0 active fs-7" aria-current="page">Boutique Officielle</li>
                    </ol>
                </nav>
            </div>
        </section>
        <!-- SECTION BREADCRUMB END -->

        <!-- SECTION BANNER -->
        <div class="row g-2 d-flex align-items-center justify-content-center">
            <div class="col-md-12">
                <div class="">
                    <!-- Carousel Boutique -->
                    @php
                        $boutiqueCarouselImages = App\Models\Banner::getBoutiqueCarouselImages();
                    @endphp
                    <div id="carouselExampleAutoplaying" class="carousel slide h-400px" data-bs-ride="carousel">
                        <div class="carousel-inner h-400px">
                            @forelse($boutiqueCarouselImages as $index => $image)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }} {{ $image->visibility_classes ?? '' }}" data-bs-interval="2000">
                                @if($image->image_url)
                                    @if($image->link_url)
                                        <a href="{{ $image->link_url }}" target="_blank" rel="noopener" class="d-block h-100">
                                    @endif
                                    <img src="{{ $image->image_url }}" class="d-block w-100 h-400px" alt="Carousel {{ $index + 1 }}">
                                    @if($image->link_url)</a>@endif
                                @else
                                    <div class="d-block w-100 h-400px bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted fa-5x"></i>
                                    </div>
                                @endif
                            </div>
                            @empty
                            <div class="carousel-item active" data-bs-interval="2000">
                                <div class="d-block w-100 h-400px bg-light d-flex align-items-center justify-content-center">
                                    <div class="text-center">
                                        <i class="fas fa-image text-muted fa-5x mb-3"></i>
                                        <p class="text-muted">Aucune image dans le carousel</p>
                                    </div>
                                </div>
                            </div>
                            @endforelse
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                    <!-- Carousel Boutique End -->
                </div>
            </div>
        </div>
        <!-- SECTION BANNER END -->

        <!-- SECTION DEALS JOUR -->
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
        <!-- SECTION DEALS JOUR END -->

        <!-- SECTION AFFICHES -->
        <section class="py-5">
            <div class="row g-3">
                <div class="col-md-4">
                    <!-- Boutique pub 1 -->
                    @php
                        $boutiquePub1 = App\Models\Banner::getBoutiquePub1();
                        $boutiquePub1Image = $boutiquePub1 ? $boutiquePub1->image_url : null;
                    @endphp
                    @if($boutiquePub1 && $boutiquePub1Image)
                        <div class="{{ $boutiquePub1->visibility_classes ?? '' }}">
                            @if($boutiquePub1->link_url)
                                <a href="{{ $boutiquePub1->link_url }}" target="_blank" rel="noopener" class="d-block">
                            @endif
                            <img src="{{ $boutiquePub1Image }}" class="w-100 h-200px object-fit-cover" alt="Boutique Pub 1">
                            @if($boutiquePub1->link_url)</a>@endif
                        </div>
                    @else
                        <div class="w-100 h-200px bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image text-muted fa-3x"></i>
                        </div>
                    @endif
                    <!-- Boutique pub 1 end -->
                </div>
                <div class="col-md-4">
                    <!-- Boutique pub 2 -->
                    @php
                        $boutiquePub2 = App\Models\Banner::getBoutiquePub2();
                        $boutiquePub2Image = $boutiquePub2 ? $boutiquePub2->image_url : null;
                    @endphp
                    @if($boutiquePub2 && $boutiquePub2Image)
                        <div class="{{ $boutiquePub2->visibility_classes ?? '' }}">
                            @if($boutiquePub2->link_url)
                                <a href="{{ $boutiquePub2->link_url }}" target="_blank" rel="noopener" class="d-block">
                            @endif
                            <img src="{{ $boutiquePub2Image }}" class="w-100 h-200px object-fit-cover" alt="Boutique Pub 2">
                            @if($boutiquePub2->link_url)</a>@endif
                        </div>
                    @else
                        <div class="w-100 h-200px bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image text-muted fa-3x"></i>
                        </div>
                    @endif
                    <!-- Boutique pub 2 end -->
                </div>
                <div class="col-md-4">
                    <!-- Boutique pub 3 -->
                    @php
                        $boutiquePub3 = App\Models\Banner::getBoutiquePub3();
                        $boutiquePub3Image = $boutiquePub3 ? $boutiquePub3->image_url : null;
                    @endphp
                    @if($boutiquePub3 && $boutiquePub3Image)
                        <div class="{{ $boutiquePub3->visibility_classes ?? '' }}">
                            @if($boutiquePub3->link_url)
                                <a href="{{ $boutiquePub3->link_url }}" target="_blank" rel="noopener" class="d-block">
                            @endif
                            <img src="{{ $boutiquePub3Image }}" class="w-100 h-200px object-fit-cover" alt="Boutique Pub 3">
                            @if($boutiquePub3->link_url)</a>@endif
                        </div>
                    @else
                        <div class="w-100 h-200px bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image text-muted fa-3x"></i>
                        </div>
                    @endif
                    <!-- Boutique pub 3 end -->
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
            $activeFilters = 0;
            if(request()->filled('category_id')) $activeFilters++;
            if(request()->filled('min_price')) $activeFilters++;
            if(request()->filled('max_price')) $activeFilters++;
            if(request()->filled('min_rating')) $activeFilters++;
        @endphp
        <!-- SECTION -->
        <section class="py-5">
            <div class="d-sm-none mb-3">
                <button class="btn blue-bg text-white w-100 d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="offcanvas" data-bs-target="#boutiqueFilters" aria-controls="boutiqueFilters">
                    <span>Filtrer les résultats<i class="bi bi-funnel ms-2"></i></span>
                    @if($activeFilters > 0)
                        <span class="badge bg-white text-dark">{{ $activeFilters }} actif(s)</span>
                    @endif
                </button>
            </div>
            <div class="row g-4 align-items-start">
                <div class="col-lg-3 col-xl-2 d-none d-lg-block">
                    @include('components.boutique-filter-form', [
                        'categories' => $categories ?? [],
                        'priceRange' => $priceRange ?? null,
                        'formId' => 'boutiqueFilterFormDesktop',
                        'wrapperClass' => 'sticky-top'
                    ])
                </div>
                <div class="col-12 col-lg-9 col-xl-10">
                    <div id="boutiqueResults">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="bg-light d-flex align-items-center justify-content-between mb-4 border-bottom p-2">
                                    <p class="mb-0 me-4">Produits ({{ $products->total() }} résultats)</p>
                                    <div class="">
                                        <form method="GET" action="{{ route('boutique_officielle') }}" class="d-inline" id="boutiqueSortForm">
                                            @foreach(request()->except(['sort']) as $key => $value)
                                                @if(!is_array($value))
                                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                @endif
                                            @endforeach
                                            <select name="sort" class="form-select form-select-sm d-inline-block w-auto">
                                                <option value="">Trier par...</option>
                                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                                                <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Meilleures notes</option>
                                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Popularité</option>
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
                                    Aucun produit disponible dans la boutique officielle pour le moment.
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
        <div class="offcanvas offcanvas-start filter-offcanvas" tabindex="-1" id="boutiqueFilters" aria-labelledby="boutiqueFiltersLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="boutiqueFiltersLabel">Filtres</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                @include('components.boutique-filter-form', [
                    'categories' => $categories ?? [],
                    'priceRange' => $priceRange ?? null,
                    'formId' => 'boutiqueFilterFormMobile'
                ])
            </div>
        </div>
        <!-- SECTION END -->

        <!-- SECTION AFFICHES -->
        <section class="py-5">
            <div class="row g-3">
                <div class="col-md-8">
                    <!-- Boutique pub 4 -->
                    @php
                        $boutiquePub4 = App\Models\Banner::getBoutiquePub4();
                        $boutiquePub4Image = $boutiquePub4 ? $boutiquePub4->image_url : null;
                    @endphp
                    @if($boutiquePub4 && $boutiquePub4Image)
                        <div class="{{ $boutiquePub4->visibility_classes ?? '' }}">
                            @if($boutiquePub4->link_url)
                                <a href="{{ $boutiquePub4->link_url }}" target="_blank" rel="noopener" class="d-block">
                            @endif
                            <img src="{{ $boutiquePub4Image }}" class="w-100 h-300px object-fit-cover" alt="Boutique Pub 4">
                            @if($boutiquePub4->link_url)</a>@endif
                        </div>
                    @else
                        <div class="w-100 h-300px bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image text-muted fa-3x"></i>
                        </div>
                    @endif
                    <!-- Boutique pub 4 end -->
                </div>
                <div class="col-md-4">
                    <!-- Boutique pub 5 -->
                    @php
                        $boutiquePub5 = App\Models\Banner::getBoutiquePub5();
                        $boutiquePub5Image = $boutiquePub5 ? $boutiquePub5->image_url : null;
                    @endphp
                    @if($boutiquePub5 && $boutiquePub5Image)
                        <div class="{{ $boutiquePub5->visibility_classes ?? '' }}">
                            @if($boutiquePub5->link_url)
                                <a href="{{ $boutiquePub5->link_url }}" target="_blank" rel="noopener" class="d-block">
                            @endif
                            <img src="{{ $boutiquePub5Image }}" class="w-100 h-300px object-fit-cover" alt="Boutique Pub 5">
                            @if($boutiquePub5->link_url)</a>@endif
                        </div>
                    @else
                        <div class="w-100 h-300px bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image text-muted fa-3x"></i>
                        </div>
                    @endif
                    <!-- Boutique pub 5 end -->
                </div>
            </div>
        </section>
        <!-- SECTION AFFICHES END -->
    </main>

@endsection
