@php
    $variationId = $variation->id ?? null;
    $isExisting = isset($variation->id);
    $variationAttributes = $isExisting ? $variation->attributeValues->pluck('id')->toArray() : [];
    $variationPrice = $variation->old_price ?? $variation->price ?? 0;
    $variationPromoPrice = ($variation->old_price && $variation->old_price > $variation->price) ? $variation->price : null;
@endphp

<div class="card mb-3 variation-row" data-index="{{ $index }}" data-variation-id="{{ $variationId }}">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Variation #{{ $index }}</h6>
        <button type="button" class="btn btn-sm btn-danger remove-variation" onclick="removeVariationRow({{ $index }}, {{ $variationId ?? 'null' }})">
            <i class="fas fa-times"></i> Supprimer
        </button>
    </div>
    <div class="card-body">
        @if($isExisting)
            <input type="hidden" name="variations[{{ $index }}][id]" value="{{ $variationId }}">
        @endif
        
        <div class="row mb-3">
            @foreach($attributes as $attribute)
            <div class="col-md-6 mb-2">
                <label class="form-label small">{{ $attribute->name }}</label>
                <select class="form-control form-control-sm variation-attribute" 
                        name="variations[{{ $index }}][attributes][{{ $attribute->id }}]">
                    <option value="">-- Sélectionner --</option>
                    @foreach($attribute->attributeValues as $value)
                        <option value="{{ $value->id }}" 
                                {{ in_array($value->id, $variationAttributes) ? 'selected' : '' }}>
                            {{ $value->value }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endforeach
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                <input type="number" class="form-control variation-price" 
                       name="variations[{{ $index }}][price]" 
                       value="{{ old("variations.{$index}.price", $variationPrice) }}"
                       step="0.01" min="0" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Prix promo (FCFA)</label>
                <input type="number" class="form-control variation-promo-price" 
                       name="variations[{{ $index }}][promo_price]" 
                       value="{{ old("variations.{$index}.promo_price", $variationPromoPrice) }}"
                       step="0.01" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Stock <span class="text-danger">*</span></label>
                <input type="number" class="form-control variation-stock" 
                       name="variations[{{ $index }}][stock]" 
                       value="{{ old("variations.{$index}.stock", $variation->stock ?? 0) }}"
                       min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">SKU</label>
                <input type="text" class="form-control variation-sku" 
                       name="variations[{{ $index }}][sku]"
                       value="{{ old("variations.{$index}.sku", $variation->sku ?? '') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Par défaut</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input variation-default" 
                           type="checkbox" 
                           name="variations[{{ $index }}][is_default]" 
                           value="1"
                           {{ old("variations.{$index}.is_default", $variation->is_default ?? false) ? 'checked' : '' }}>
                </div>
            </div>
        </div>
    </div>
</div>

