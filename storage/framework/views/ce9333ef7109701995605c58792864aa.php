<?php
    $currentBanner = $banner ?? null;
    $uniqueId = $prefix ?? ('banner_' . \Illuminate\Support\Str::random(6));
    $linkValue = $currentBanner->link_url ?? '';
    $desktopChecked = ($currentBanner->show_on_desktop ?? true) ? 'checked' : '';
    $mobileChecked = ($currentBanner->show_on_mobile ?? true) ? 'checked' : '';
?>

<div class="form-group">
    <label>Lien (optionnel)</label>
    <input type="url"
           name="link_url"
           class="form-control"
           value="<?php echo e($linkValue); ?>"
           placeholder="https://example.com">
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input type="hidden" name="show_on_desktop" value="0">
            <input class="form-check-input"
                   type="checkbox"
                   id="<?php echo e($uniqueId); ?>Desktop"
                   name="show_on_desktop"
                   value="1"
                   <?php echo e($desktopChecked); ?>>
            <label class="form-check-label" for="<?php echo e($uniqueId); ?>Desktop">
                Afficher sur desktop
            </label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-check form-switch">
            <input type="hidden" name="show_on_mobile" value="0">
            <input class="form-check-input"
                   type="checkbox"
                   id="<?php echo e($uniqueId); ?>Mobile"
                   name="show_on_mobile"
                   value="1"
                   <?php echo e($mobileChecked); ?>>
            <label class="form-check-label" for="<?php echo e($uniqueId); ?>Mobile">
                Afficher sur mobile
            </label>
        </div>
    </div>
</div>

<?php /**PATH D:\kaz_final\kazaria_marketplace\resources\views/admin/banners/partials/link-visibility-fields.blade.php ENDPATH**/ ?>