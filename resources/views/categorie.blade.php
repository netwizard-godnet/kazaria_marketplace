@extends('layouts.app')

@section('content')
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
                    <img src="{{ asset('images/bg-2.jpg') }}" class="w-100 h-200px object-fit-cover" alt="">
                </div>
                <div class="col-md-4">
                    <img src="{{ asset('images/bg-2.jpg') }}" class="w-100 h-200px object-fit-cover" alt="">
                </div>
                <div class="col-md-4">
                    <img src="{{ asset('images/bg-2.jpg') }}" class="w-100 h-200px object-fit-cover" alt="">
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
                            <span>
                                @if($category->icon)
                                <i class="{{ $category->icon }} me-2"></i>
                                @endif
                                Filtres
                            </span>
                            <a href="{{ route('categorie', $category->slug) }}" class="btn btn-sm btn-outline-light">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        </p>
                        
                        <form method="GET" action="{{ route('categorie', $category->slug) }}" id="filterForm">
                            
                            <!-- Sous-catégories -->
                            @if($category->subcategories->count() > 0)
                            <div class="mb-3">
                                <p class="fw-bold mb-2 fs-7">Sous-catégories</p>
                                @foreach($category->subcategories as $subcategory)
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="subcategory" value="{{ $subcategory->id }}" 
                                        id="subcat{{ $subcategory->id }}" {{ request('subcategory') == $subcategory->id ? 'checked' : '' }}>
                                    <label class="form-check-label fs-8" for="subcat{{ $subcategory->id }}">
                                        @if($subcategory->icon)
                                        <i class="{{ $subcategory->icon }} me-1"></i>
                                        @endif
                                        {{ $subcategory->name }}
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
                                            placeholder="Min" value="{{ request('min_price') }}" 
                                            min="0" max="{{ $priceRange->max_price }}">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="max_price" 
                                            placeholder="Max" value="{{ request('max_price') }}" 
                                            min="0" max="{{ $priceRange->max_price }}">
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
                                        id="rating{{ $i }}" {{ request('min_rating') == $i ? 'checked' : '' }}>
                                    <label class="form-check-label fs-8" for="rating{{ $i }}">
                                        @for($j = 1; $j <= $i; $j++)
                                            <i class="fa-solid fa-star text-warning"></i>
                                        @endfor
                                        & plus
                                    </label>
                                </div>
                                @endfor
                            </div>
                            <hr class="text-white">
                            
                            <!-- Attributs -->
                            @if(isset($attributes))
                                @foreach($attributes as $attribute)
                                <div class="mb-3">
                                    <p class="fw-bold mb-2 fs-7">{{ $attribute->name }}</p>
                                    @foreach($attribute->attributeValues->take(5) as $value)
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" 
                                            name="attributes[{{ $attribute->id }}][]" 
                                            value="{{ $value->id }}" 
                                            id="attr{{ $value->id }}"
                                            {{ in_array($value->id, request('attributes.'.$attribute->id, [])) ? 'checked' : '' }}>
                                        <label class="form-check-label fs-8" for="attr{{ $value->id }}">
                                            {{ $value->value }}
                                        </label>
                                    </div>
                                    @endforeach
                                    @if($attribute->attributeValues->count() > 5)
                                    <a href="#" class="text-white fs-8">Voir plus...</a>
                                    @endif
                                </div>
                                @if(!$loop->last)
                                <hr class="text-white">
                                @endif
                                @endforeach
                            @endif
                            
                        </form>
                    </div>
                </div>
                <div class="col-12 col-sm-9 col-md-10">
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
                            {{ $products->links() }}
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
                    <img src="{{ asset('images/bg-1.jpg') }}" class="w-100 h-300px object-fit-cover" alt="">
                </div>
                <div class="col-md-4">
                    <img src="{{ asset('images/bg-2.jpg') }}" class="w-100 h-300px object-fit-cover" alt="">
                </div>
            </div>
        </section>
        <!-- SECTION AFFICHES END -->
    </main>

@endsection
