<?php
    $formId = $formId ?? 'boutiqueFilterForm';
?>

<style>
.category-filters .form-check-input {
    width: 1.25em;
    height: 1.25em;
    margin-top: 0.25em;
    vertical-align: top;
    background-color: #fff;
    border: 2px solid #dee2e6;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}

.category-filters .form-check-input:checked {
    background-color: #ff8c00;
    border-color: #ff8c00;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: center;
    background-size: 100% 100%;
}

.category-filters .form-check-input[type="radio"] {
    border-radius: 50%;
}

.category-filters .form-check-input[type="radio"]:checked {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='2' fill='%23fff'/%3e%3c/svg%3e");
}

.category-filters .form-check-input:focus {
    border-color: #ff8c00;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(255, 140, 0, 0.25);
}

.category-filters .form-check-label {
    cursor: pointer;
    margin-left: 0.5em;
}

.category-filters .filter-icon {
    width: 20px;
    height: 20px;
    object-fit: contain;
}
</style>

<div class="category-filters <?php echo e($wrapperClass ?? ''); ?>">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-filter"></i>
            <span class="fw-bold text-uppercase small">Filtres</span>
        </div>
        <a href="<?php echo e(route('boutique_officielle')); ?>" class="btn btn-light btn-sm filter-reset-btn" title="Réinitialiser les filtres">
            <i class="fa-solid fa-rotate-right"></i>
        </a>
    </div>

    <form method="GET" action="<?php echo e(route('boutique_officielle')); ?>" id="<?php echo e($formId); ?>">
        <?php $__currentLoopData = request()->only('sort'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(!is_array($value)): ?>
                <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if(isset($categories) && count($categories)): ?>
            <div class="mb-4">
                <p class="filter-title">Catégories</p>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="category_id"
                            value="<?php echo e($cat->id); ?>" id="boutiqueCat<?php echo e($formId); ?><?php echo e($cat->id); ?>"
                            <?php echo e(request('category_id') == $cat->id ? 'checked' : ''); ?>>
                        <label class="form-check-label filter-label" for="boutiqueCat<?php echo e($formId); ?><?php echo e($cat->id); ?>">
                            <?php if($cat->image && !empty($cat->image)): ?>
                                <img src="<?php echo e(str_starts_with($cat->image, 'http') ? $cat->image : (str_starts_with($cat->image, 'images/') ? asset($cat->image) : Storage::url($cat->image))); ?>"
                                    alt="<?php echo e($cat->name); ?>" class="filter-icon me-1">
                            <?php elseif($cat->icon && !empty($cat->icon)): ?>
                                <i class="<?php echo e($cat->icon); ?> me-1"></i>
                            <?php endif; ?>
                            <?php echo e($cat->name); ?>

                        </label>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <hr>
        <?php endif; ?>

        <?php if(isset($priceRange) && $priceRange->min_price && $priceRange->max_price): ?>
            <div class="mb-4">
                <p class="filter-title">Prix (FCFA)</p>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm price-input" name="min_price"
                            placeholder="Min" value="<?php echo e(request('min_price')); ?>"
                            min="<?php echo e($priceRange->min_price); ?>" max="<?php echo e($priceRange->max_price); ?>"
                            data-min="<?php echo e($priceRange->min_price); ?>" data-max="<?php echo e($priceRange->max_price); ?>">
                    </div>
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm price-input" name="max_price"
                            placeholder="Max" value="<?php echo e(request('max_price')); ?>"
                            min="<?php echo e($priceRange->min_price); ?>" max="<?php echo e($priceRange->max_price); ?>"
                            data-min="<?php echo e($priceRange->min_price); ?>" data-max="<?php echo e($priceRange->max_price); ?>">
                    </div>
                </div>
                <div class="price-range-display small text-muted">
                    <span id="priceMinDisplay"><?php echo e(number_format($priceRange->min_price, 0, ',', ' ')); ?></span> - 
                    <span id="priceMaxDisplay"><?php echo e(number_format($priceRange->max_price, 0, ',', ' ')); ?></span> FCFA
                </div>
            </div>
            <hr>
        <?php endif; ?>
        
        <?php if(isset($attributes) && $attributes->count() > 0): ?>
            <?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="mb-4">
                    <p class="filter-title"><?php echo e($attribute->name); ?></p>
                    <?php if($attribute->attributeValues->count() > 5): ?>
                        <div class="filter-search mb-2">
                            <input type="text" class="form-control form-control-sm" placeholder="Rechercher..." 
                                   id="attrSearch<?php echo e($formId); ?><?php echo e($attribute->id); ?>" 
                                   onkeyup="filterOptions(this, 'attrOptions<?php echo e($formId); ?><?php echo e($attribute->id); ?>')">
                        </div>
                    <?php endif; ?>
                    <div class="filter-options" id="attrOptions<?php echo e($formId); ?><?php echo e($attribute->id); ?>" 
                         style="<?php echo e($attribute->attributeValues->count() > 5 ? 'max-height: 200px; overflow-y: auto;' : ''); ?>">
                        <?php $__currentLoopData = $attribute->attributeValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="form-check mb-1 attr-option">
                                <input class="form-check-input" type="checkbox"
                                    name="attributes[<?php echo e($attribute->id); ?>][]"
                                    value="<?php echo e($value->id); ?>"
                                    id="boutiqueAttr<?php echo e($formId); ?><?php echo e($value->id); ?>"
                                    <?php echo e(in_array($value->id, request('attributes.'.$attribute->id, [])) ? 'checked' : ''); ?>>
                                <label class="form-check-label filter-label" for="boutiqueAttr<?php echo e($formId); ?><?php echo e($value->id); ?>">
                                    <?php echo e($value->value); ?>

                                </label>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php if(!$loop->last): ?>
                    <hr>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <hr>
        <?php endif; ?>
        
        <div class="mb-4">
            <p class="filter-title">Options</p>
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="on_sale" value="1"
                    id="boutiqueOnsale<?php echo e($formId); ?>" <?php echo e(request('on_sale') == '1' ? 'checked' : ''); ?>>
                <label class="form-check-label filter-label" for="boutiqueOnsale<?php echo e($formId); ?>">
                    <i class="fa-solid fa-tag text-danger me-1"></i>En promotion
                </label>
            </div>
            <div class="form-check mb-1">
                <input class="form-check-input" type="checkbox" name="is_new" value="1"
                    id="boutiqueNew<?php echo e($formId); ?>" <?php echo e(request('is_new') == '1' ? 'checked' : ''); ?>>
                <label class="form-check-label filter-label" for="boutiqueNew<?php echo e($formId); ?>">
                    <i class="fa-solid fa-sparkles text-primary me-1"></i>Nouveautés
                </label>
            </div>
        </div>
        <hr>

        <div class="mb-4">
            <p class="filter-title">Note minimum</p>
            <?php for($i = 5; $i >= 1; $i--): ?>
                <div class="form-check mb-1">
                    <input class="form-check-input" type="radio" name="min_rating"
                        value="<?php echo e($i); ?>" id="boutiqueRating<?php echo e($formId); ?><?php echo e($i); ?>"
                        <?php echo e(request('min_rating') == $i ? 'checked' : ''); ?>>
                    <label class="form-check-label filter-label" for="boutiqueRating<?php echo e($formId); ?><?php echo e($i); ?>">
                        <?php for($j = 1; $j <= $i; $j++): ?>
                            <i class="fa-solid fa-star text-warning"></i>
                        <?php endfor; ?>
                        &nbsp;et plus
                    </label>
                </div>
            <?php endfor; ?>
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-light btn-sm text-uppercase fw-bold">
                <i class="bi bi-search me-1"></i>Appliquer
            </button>
            <a href="<?php echo e(route('boutique_officielle')); ?>" class="btn btn-outline-light btn-sm text-uppercase">
                Effacer les filtres
            </a>
        </div>
    </form>
</div>

<?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/components/boutique-filter-form.blade.php ENDPATH**/ ?>