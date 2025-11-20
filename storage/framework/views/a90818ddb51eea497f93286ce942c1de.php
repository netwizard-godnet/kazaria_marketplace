<?php
    $formId = $formId ?? 'boutiqueFilterForm';
?>

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
                            <?php if($cat->icon): ?>
                                <i class="<?php echo e($cat->icon); ?> me-1"></i>
                            <?php endif; ?>
                            <?php echo e($cat->name); ?>

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
                            placeholder="Min" value="<?php echo e(request('min_price')); ?>">
                    </div>
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm" name="max_price"
                            placeholder="Max" value="<?php echo e(request('max_price')); ?>">
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

<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/components/boutique-filter-form.blade.php ENDPATH**/ ?>