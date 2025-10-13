@extends('layouts.app')

@section('content')
    <main class="container-fluid">
        <!-- SECTION BREADCRUMB -->
        <section class="bg-light py-2">
            <div class="container-fluid">
                <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb" class="">
                    <ol class="breadcrumb" class="">
                        <li class="breadcrumb-item mb-0"><a href="{{ route('accueil') }}" class="fs-7">Accueil</a></li>
                        <li class="breadcrumb-item mb-0 active fs-7" aria-current="page">Recherche</li>
                    </ol>
                </nav>
            </div>
        </section>
        <!-- SECTION BREADCRUMB END -->

        <!-- SECTION -->
        <section class="py-3">
            <div class="row g-3">
                <div class="col-12 col-sm-3 col-md-2" style="position: sticky; top: 0;">
                    <div class="blue-bg rounded-2 p-3 text-white">
                        <p class="mb-3 fw-bold d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-filter me-2"></i>Filtres</span>
                            <a href="{{ route('search_product') }}" class="btn btn-sm btn-outline-light">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        </p>
                        
                        <form method="GET" action="{{ route('search_product') }}" id="searchFilterForm">
                            <input type="hidden" name="q" value="{{ $searchQuery ?? '' }}">
                            
                            <!-- Catégories -->
                            <div class="mb-3">
                                <p class="fw-bold mb-2 fs-7">Catégories</p>
                                @foreach($categories as $cat)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="category_id" 
                                        value="{{ $cat->id }}" id="cat{{ $cat->id }}"
                                        {{ request('category_id') == $cat->id ? 'checked' : '' }}>
                                    <label class="form-check-label fs-8" for="cat{{ $cat->id }}">
                                        @if($cat->icon)
                                        <i class="{{ $cat->icon }} me-1"></i>
                                        @endif
                                        {{ $cat->name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <hr class="text-white">
                            
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
                                        id="searchRating{{ $i }}" {{ request('min_rating') == $i ? 'checked' : '' }}>
                                    <label class="form-check-label fs-8" for="searchRating{{ $i }}">
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
                    <div id="searchResults">
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
                            {{ $products->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        <!-- SECTION END -->
    </main>

@endsection
