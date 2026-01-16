<div class="card mb-3 banner-item" data-banner-index="<?php echo e($index); ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Bannière #<?php echo e($index + 1); ?></h6>
        <button type="button" class="btn btn-sm btn-danger remove-banner-btn">
            <i class="fas fa-trash"></i> Supprimer
        </button>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Titre (optionnel)</label>
                    <input type="text" class="form-control banner-title" 
                           value="<?php echo e($banner['title'] ?? ''); ?>" 
                           placeholder="Titre de la bannière">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Position dans le layout</label>
                    <input type="number" class="form-control banner-order" 
                           value="<?php echo e($banner['order'] ?? (10 + $index)); ?>" 
                           min="0" step="0.1"
                           placeholder="Ex: 1.5 (entre section 1 et 2)">
                    <small class="text-muted">Définissez l'ordre d'affichage. Les sections par défaut sont : 1=Meilleures offres, 2=Bannières sup, 3=Nouveautés, 4=Produits, 5=Bannières inf. Utilisez des décimales (ex: 1.5) pour insérer entre deux sections.</small>
                </div>
            </div>
        </div>
        
        <div class="form-group mb-3">
            <label>Image</label>
            <input type="file" class="form-control banner-image-input" accept="image/*">
            <?php if(isset($banner['image']) && $banner['image']): ?>
                <div class="mt-2">
                    <img src="<?php echo e(str_starts_with($banner['image'], 'http') ? $banner['image'] : (str_starts_with($banner['image'], 'images/') ? asset($banner['image']) : asset('storage/' . $banner['image']))); ?>" 
                         class="img-thumbnail banner-preview" 
                         style="max-width: 200px; max-height: 150px;">
                    <input type="hidden" class="banner-image-url" value="<?php echo e($banner['image']); ?>">
                </div>
            <?php else: ?>
                <div class="mt-2 banner-preview-container" style="display: none;">
                    <img src="" class="img-thumbnail banner-preview" style="max-width: 200px; max-height: 150px;">
                    <input type="hidden" class="banner-image-url" value="">
                </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>URL du lien (optionnel)</label>
                    <input type="url" class="form-control banner-link-url" 
                           value="<?php echo e($banner['link_url'] ?? ''); ?>" 
                           placeholder="https://example.com">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Cible du lien</label>
                    <select class="form-control banner-link-target">
                        <option value="_blank" <?php echo e(($banner['link_target'] ?? '_blank') === '_blank' ? 'selected' : ''); ?>>Nouvel onglet</option>
                        <option value="_self" <?php echo e(($banner['link_target'] ?? '_blank') === '_self' ? 'selected' : ''); ?>>Même onglet</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label>Colonnes Bootstrap</label>
                    <select class="form-control banner-columns">
                        <option value="col-12" <?php echo e(($banner['columns'] ?? 'col-12') === 'col-12' ? 'selected' : ''); ?>>12 colonnes (pleine largeur)</option>
                        <option value="col-md-6" <?php echo e(($banner['columns'] ?? 'col-12') === 'col-md-6' ? 'selected' : ''); ?>>6 colonnes (moitié)</option>
                        <option value="col-md-4" <?php echo e(($banner['columns'] ?? 'col-12') === 'col-md-4' ? 'selected' : ''); ?>>4 colonnes (tiers)</option>
                        <option value="col-md-3" <?php echo e(($banner['columns'] ?? 'col-12') === 'col-md-3' ? 'selected' : ''); ?>>3 colonnes (quart)</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label>Hauteur (optionnel)</label>
                    <input type="text" class="form-control banner-height" 
                           value="<?php echo e($banner['height'] ?? ''); ?>" 
                           placeholder="ex: 200px, 50vh">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label>Texte alternatif</label>
                    <input type="text" class="form-control banner-alt" 
                           value="<?php echo e($banner['alt'] ?? ''); ?>" 
                           placeholder="Description de l'image">
                </div>
            </div>
        </div>

        <div class="form-check">
            <input class="form-check-input banner-enabled" type="checkbox" 
                   <?php echo e(($banner['enabled'] ?? true) ? 'checked' : ''); ?>>
            <label class="form-check-label">Bannière activée</label>
        </div>
    </div>
</div>

<?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/categories/partials/banner-item.blade.php ENDPATH**/ ?>