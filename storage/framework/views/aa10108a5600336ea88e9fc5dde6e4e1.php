<?php
    $prefix = $inputPrefix ?? '';
    $formId = $formId ?? 'filterForm';
?>

<div class="category-filters <?php echo e($wrapperClass ?? ''); ?>">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <?php if($category->image && !empty($category->image)): ?>
                <img src="<?php echo e(str_starts_with($category->image, 'http') ? $category->image : (str_starts_with($category->image, 'images/') ? asset($category->image) : Storage::url($category->image))); ?>"
                    alt="<?php echo e($category->name); ?>" class="filter-icon">
            <?php endif; ?>
            <span class="fw-bold text-uppercase small">Filtres</span>
        </div>
        <a href="<?php echo e(route('categorie', $category->slug)); ?>" class="btn btn-light btn-sm filter-reset-btn" title="Réinitialiser les filtres">
            <i class="fa-solid fa-rotate-right"></i>
        </a>
    </div>

    <form method="GET" action="<?php echo e(route('categorie', $category->slug)); ?>" id="<?php echo e($formId); ?>" class="category-filters__form">
        <?php $__currentLoopData = request()->only('sort', 'order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($category->subcategories->count() > 0): ?>
            <div class="mb-4">
                <p class="filter-title">Sous-catégories</p>
                <?php $__currentLoopData = $category->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="form-check mb-1">
                        <input class="form-check-input" type="radio" name="subcategory"
                            value="<?php echo e($subcategory->id); ?>"
                            id="<?php echo e($prefix); ?>subcat<?php echo e($subcategory->id); ?>"
                            <?php echo e(request('subcategory') == $subcategory->id ? 'checked' : ''); ?>>
                        <label class="form-check-label filter-label" for="<?php echo e($prefix); ?>subcat<?php echo e($subcategory->id); ?>">
                            <?php if($subcategory->image && !empty($subcategory->image)): ?>
                                <img src="<?php echo e(str_starts_with($subcategory->image, 'http') ? $subcategory->image : (str_starts_with($subcategory->image, 'images/') ? asset($subcategory->image) : Storage::url($subcategory->image))); ?>"
                                    alt="<?php echo e($subcategory->name); ?>" class="filter-icon me-1">
                            <?php endif; ?>
                            <?php echo e($subcategory->name); ?>

                        </label>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <hr>
        <?php endif; ?>

        <?php if(isset($priceRange)): ?>
            <div class="mb-4">
                <p class="filter-title">Prix (FCFA)</p>
                <div class="row g-2">
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm" name="min_price"
                            placeholder="Min" value="<?php echo e(request('min_price')); ?>"
                            min="0" max="<?php echo e($priceRange->max_price); ?>">
                    </div>
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm" name="max_price"
                            placeholder="Max" value="<?php echo e(request('max_price')); ?>"
                            min="0" max="<?php echo e($priceRange->max_price); ?>">
                    </div>
                </div>
            </div>
            <hr>
        <?php endif; ?>

        <div class="mb-4">
            <p class="filter-title">Note minimum</p>
            <?php for($i = 5; $i >= 1; $i--): ?>
                <div class="form-check mb-1">
                    <input class="form-check-input" type="radio" name="min_rating"
                        value="<?php echo e($i); ?>"
                        id="<?php echo e($prefix); ?>rating<?php echo e($i); ?>"
                        <?php echo e(request('min_rating') == $i ? 'checked' : ''); ?>>
                    <label class="form-check-label filter-label" for="<?php echo e($prefix); ?>rating<?php echo e($i); ?>">
                        <?php for($j = 1; $j <= $i; $j++): ?>
                            <i class="fa-solid fa-star text-warning"></i>
                        <?php endfor; ?>
                        &nbsp;et plus
                    </label>
                </div>
            <?php endfor; ?>
        </div>

        <?php if(isset($attributes)): ?>
            <hr>
            <?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="mb-4">
                    <p class="filter-title"><?php echo e($attribute->name); ?></p>
                    <?php $__currentLoopData = $attribute->attributeValues->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox"
                                name="attributes[<?php echo e($attribute->id); ?>][]"
                                value="<?php echo e($value->id); ?>"
                                id="<?php echo e($prefix); ?>attr<?php echo e($value->id); ?>"
                                <?php echo e(in_array($value->id, request('attributes.'.$attribute->id, [])) ? 'checked' : ''); ?>>
                            <label class="form-check-label filter-label" for="<?php echo e($prefix); ?>attr<?php echo e($value->id); ?>">
                                <?php echo e($value->value); ?>

                            </label>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php if(!$loop->last): ?>
                    <hr>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-light btn-sm text-uppercase fw-bold">
                <i class="bi bi-search me-1"></i>Appliquer
            </button>
            <a href="<?php echo e(route('categorie', $category->slug)); ?>" class="btn btn-outline-light btn-sm text-uppercase">
                Effacer les filtres
            </a>
        </div>
    </form>
</div>

<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/components/category-filter-form.blade.php ENDPATH**/ ?>