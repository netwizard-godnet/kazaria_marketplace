

<?php $__env->startSection('title', 'Gestion des Bannières'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Bannières</li>
                    </ol>
                </div>
                <h4 class="page-title">Gestion des Bannières</h4>
            </div>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Bannière header (GIF) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Bannière du header (GIF)</h4>
                    <small class="text-muted">Image affichée au-dessus du header sur toutes les pages</small>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="mb-2">Aperçu actuel :</h6>
                        <?php if($headerBanner && $headerBanner->image_url): ?>
                            <img src="<?php echo e($headerBanner->image_url); ?>" alt="Bannière header" class="img-thumbnail w-100" style="max-height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center border rounded" style="height: 120px;">
                                <i class="fas fa-image text-muted fa-2x"></i>
                                <span class="text-muted ms-2">Aucune bannière configurée</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form action="<?php echo e(route('admin.banners.update-header-gif')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="form-group mb-3">
                            <label class="form-label">Nouvelle image (GIF recommandé)</label>
                            <input type="file" name="image" class="form-control" required>
                            <small class="text-muted">Formats libres (GIF, JPG, PNG, WebP, BMP, SVG, etc.). Pensez à optimiser vos fichiers.</small>
                        </div>
                        <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $headerBanner, 'prefix' => 'headerGif'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="headerGifActive" name="is_active" value="1" <?php echo e(($headerBanner->is_active ?? true) ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="headerGifActive">Afficher la bannière sur le site</label>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit">
                            <i class="fas fa-save me-1"></i>Enregistrer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Gestion des bannières d'accueil -->
    <div class="row mb-4">
        <!-- Première bannière d'accueil -->
        <?php if($homepageBanner1): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Première Bannière d'Accueil</h4>
                    <p class="text-muted">Position 1 à côté du carousel</p>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Image actuelle :</h6>
                        <?php if($homepageBanner1->image_url): ?>
                            <img src="<?php echo e($homepageBanner1->image_url); ?>" alt="Bannière actuelle" class="img-thumbnail" style="max-width: 100%; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 100px;">
                                <i class="fas fa-image text-muted fa-2x"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form action="<?php echo e(route('admin.banners.update-homepage-banner-1')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label>Nouvelle image</label>
                            <input type="file" name="image" class="form-control" required>
                            <small class="text-muted">Formats libres (JPG, PNG, GIF, etc.). Pensez à optimiser vos fichiers (aucune limite de taille).</small>
                        </div>
                        <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $homepageBanner1, 'prefix' => 'homepageBanner1'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <button class="btn btn-primary btn-sm" type="submit">
                            <i class="fas fa-upload"></i> Mettre à jour
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Deuxième bannière d'accueil -->
        <?php if($homepageBanner2): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Deuxième Bannière d'Accueil</h4>
                    <p class="text-muted">Position 2 à côté du carousel</p>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Image actuelle :</h6>
                        <?php if($homepageBanner2->image_url): ?>
                            <img src="<?php echo e($homepageBanner2->image_url); ?>" alt="Bannière actuelle" class="img-thumbnail" style="max-width: 100%; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 100px;">
                                <i class="fas fa-image text-muted fa-2x"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form action="<?php echo e(route('admin.banners.update-homepage-banner-2')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label>Nouvelle image</label>
                            <input type="file" name="image" class="form-control" required>
                            <small class="text-muted">Formats libres (JPG, PNG, GIF, etc.). Pensez à optimiser vos fichiers (aucune limite de taille).</small>
                        </div>
                        <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $homepageBanner2, 'prefix' => 'homepageBanner2'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <button class="btn btn-primary btn-sm" type="submit">
                            <i class="fas fa-upload"></i> Mettre à jour
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Gestion des publicités d'accueil -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Publicités Page d'Accueil</h4>
        </div>
        
        <!-- Publicités 1, 2, 3 (petites) -->
        <div class="row mb-3">
            <!-- Publicité 1 -->
            <?php if($publicite1): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Publicité 1</h6>
                        <p class="text-muted mb-0">Petite publicité (h-200px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($publicite1->image_url): ?>
                                <img src="<?php echo e($publicite1->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-publicite-1')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $publicite1, 'prefix' => 'publicite1'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Publicité 2 -->
            <?php if($publicite2): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Publicité 2</h6>
                        <p class="text-muted mb-0">Petite publicité (h-200px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($publicite2->image_url): ?>
                                <img src="<?php echo e($publicite2->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-publicite-2')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $publicite2, 'prefix' => 'publicite2'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Publicité 3 -->
            <?php if($publicite3): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Publicité 3</h6>
                        <p class="text-muted mb-0">Petite publicité (h-200px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($publicite3->image_url): ?>
                                <img src="<?php echo e($publicite3->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-publicite-3')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $publicite3, 'prefix' => 'publicite3'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Publicités 4 et 5 (grandes) -->
        <div class="row">
            <!-- Publicité 4 -->
            <?php if($publicite4): ?>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Publicité 4</h6>
                        <p class="text-muted mb-0">Grande publicité (h-300px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($publicite4->image_url): ?>
                                <img src="<?php echo e($publicite4->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-publicite-4')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $publicite4, 'prefix' => 'publicite4'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Publicité 5 -->
            <?php if($publicite5): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Publicité 5</h6>
                        <p class="text-muted mb-0">Petite publicité (h-300px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($publicite5->image_url): ?>
                                <img src="<?php echo e($publicite5->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-publicite-5')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $publicite5, 'prefix' => 'publicite5'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gestion du carousel boutique -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Carousel Boutique Officielle</h4>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addCarouselImageModal">
                    <i class="fas fa-plus"></i> Ajouter une image
                </button>
            </div>
        </div>
        
        <?php $__empty_1 = true; $__currentLoopData = $boutiqueCarouselImages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Image <?php echo e($index + 1); ?></h6>
                        <p class="text-muted mb-0">Ordre: <?php echo e($image->sort_order); ?></p>
                    </div>
                    <form action="<?php echo e(route('admin.banners.remove-boutique-carousel-image', $image)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette image du carousel ?')">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Image actuelle :</h6>
                        <?php if($image->image_url): ?>
                            <img src="<?php echo e($image->image_url); ?>" alt="Carousel actuel" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <form action="<?php echo e(route('admin.banners.update-boutique-carousel-image', $image)); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label>Nouvelle image</label>
                            <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                        </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $image, 'prefix' => 'boutiqueCarousel'.$image->id], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <button class="btn btn-primary btn-sm" type="submit">
                            <i class="fas fa-upload"></i> Mettre à jour
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> Aucune image dans le carousel pour le moment.
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal pour ajouter une image -->
    <div class="modal fade" id="addCarouselImageModal" tabindex="-1" aria-labelledby="addCarouselImageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCarouselImageModalLabel">Ajouter une image au carousel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?php echo e(route('admin.banners.add-boutique-carousel-image')); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Image du carousel</label>
                            <input type="file" name="image" class="form-control" required>
                            <small class="text-muted">Formats libres (JPG, PNG, GIF, etc.). Pensez à optimiser vos fichiers (aucune limite de taille).</small>
                        </div>
                        <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => null, 'prefix' => 'newBoutiqueCarousel'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-plus"></i> Ajouter l'image
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Gestion des publicités boutique -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Publicités Boutique Officielle</h4>
        </div>
        
        <!-- Publicités 1, 2, 3 (petites) -->
        <div class="row mb-3">
            <!-- Boutique Pub 1 -->
            <?php if($boutiquePub1): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Boutique Pub 1</h6>
                        <p class="text-muted mb-0">Petite publicité (h-200px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($boutiquePub1->image_url): ?>
                                <img src="<?php echo e($boutiquePub1->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-boutique-pub-1')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $boutiquePub1, 'prefix' => 'boutiquePub1'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Boutique Pub 2 -->
            <?php if($boutiquePub2): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Boutique Pub 2</h6>
                        <p class="text-muted mb-0">Petite publicité (h-200px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($boutiquePub2->image_url): ?>
                                <img src="<?php echo e($boutiquePub2->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-boutique-pub-2')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $boutiquePub2, 'prefix' => 'boutiquePub2'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Boutique Pub 3 -->
            <?php if($boutiquePub3): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Boutique Pub 3</h6>
                        <p class="text-muted mb-0">Petite publicité (h-200px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($boutiquePub3->image_url): ?>
                                <img src="<?php echo e($boutiquePub3->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-boutique-pub-3')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $boutiquePub3, 'prefix' => 'boutiquePub3'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Publicités 4 et 5 (grandes) -->
        <div class="row">
            <!-- Boutique Pub 4 -->
            <?php if($boutiquePub4): ?>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Boutique Pub 4</h6>
                        <p class="text-muted mb-0">Grande publicité (h-300px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($boutiquePub4->image_url): ?>
                                <img src="<?php echo e($boutiquePub4->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-boutique-pub-4')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $boutiquePub4, 'prefix' => 'boutiquePub4'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Boutique Pub 5 -->
            <?php if($boutiquePub5): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Boutique Pub 5</h6>
                        <p class="text-muted mb-0">Petite publicité (h-300px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($boutiquePub5->image_url): ?>
                                <img src="<?php echo e($boutiquePub5->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-boutique-pub-5')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $boutiquePub5, 'prefix' => 'boutiquePub5'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gestion des publicités catégorie -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-3">Publicités Page Catégorie</h4>
        </div>
        
        <!-- Publicités 1, 2, 3 (petites) -->
        <div class="row mb-3">
            <!-- Catégorie Pub 1 -->
            <?php if($categoriePub1): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Catégorie Pub 1</h6>
                        <p class="text-muted mb-0">Petite publicité (h-200px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($categoriePub1->image_url): ?>
                                <img src="<?php echo e($categoriePub1->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-categorie-pub-1')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $categoriePub1, 'prefix' => 'categoriePub1'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Catégorie Pub 2 -->
            <?php if($categoriePub2): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Catégorie Pub 2</h6>
                        <p class="text-muted mb-0">Petite publicité (h-200px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($categoriePub2->image_url): ?>
                                <img src="<?php echo e($categoriePub2->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-categorie-pub-2')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $categoriePub2, 'prefix' => 'categoriePub2'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Catégorie Pub 3 -->
            <?php if($categoriePub3): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Catégorie Pub 3</h6>
                        <p class="text-muted mb-0">Petite publicité (h-200px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($categoriePub3->image_url): ?>
                                <img src="<?php echo e($categoriePub3->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-categorie-pub-3')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $categoriePub3, 'prefix' => 'categoriePub3'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Publicités 4 et 5 (grandes) -->
        <div class="row">
            <!-- Catégorie Pub 4 -->
            <?php if($categoriePub4): ?>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Catégorie Pub 4</h6>
                        <p class="text-muted mb-0">Grande publicité (h-300px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($categoriePub4->image_url): ?>
                                <img src="<?php echo e($categoriePub4->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-categorie-pub-4')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $categoriePub4, 'prefix' => 'categoriePub4'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Catégorie Pub 5 -->
            <?php if($categoriePub5): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Catégorie Pub 5</h6>
                        <p class="text-muted mb-0">Petite publicité (h-300px)</p>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6>Image actuelle :</h6>
                            <?php if($categoriePub5->image_url): ?>
                                <img src="<?php echo e($categoriePub5->image_url); ?>" alt="Publicité actuelle" class="img-thumbnail" style="max-width: 100%; height: 80px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center" style="width: 100%; height: 80px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form action="<?php echo e(route('admin.banners.update-categorie-pub-5')); ?>" method="POST" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="form-group">
                                <label>Nouvelle image</label>
                                <input type="file" name="image" class="form-control" required>
                                <small class="text-muted">Formats libres (aucune limite de taille — pensez à optimiser vos fichiers).</small>
                            </div>
                            <?php echo $__env->make('admin.banners.partials.link-visibility-fields', ['banner' => $categoriePub5, 'prefix' => 'categoriePub5'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-upload"></i> Mettre à jour
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/admin/banners/index.blade.php ENDPATH**/ ?>