<?php
    $variationId = $variation->id ?? null;
    $isExisting = isset($variation->id);
    $variationAttributes = $isExisting ? $variation->attributeValues->pluck('id')->toArray() : [];
    $variationPrice = $variation->old_price ?? $variation->price ?? 0;
    $variationPromoPrice = ($variation->old_price && $variation->old_price > $variation->price) ? $variation->price : null;
?>

<div class="card mb-3 variation-row" data-index="<?php echo e($index); ?>" data-variation-id="<?php echo e($variationId); ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Variation #<?php echo e($index); ?></h6>
        <button type="button" class="btn btn-sm btn-danger remove-variation" onclick="removeVariationRow(<?php echo e($index); ?>, <?php echo e($variationId ?? 'null'); ?>)">
            <i class="fas fa-times"></i> Supprimer
        </button>
    </div>
    <div class="card-body">
        <?php if($isExisting): ?>
            <input type="hidden" name="variations[<?php echo e($index); ?>][id]" value="<?php echo e($variationId); ?>">
        <?php endif; ?>
        
        <div class="row mb-3">
            <?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-6 mb-2">
                <label class="form-label small"><?php echo e($attribute->name); ?></label>
                <select class="form-control form-control-sm variation-attribute" 
                        name="variations[<?php echo e($index); ?>][attributes][<?php echo e($attribute->id); ?>]">
                    <option value="">-- Sélectionner --</option>
                    <?php $__currentLoopData = $attribute->attributeValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($value->id); ?>" 
                                <?php echo e(in_array($value->id, $variationAttributes) ? 'selected' : ''); ?>>
                            <?php echo e($value->value); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Prix (FCFA) <span class="text-danger">*</span></label>
                <input type="number" class="form-control variation-price" 
                       name="variations[<?php echo e($index); ?>][price]" 
                       value="<?php echo e(old("variations.{$index}.price", $variationPrice)); ?>"
                       step="0.01" min="0" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Prix promo (FCFA)</label>
                <input type="number" class="form-control variation-promo-price" 
                       name="variations[<?php echo e($index); ?>][promo_price]" 
                       value="<?php echo e(old("variations.{$index}.promo_price", $variationPromoPrice)); ?>"
                       step="0.01" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Stock <span class="text-danger">*</span></label>
                <input type="number" class="form-control variation-stock" 
                       name="variations[<?php echo e($index); ?>][stock]" 
                       value="<?php echo e(old("variations.{$index}.stock", $variation->stock ?? 0)); ?>"
                       min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">SKU</label>
                <input type="text" class="form-control variation-sku" 
                       name="variations[<?php echo e($index); ?>][sku]"
                       value="<?php echo e(old("variations.{$index}.sku", $variation->sku ?? '')); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Par défaut</label>
                <div class="form-check form-switch mt-2">
                    <input class="form-check-input variation-default" 
                           type="checkbox" 
                           name="variations[<?php echo e($index); ?>][is_default]" 
                           value="1"
                           <?php echo e(old("variations.{$index}.is_default", $variation->is_default ?? false) ? 'checked' : ''); ?>>
                </div>
            </div>
        </div>
    </div>
</div>

<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/admin/products/partials/variation-row.blade.php ENDPATH**/ ?>