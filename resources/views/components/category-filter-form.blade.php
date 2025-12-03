@php
    $prefix = $inputPrefix ?? '';
    $formId = $formId ?? 'filterForm';
@endphp

<div class="category-filters {{ $wrapperClass ?? '' }}">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            @if($category->image && !empty($category->image))
                <img src="{{ str_starts_with($category->image, 'http') ? $category->image : (str_starts_with($category->image, 'images/') ? asset($category->image) : Storage::url($category->image)) }}"
                    alt="{{ $category->name }}" class="filter-icon">
            @endif
            <span class="fw-bold text-uppercase small">Filtres</span>
        </div>
        <a href="{{ route('categorie', $category->slug) }}" class="btn btn-light btn-sm filter-reset-btn" title="Réinitialiser les filtres">
            <i class="fa-solid fa-rotate-right"></i>
        </a>
    </div>

    <form method="GET" action="{{ route('categorie', $category->slug) }}" id="{{ $formId }}" class="category-filters__form">
        @foreach(request()->only('sort', 'order') as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        @if($category->subcategories->count() > 0)
            <div class="mb-4">
                <p class="filter-title">Sous-catégories</p>
                @foreach($category->subcategories as $subcategory)
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="subcategory"
                            value="{{ $subcategory->id }}"
                            id="{{ $prefix }}subcat{{ $subcategory->id }}"
                            {{ request('subcategory') == $subcategory->id ? 'checked' : '' }}>
                        <label class="form-check-label filter-label" for="{{ $prefix }}subcat{{ $subcategory->id }}">
                            @if($subcategory->image && !empty($subcategory->image))
                                <img src="{{ str_starts_with($subcategory->image, 'http') ? $subcategory->image : (str_starts_with($subcategory->image, 'images/') ? asset($subcategory->image) : Storage::url($subcategory->image)) }}"
                                    alt="{{ $subcategory->name }}" class="filter-icon me-1">
                            @endif
                            {{ $subcategory->name }}
                        </label>
                    </div>
                @endforeach
            </div>
            <hr>
        @endif

        @if(isset($priceRange) && $priceRange->min_price && $priceRange->max_price)
            <div class="mb-4">
                <p class="filter-title">Prix (FCFA)</p>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm price-input" name="min_price"
                            placeholder="Min" value="{{ request('min_price') }}"
                            min="{{ $priceRange->min_price }}" max="{{ $priceRange->max_price }}"
                            data-min="{{ $priceRange->min_price }}" data-max="{{ $priceRange->max_price }}">
                    </div>
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm price-input" name="max_price"
                            placeholder="Max" value="{{ request('max_price') }}"
                            min="{{ $priceRange->min_price }}" max="{{ $priceRange->max_price }}"
                            data-min="{{ $priceRange->min_price }}" data-max="{{ $priceRange->max_price }}">
                    </div>
                </div>
                <div class="price-range-display small text-muted">
                    <span id="priceMinDisplay">{{ number_format($priceRange->min_price, 0, ',', ' ') }}</span> - 
                    <span id="priceMaxDisplay">{{ number_format($priceRange->max_price, 0, ',', ' ') }}</span> FCFA
                </div>
            </div>
            <hr>
        @endif
        
        @if(isset($availableBrands) && $availableBrands->count() > 0)
            <div class="mb-4">
                <p class="filter-title">Marques</p>
                <div class="filter-search mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Rechercher une marque..." 
                           id="brandSearch{{ $prefix }}" onkeyup="filterOptions(this, 'brandOptions{{ $prefix }}')">
                </div>
                <div class="filter-options" id="brandOptions{{ $prefix }}" style="max-height: 200px; overflow-y: auto;">
                    @foreach($availableBrands as $brand)
                        <div class="form-check mb-1 brand-option">
                            <input class="form-check-input" type="checkbox" name="brand[]"
                                value="{{ $brand }}" id="{{ $prefix }}brand{{ $loop->index }}"
                                {{ in_array($brand, request('brand', [])) ? 'checked' : '' }}>
                            <label class="form-check-label filter-label" for="{{ $prefix }}brand{{ $loop->index }}">
                                {{ $brand }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <hr>
        @endif
        
        @if(isset($availableStores) && $availableStores->count() > 0)
            <div class="mb-4">
                <p class="filter-title">Boutiques</p>
                <div class="filter-search mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Rechercher une boutique..." 
                           id="storeSearch{{ $prefix }}" onkeyup="filterOptions(this, 'storeOptions{{ $prefix }}')">
                </div>
                <div class="filter-options" id="storeOptions{{ $prefix }}" style="max-height: 200px; overflow-y: auto;">
                    @foreach($availableStores as $store)
                        <div class="form-check mb-1 store-option">
                            <input class="form-check-input" type="checkbox" name="store_id[]"
                                value="{{ $store->id }}" id="{{ $prefix }}store{{ $store->id }}"
                                {{ in_array($store->id, request('store_id', [])) ? 'checked' : '' }}>
                            <label class="form-check-label filter-label" for="{{ $prefix }}store{{ $store->id }}">
                                {{ $store->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <hr>
        @endif
        
        <div class="mb-4">
            <p class="filter-title">Disponibilité</p>
            <div class="form-check mb-1">
                <input class="form-check-input" type="radio" name="in_stock" value="1"
                    id="{{ $prefix }}stock1" {{ request('in_stock') == '1' ? 'checked' : '' }}>
                <label class="form-check-label filter-label" for="{{ $prefix }}stock1">
                    <i class="fa-solid fa-check-circle text-success me-1"></i>En stock
                </label>
            </div>
            <div class="form-check mb-1">
                <input class="form-check-input" type="radio" name="in_stock" value="0"
                    id="{{ $prefix }}stock0" {{ request('in_stock') == '0' ? 'checked' : '' }}>
                <label class="form-check-label filter-label" for="{{ $prefix }}stock0">
                    <i class="fa-solid fa-times-circle text-danger me-1"></i>Rupture de stock
                </label>
            </div>
        </div>
        <hr>
        
        <div class="mb-4">
            <p class="filter-title">Options</p>
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="on_sale" value="1"
                    id="{{ $prefix }}onsale" {{ request('on_sale') == '1' ? 'checked' : '' }}>
                <label class="form-check-label filter-label" for="{{ $prefix }}onsale">
                    <i class="fa-solid fa-tag text-danger me-1"></i>En promotion
                </label>
            </div>
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="is_new" value="1"
                    id="{{ $prefix }}new" {{ request('is_new') == '1' ? 'checked' : '' }}>
                <label class="form-check-label filter-label" for="{{ $prefix }}new">
                    <i class="fa-solid fa-sparkles text-primary me-1"></i>Nouveautés
                </label>
            </div>
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="is_trending" value="1"
                    id="{{ $prefix }}trending" {{ request('is_trending') == '1' ? 'checked' : '' }}>
                <label class="form-check-label filter-label" for="{{ $prefix }}trending">
                    <i class="fa-solid fa-fire text-warning me-1"></i>Tendance
                </label>
            </div>
        </div>
        <hr>

        <div class="mb-4">
            <p class="filter-title">Note minimum</p>
            @for($i = 5; $i >= 1; $i--)
                <div class="form-check mb-1">
                    <input class="form-check-input" type="radio" name="min_rating"
                        value="{{ $i }}"
                        id="{{ $prefix }}rating{{ $i }}"
                        {{ request('min_rating') == $i ? 'checked' : '' }}>
                    <label class="form-check-label filter-label" for="{{ $prefix }}rating{{ $i }}">
                        @for($j = 1; $j <= $i; $j++)
                            <i class="fa-solid fa-star text-warning"></i>
                        @endfor
                        &nbsp;et plus
                    </label>
                </div>
            @endfor
        </div>

        @if(isset($attributes) && $attributes->count() > 0)
            @foreach($attributes as $attribute)
                <div class="mb-4">
                    <p class="filter-title">{{ $attribute->name }}</p>
                    @if($attribute->attributeValues->count() > 5)
                        <div class="filter-search mb-2">
                            <input type="text" class="form-control form-control-sm" placeholder="Rechercher..." 
                                   id="attrSearch{{ $prefix }}{{ $attribute->id }}" 
                                   onkeyup="filterOptions(this, 'attrOptions{{ $prefix }}{{ $attribute->id }}')">
                        </div>
                    @endif
                    <div class="filter-options" id="attrOptions{{ $prefix }}{{ $attribute->id }}" 
                         style="{{ $attribute->attributeValues->count() > 5 ? 'max-height: 200px; overflow-y: auto;' : '' }}">
                        @foreach($attribute->attributeValues as $value)
                            <div class="form-check mb-1 attr-option">
                                <input class="form-check-input" type="checkbox"
                                    name="attributes[{{ $attribute->id }}][]"
                                    value="{{ $value->id }}"
                                    id="{{ $prefix }}attr{{ $value->id }}"
                                    {{ in_array($value->id, request('attributes.'.$attribute->id, [])) ? 'checked' : '' }}>
                                <label class="form-check-label filter-label" for="{{ $prefix }}attr{{ $value->id }}">
                                    {{ $value->value }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                @if(!$loop->last)
                    <hr>
                @endif
            @endforeach
        @endif

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-light btn-sm text-uppercase fw-bold">
                <i class="bi bi-search me-1"></i>Appliquer
            </button>
            <a href="{{ route('categorie', $category->slug) }}" class="btn btn-outline-light btn-sm text-uppercase">
                Effacer les filtres
            </a>
        </div>
    </form>
</div>

