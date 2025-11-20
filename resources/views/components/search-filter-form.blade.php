@php
    $formId = $formId ?? 'searchFilterForm';
    $searchQuery = $searchQuery ?? request('q');
@endphp

<div class="category-filters {{ $wrapperClass ?? '' }}">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-filter"></i>
            <span class="fw-bold text-uppercase small">Filtres</span>
        </div>
        <a href="{{ route('search_product', ['q' => $searchQuery]) }}" class="btn btn-light btn-sm filter-reset-btn" title="Réinitialiser les filtres">
            <i class="fa-solid fa-rotate-right"></i>
        </a>
    </div>

    <form method="GET" action="{{ route('search_product') }}" id="{{ $formId }}">
        @if($searchQuery)
            <input type="hidden" name="q" value="{{ $searchQuery }}">
        @endif
        @foreach(request()->only('sort') as $key => $value)
            @if(!is_array($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <div class="mb-4">
            <p class="filter-title">Catégories</p>
            @foreach($categories as $cat)
                <div class="form-check mb-1">
                    <input class="form-check-input" type="radio" name="category_id"
                        value="{{ $cat->id }}" id="searchCat{{ $formId }}{{ $cat->id }}"
                        {{ request('category_id') == $cat->id ? 'checked' : '' }}>
                    <label class="form-check-label filter-label" for="searchCat{{ $formId }}{{ $cat->id }}">
                        @if($cat->icon)
                            <i class="{{ $cat->icon }} me-1"></i>
                        @endif
                        {{ $cat->name }}
                    </label>
                </div>
            @endforeach
        </div>
        <hr>

        @if(isset($priceRange))
            <div class="mb-4">
                <p class="filter-title">Prix (FCFA)</p>
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
            </div>
            <hr>
        @endif

        <div class="mb-4">
            <p class="filter-title">Note minimum</p>
            @for($i = 5; $i >= 1; $i--)
                <div class="form-check mb-1">
                    <input class="form-check-input" type="radio" name="min_rating"
                        value="{{ $i }}" id="searchRating{{ $formId }}{{ $i }}"
                        {{ request('min_rating') == $i ? 'checked' : '' }}>
                    <label class="form-check-label filter-label" for="searchRating{{ $formId }}{{ $i }}">
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
            <a href="{{ route('search_product', ['q' => $searchQuery]) }}" class="btn btn-outline-light btn-sm text-uppercase">
                Effacer les filtres
            </a>
        </div>
    </form>
</div>

