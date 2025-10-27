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
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}" data-bs-interval="2000">
                                @if($image->image_url)
                                    <img src="{{ $image->image_url }}" class="d-block w-100 h-400px" alt="Carousel {{ $index + 1 }}">
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
                    @if($boutiquePub1Image)
                        <img src="{{ $boutiquePub1Image }}" class="w-100 h-200px object-fit-cover" alt="Boutique Pub 1">
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
                    @if($boutiquePub2Image)
                        <img src="{{ $boutiquePub2Image }}" class="w-100 h-200px object-fit-cover" alt="Boutique Pub 2">
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
                    @if($boutiquePub3Image)
                        <img src="{{ $boutiquePub3Image }}" class="w-100 h-200px object-fit-cover" alt="Boutique Pub 3">
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

        <!-- SECTION -->
        <section class="py-5">
            <div class="row g-3">
                <div class="col-12 col-sm-3 col-md-2" style="position: sticky; top: 0;">
                    <div class="blue-bg rounded-2 p-3 text-white">
                        <p class="mb-3 fw-bold d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-filter me-2"></i>Filtres</span>
                            <a href="{{ route('boutique_officielle') }}" class="btn btn-sm btn-outline-light">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        </p>
                        
                        <form method="GET" action="{{ route('boutique_officielle') }}" id="boutiqueFilterForm">
                            
                            <!-- Catégories -->
                            @if(isset($categories))
                            <div class="mb-3">
                                <p class="fw-bold mb-2 fs-7">Catégories</p>
                                @foreach($categories as $cat)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="category_id" 
                                        value="{{ $cat->id }}" id="boutiqueCat{{ $cat->id }}"
                                        {{ request('category_id') == $cat->id ? 'checked' : '' }}>
                                    <label class="form-check-label fs-8" for="boutiqueCat{{ $cat->id }}">
                                        @if($cat->icon)
                                        <i class="{{ $cat->icon }} me-1"></i>
                                        @endif
                                        {{ $cat->name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <hr class="text-white">
                            @endif
                            
                            <!-- Prix -->
                            @if(isset($priceRange))
                            <div class="mb-3">
                                <p class="fw-bold mb-2 fs-7">Prix (FCFA)</p>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="min_price" 
                                            placeholder="Min" value="{{ request('min_price') }}">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="max_price" 
                                            placeholder="Max" value="{{ request('max_price') }}">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-light w-100 mt-2">Appliquer</button>
                            </div>
                            <hr class="text-white">
                            @endif
                            
                            <!-- Note minimum -->
                            <div class="mb-3">
                                <p class="fw-bold mb-2 fs-7">Note minimum</p>
                                @for($i = 5; $i >= 1; $i--)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="min_rating" value="{{ $i }}" 
                                        id="boutiqueRating{{ $i }}" {{ request('min_rating') == $i ? 'checked' : '' }}>
                                    <label class="form-check-label fs-8" for="boutiqueRating{{ $i }}">
                                        @for($j = 1; $j <= $i; $j++)
                                            <i class="fa-solid fa-star text-warning"></i>
                                        @endfor
                                        & plus
                                    </label>
                                </div>
                                @endfor
                            </div>
                            
                        </form>
                    </div>
                </div>
                <div class="col-12 col-sm-9 col-md-10">
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
                    @if($boutiquePub4Image)
                        <img src="{{ $boutiquePub4Image }}" class="w-100 h-300px object-fit-cover" alt="Boutique Pub 4">
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
                    @if($boutiquePub5Image)
                        <img src="{{ $boutiquePub5Image }}" class="w-100 h-300px object-fit-cover" alt="Boutique Pub 5">
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
