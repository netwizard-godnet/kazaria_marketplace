@php
    $formId = $formId ?? 'boutiqueFilterForm';
@endphp

<div class="category-filters {{ $wrapperClass ?? '' }}">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-filter"></i>
            <span class="fw-bold text-uppercase small">Filtres</span>
        </div>
        <a href="{{ route('boutique_officielle') }}" class="btn btn-light btn-sm filter-reset-btn" title="Réinitialiser les filtres">
            <i class="fa-solid fa-rotate-right"></i>
        </a>
    </div>

    <form method="GET" action="{{ route('boutique_officielle') }}" id="{{ $formId }}">
        @foreach(request()->only('sort') as $key => $value)
            @if(!is_array($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        @if(isset($categories) && count($categories))
            <div class="mb-4">
                <p class="filter-title">Catégories</p>
                @foreach($categories as $cat)
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="category_id"
                            value="{{ $cat->id }}" id="boutiqueCat{{ $formId }}{{ $cat->id }}"
                            {{ request('category_id') == $cat->id ? 'checked' : '' }}>
                        <label class="form-check-label filter-label" for="boutiqueCat{{ $formId }}{{ $cat->id }}">
                            @if($cat->icon)
                                <i class="{{ $cat->icon }} me-1"></i>
                            @endif
                            {{ $cat->name }}
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
        
        @if(isset($attributes) && $attributes->count() > 0)
            @foreach($attributes as $attribute)
                <div class="mb-4">
                    <p class="filter-title">{{ $attribute->name }}</p>
                    @if($attribute->attributeValues->count() > 5)
                        <div class="filter-search mb-2">
                            <input type="text" class="form-control form-control-sm" placeholder="Rechercher..." 
                                   id="attrSearch{{ $formId }}{{ $attribute->id }}" 
                                   onkeyup="filterOptions(this, 'attrOptions{{ $formId }}{{ $attribute->id }}')">
                        </div>
                    @endif
                    <div class="filter-options" id="attrOptions{{ $formId }}{{ $attribute->id }}" 
                         style="{{ $attribute->attributeValues->count() > 5 ? 'max-height: 200px; overflow-y: auto;' : '' }}">
                        @foreach($attribute->attributeValues as $value)
                            <div class="form-check mb-1 attr-option">
                                <input class="form-check-input" type="checkbox"
                                    name="attributes[{{ $attribute->id }}][]"
                                    value="{{ $value->id }}"
                                    id="boutiqueAttr{{ $formId }}{{ $value->id }}"
                                    {{ in_array($value->id, request('attributes.'.$attribute->id, [])) ? 'checked' : '' }}>
                                <label class="form-check-label filter-label" for="boutiqueAttr{{ $formId }}{{ $value->id }}">
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
            <hr>
        @endif
        
        <div class="mb-4">
            <p class="filter-title">Options</p>
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="on_sale" value="1"
                    id="boutiqueOnsale{{ $formId }}" {{ request('on_sale') == '1' ? 'checked' : '' }}>
                <label class="form-check-label filter-label" for="boutiqueOnsale{{ $formId }}">
                    <i class="fa-solid fa-tag text-danger me-1"></i>En promotion
                </label>
            </div>
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="is_new" value="1"
                    id="boutiqueNew{{ $formId }}" {{ request('is_new') == '1' ? 'checked' : '' }}>
                <label class="form-check-label filter-label" for="boutiqueNew{{ $formId }}">
                    <i class="fa-solid fa-sparkles text-primary me-1"></i>Nouveautés
                </label>
            </div>
        </div>
        <hr>

        <div class="mb-4">
            <p class="filter-title">Note minimum</p>
            @for($i = 5; $i >= 1; $i--)
                <div class="form-check mb-1">
                    <input class="form-check-input" type="radio" name="min_rating"
                        value="{{ $i }}" id="boutiqueRating{{ $formId }}{{ $i }}"
                        {{ request('min_rating') == $i ? 'checked' : '' }}>
                    <label class="form-check-label filter-label" for="boutiqueRating{{ $formId }}{{ $i }}">
                        @for($j = 1; $j <= $i; $j++)
                            <i class="fa-solid fa-star text-warning"></i>
                        @endfor
                        &nbsp;et plus
                    </label>
                </div>
            @endfor
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-light btn-sm text-uppercase fw-bold">
                <i class="bi bi-search me-1"></i>Appliquer
            </button>
            <a href="{{ route('boutique_officielle') }}" class="btn btn-outline-light btn-sm text-uppercase">
                Effacer les filtres
            </a>
        </div>
    </form>
</div>

