@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <main class="container-fluid">
        <!-- SECTION BREADCRUMB ET TITRE -->
        <section class="bg-white py-3 border-bottom">
            <div class="container-fluid">
                <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2" style="--bs-breadcrumb-item-color: #f04e27;">
                        <li class="breadcrumb-item">
                            <a href="{{ route('accueil') }}" class="text-decoration-none" style="color: #f04e27;">Accueil</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page" style="color: #f04e27;">
                            @if($searchQuery)
                                Recherche
                            @else
                                Tous les produits
                            @endif
                        </li>
                    </ol>
                </nav>
                <h1 class="fw-bold mb-0" style="font-size: 2rem; color: #333;">
                    @if($searchQuery)
                        Résultats pour "{{ $searchQuery }}"
                    @else
                        Tous les produits
                    @endif
                </h1>
            </div>
        </section>
        <!-- SECTION BREADCRUMB ET TITRE END -->

        @php
            $activeFilters = 0;
            if(request()->filled('category_id')) $activeFilters++;
            if(request()->filled('min_price')) $activeFilters++;
            if(request()->filled('max_price')) $activeFilters++;
            if(request()->filled('min_rating')) $activeFilters++;
        @endphp
        <!-- SECTION -->
        <section class="py-3">
            <div class="d-sm-none mb-3">
                <button class="btn blue-bg text-white w-100 d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="offcanvas" data-bs-target="#searchFilters" aria-controls="searchFilters">
                    <span>Filtrer les résultats<i class="bi bi-funnel ms-2"></i></span>
                    @if($activeFilters > 0)
                        <span class="badge bg-white text-dark">{{ $activeFilters }} actif(s)</span>
                    @endif
                </button>
            </div>
            <div class="row g-4 align-items-start">
                <div class="col-lg-3 col-xl-2 d-none d-lg-block">
                    @include('components.search-filter-form', [
                        'categories' => $categories,
                        'priceRange' => $priceRange ?? null,
                        'formId' => 'searchFilterFormDesktop',
                        'wrapperClass' => 'sticky-top',
                        'searchQuery' => $searchQuery ?? null
                    ])
                </div>
                <div class="col-12 col-lg-9 col-xl-10 bg-light z-index-7x">
                    <div id="searchResults" class="">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="bg-light d-flex align-items-center justify-content-between mb-4 border-bottom p-2">
                                    <p class="mb-0 me-4">
                                        @if($searchQuery)
                                            Résultats pour "{{ $searchQuery }}" ({{ $products->total() }} produits)
                                        @else
                                            Tous les produits ({{ $products->total() }} produits)
                                        @endif
                                    </p>
                                    <div class="">
                                        <form method="GET" action="{{ route('search_product') }}" class="d-inline" id="searchSortForm">
                                            @if($searchQuery)
                                            <input type="hidden" name="q" value="{{ $searchQuery }}">
                                            @endif
                                            @foreach(request()->except(['sort', 'q']) as $key => $value)
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
                                    @if($searchQuery)
                                        Aucun produit trouvé pour "{{ $searchQuery }}". Essayez avec d'autres mots-clés.
                                    @else
                                        Aucun produit disponible pour le moment.
                                    @endif
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
        <div class="offcanvas offcanvas-start filter-offcanvas" tabindex="-1" id="searchFilters" aria-labelledby="searchFiltersLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="searchFiltersLabel">Filtres</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                @include('components.search-filter-form', [
                    'categories' => $categories,
                    'priceRange' => $priceRange ?? null,
                    'formId' => 'searchFilterFormMobile',
                    'searchQuery' => $searchQuery ?? null
                ])
            </div>
        </div>
        <!-- SECTION END -->
    </main>

@endsection
