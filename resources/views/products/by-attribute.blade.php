@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar des filtres -->
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ request()->url() }}">
                        <!-- Recherche -->
                        <div class="mb-3">
                            <label for="q" class="form-label">Recherche</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="q" 
                                   name="q" 
                                   value="{{ request('q') }}" 
                                   placeholder="Rechercher un produit...">
                        </div>

                        <!-- Filtre par catégorie -->
                        <div class="mb-3">
                            <label class="form-label">Catégorie</label>
                            <select name="category" class="form-select">
                                <option value="">Toutes les catégories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->slug }}" 
                                            {{ request('category') === $category->slug ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filtre par prix -->
                        <div class="mb-3">
                            <label class="form-label">Prix</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" 
                                           class="form-control" 
                                           name="min_price" 
                                           value="{{ request('min_price') }}" 
                                           placeholder="Min">
                                </div>
                                <div class="col-6">
                                    <input type="number" 
                                           class="form-control" 
                                           name="max_price" 
                                           value="{{ request('max_price') }}" 
                                           placeholder="Max">
                                </div>
                            </div>
                            @if($priceRange)
                                <small class="text-muted">
                                    Prix: {{ number_format($priceRange->min_price) }} - {{ number_format($priceRange->max_price) }} FCFA
                                </small>
                            @endif
                        </div>

                        <!-- Filtres par attribut -->
                        @if($attributeValues->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">{{ $attribute->name }}</label>
                            <div class="list-group list-group-flush">
                                <a href="{{ route('products.by-attribute', $attribute->slug) }}" 
                                   class="list-group-item list-group-item-action {{ !request('value') ? 'active' : '' }}">
                                    Tous les {{ strtolower($attribute->name) }}s
                                </a>
                                @foreach($attributeValues as $value)
                                <a href="{{ route('products.by-attribute-value', [$attribute->slug, $value->slug]) }}" 
                                   class="list-group-item list-group-item-action {{ request('value') === $value->slug ? 'active' : '' }}">
                                    {{ $value->value }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Autres attributs -->
                        @foreach($otherAttributes as $otherAttribute)
                        @if($otherAttribute->attributeValues->count() > 0)
                        <div class="mb-3">
                            <label class="form-label">{{ $otherAttribute->name }}</label>
                            <div class="list-group list-group-flush">
                                @foreach($otherAttribute->attributeValues->take(5) as $value)
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="attributes[{{ $otherAttribute->slug }}][]" 
                                           value="{{ $value->slug }}" 
                                           id="{{ $otherAttribute->slug }}_{{ $value->slug }}"
                                           {{ in_array($value->slug, request('attributes.'.$otherAttribute->slug, [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="{{ $otherAttribute->slug }}_{{ $value->slug }}">
                                        {{ $value->value }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @endforeach

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Appliquer les filtres
                            </button>
                            <a href="{{ request()->url() }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="col-lg-9">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">{{ $pageTitle }}</h2>
                    <p class="text-muted mb-0">
                        {{ $products->total() }} produit(s) trouvé(s)
                    </p>
                </div>
                
                <!-- Tri -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-sort me-2"></i>
                        @switch(request('sort', 'name'))
                            @case('price_asc') Prix croissant @break
                            @case('price_desc') Prix décroissant @break
                            @case('newest') Plus récents @break
                            @case('popular') Plus populaires @break
                            @default Nom A-Z @break
                        @endswitch
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'name']) }}">Nom A-Z</a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}">Prix croissant</a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}">Prix décroissant</a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}">Plus récents</a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}">Plus populaires</a></li>
                    </ul>
                </div>
            </div>

            <!-- Produits -->
            @if($products->count() > 0)
                <div class="row g-4">
                    @foreach($products as $product)
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 product-card">
                            <div class="position-relative">
                                @if($product->image)
                                <img src="{{ str_starts_with($product->image, 'http') ? $product->image : asset('storage/' . $product->image) }}" 
                                     class="card-img-top" 
                                     alt="{{ $product->name }}"
                                     style="height: 200px; object-fit: cover;">
                                @else
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                                @endif
                                
                                @if($product->is_new)
                                <span class="badge bg-success position-absolute top-0 start-0 m-2">Nouveau</span>
                                @endif
                                
                                @if($product->old_price && $product->old_price > $product->price)
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                    -{{ round((($product->old_price - $product->price) / $product->old_price) * 100) }}%
                                </span>
                                @endif
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title">
                                    <a href="{{ route('product-page', $product->slug) }}" class="text-decoration-none">
                                        {{ Str::limit($product->name, 50) }}
                                    </a>
                                </h6>
                                
                                <div class="mb-2">
                                    @if($product->old_price && $product->old_price > $product->price)
                                    <span class="text-muted text-decoration-line-through me-2">
                                        {{ number_format($product->old_price) }} FCFA
                                    </span>
                                    @endif
                                    <span class="h5 text-primary mb-0">
                                        {{ number_format($product->price) }} FCFA
                                    </span>
                                </div>
                                
                                <!-- Attributs du produit -->
                                @if($product->attributeValues->count() > 0)
                                <div class="mb-2">
                                    @foreach($product->attributeValues->take(3) as $attrValue)
                                    <span class="badge bg-light text-dark me-1 mb-1">
                                        {{ $attrValue->attribute->name }}: {{ $attrValue->value }}
                                    </span>
                                    @endforeach
                                    @if($product->attributeValues->count() > 3)
                                    <span class="badge bg-secondary">+{{ $product->attributeValues->count() - 3 }} autres</span>
                                    @endif
                                </div>
                                @endif
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            @if($product->rating > 0)
                                            <div class="me-2">
                                                <i class="fas fa-star text-warning"></i>
                                                <small>{{ number_format($product->rating, 1) }}</small>
                                            </div>
                                            @endif
                                            <small class="text-muted">{{ $product->views_count }} vues</small>
                                        </div>
                                        
                                        <a href="{{ route('product-page', $product->slug) }}" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $products->links('pagination.custom') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucun produit trouvé</h4>
                    <p class="text-muted">Essayez de modifier vos critères de recherche.</p>
                    <a href="{{ route('boutique_officielle') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la boutique
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
