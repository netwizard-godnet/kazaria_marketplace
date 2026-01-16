<div class="card mb-3 carousel-item" data-carousel-index="<?php echo e($index); ?>">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Carrousel #<?php echo e($index + 1); ?></h6>
        <button type="button" class="btn btn-sm btn-danger remove-carousel-btn">
            <i class="fas fa-trash"></i> Supprimer
        </button>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Titre du carrousel</label>
                    <input type="text" class="form-control carousel-title" 
                           value="<?php echo e($carousel['title'] ?? ''); ?>" 
                           placeholder="Titre du carrousel">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label>Position dans le layout</label>
                    <input type="number" class="form-control carousel-order" 
                           value="<?php echo e($carousel['order'] ?? (20 + $index)); ?>" 
                           min="0" step="0.1"
                           placeholder="Ex: 3.5 (entre Nouveautés et Produits)">
                    <small class="text-muted">Définissez l'ordre d'affichage. Les sections par défaut sont : 1=Meilleures offres, 2=Bannières sup, 3=Nouveautés, 4=Produits, 5=Bannières inf. Utilisez des décimales (ex: 3.5) pour insérer entre deux sections.</small>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Slides à afficher</label>
                <input type="number" class="form-control carousel-slides-to-show" 
                       value="<?php echo e($carousel['slides_to_show'] ?? 6); ?>" min="1" max="12">
            </div>
            <div class="col-md-3">
                <label>Slides (Large)</label>
                <input type="number" class="form-control carousel-slides-lg" 
                       value="<?php echo e($carousel['slides_lg'] ?? 4); ?>" min="1" max="12">
            </div>
            <div class="col-md-3">
                <label>Slides (Moyen)</label>
                <input type="number" class="form-control carousel-slides-md" 
                       value="<?php echo e($carousel['slides_md'] ?? 3); ?>" min="1" max="12">
            </div>
            <div class="col-md-3">
                <label>Slides (Petit)</label>
                <input type="number" class="form-control carousel-slides-sm" 
                       value="<?php echo e($carousel['slides_sm'] ?? 2); ?>" min="1" max="12">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Espacement (gap)</label>
                <input type="number" class="form-control carousel-gap" 
                       value="<?php echo e($carousel['gap'] ?? 0); ?>" min="0">
            </div>
            <div class="col-md-4">
                <label>Vitesse autoplay (ms)</label>
                <input type="number" class="form-control carousel-autoplay-speed" 
                       value="<?php echo e($carousel['autoplay_speed'] ?? 2000); ?>" min="500">
            </div>
            <div class="col-md-4">
                <div class="form-check mt-4">
                    <input class="form-check-input carousel-autoplay" type="checkbox" 
                           <?php echo e(($carousel['autoplay'] ?? true) ? 'checked' : ''); ?>>
                    <label class="form-check-label">Autoplay</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input carousel-pause-on-hover" type="checkbox" 
                           <?php echo e(($carousel['pause_on_hover'] ?? true) ? 'checked' : ''); ?>>
                    <label class="form-check-label">Pause au survol</label>
                </div>
            </div>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Images du carrousel</h6>
            <button type="button" class="btn btn-sm btn-primary add-carousel-image-btn">
                <i class="fas fa-plus"></i> Ajouter une image
            </button>
        </div>

        <div class="carousel-images-container">
            <?php
                $carouselImages = $carousel['images'] ?? [];
            ?>
            <?php if(!empty($carouselImages)): ?>
                <?php $__currentLoopData = $carouselImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $imgIndex => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="card mb-2 carousel-image-item" data-image-index="<?php echo e($imgIndex); ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <?php if(isset($image['url']) && $image['url']): ?>
                                        <img src="<?php echo e(str_starts_with($image['url'], 'http') ? $image['url'] : (str_starts_with($image['url'], 'images/') ? asset($image['url']) : asset('storage/' . $image['url']))); ?>" 
                                             class="img-thumbnail carousel-image-preview" 
                                             style="max-width: 100px; max-height: 80px;">
                                    <?php else: ?>
                                        <div class="carousel-image-preview-container" style="display: none;">
                                            <img src="" class="img-thumbnail carousel-image-preview" style="max-width: 100px; max-height: 80px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control form-control-sm mt-2 carousel-image-input" accept="image/*">
                                    <input type="hidden" class="carousel-image-url" value="<?php echo e($image['url'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label>URL du lien</label>
                                    <input type="url" class="form-control form-control-sm carousel-image-link-url" 
                                           value="<?php echo e($image['link_url'] ?? ''); ?>" 
                                           placeholder="https://example.com">
                                </div>
                                <div class="col-md-2">
                                    <label>Cible</label>
                                    <select class="form-control form-control-sm carousel-image-link-target">
                                        <option value="_blank" <?php echo e(($image['link_target'] ?? '_blank') === '_blank' ? 'selected' : ''); ?>>Nouvel onglet</option>
                                        <option value="_self" <?php echo e(($image['link_target'] ?? '_blank') === '_self' ? 'selected' : ''); ?>>Même onglet</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Texte alternatif</label>
                                    <input type="text" class="form-control form-control-sm carousel-image-alt" 
                                           value="<?php echo e($image['alt'] ?? ''); ?>" 
                                           placeholder="Description">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-danger remove-carousel-image-btn">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </div>

        <div class="form-check mt-3">
            <input class="form-check-input carousel-enabled" type="checkbox" 
                   <?php echo e(($carousel['enabled'] ?? true) ? 'checked' : ''); ?>>
            <label class="form-check-label">Carrousel activé</label>
        </div>
    </div>
</div>

<?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/categories/partials/carousel-item.blade.php ENDPATH**/ ?>