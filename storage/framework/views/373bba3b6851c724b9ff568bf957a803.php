<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
    <main class="container-fluid">
        <!-- SECTION BREADCRUMB ET TITRE -->
        <section class="bg-white py-3 border-bottom">
            <div class="container-fluid">
                <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2" style="--bs-breadcrumb-item-color: #f04e27;">
                        <li class="breadcrumb-item">
                            <a href="<?php echo e(route('accueil')); ?>" class="text-decoration-none" style="color: #f04e27;">Accueil</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page" style="color: #f04e27;">
                            <?php if($searchQuery): ?>
                                Recherche
                            <?php else: ?>
                                Tous les produits
                            <?php endif; ?>
                        </li>
                    </ol>
                </nav>
                <h1 class="fw-bold mb-0" style="font-size: 2rem; color: #333;">
                    <?php if($searchQuery): ?>
                        Résultats pour "<?php echo e($searchQuery); ?>"
                    <?php else: ?>
                        Tous les produits
                    <?php endif; ?>
                </h1>
            </div>
        </section>
        <!-- SECTION BREADCRUMB ET TITRE END -->

        <?php
            $activeFilters = 0;
            if(request()->filled('category_id')) $activeFilters++;
            if(request()->filled('min_price')) $activeFilters++;
            if(request()->filled('max_price')) $activeFilters++;
            if(request()->filled('min_rating')) $activeFilters++;
        ?>
        <!-- SECTION -->
        <section class="py-3">
            <div class="d-sm-none mb-3">
                <button class="btn blue-bg text-white w-100 d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="offcanvas" data-bs-target="#searchFilters" aria-controls="searchFilters">
                    <span>Filtrer les résultats<i class="bi bi-funnel ms-2"></i></span>
                    <?php if($activeFilters > 0): ?>
                        <span class="badge bg-white text-dark"><?php echo e($activeFilters); ?> actif(s)</span>
                    <?php endif; ?>
                </button>
            </div>
            <div class="row g-4 align-items-start">
                <div class="col-lg-3 col-xl-2 d-none d-lg-block">
                    <?php echo $__env->make('components.search-filter-form', [
                        'categories' => $categories,
                        'priceRange' => $priceRange ?? null,
                        'formId' => 'searchFilterFormDesktop',
                        'wrapperClass' => 'sticky-top',
                        'searchQuery' => $searchQuery ?? null
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div class="col-12 col-lg-9 col-xl-10 bg-light z-index-7x">
                    <div id="searchResults" class="">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="bg-light d-flex align-items-center justify-content-between mb-4 border-bottom p-2">
                                    <p class="mb-0 me-4">
                                        <?php if($searchQuery): ?>
                                            Résultats pour "<?php echo e($searchQuery); ?>" (<?php echo e($products->total()); ?> produits)
                                        <?php else: ?>
                                            Tous les produits (<?php echo e($products->total()); ?> produits)
                                        <?php endif; ?>
                                    </p>
                                    <div class="">
                                        <form method="GET" action="<?php echo e(route('search_product')); ?>" class="d-inline" id="searchSortForm">
                                            <?php if($searchQuery): ?>
                                            <input type="hidden" name="q" value="<?php echo e($searchQuery); ?>">
                                            <?php endif; ?>
                                            <?php $__currentLoopData = request()->except(['sort', 'q']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if(!is_array($value)): ?>
                                                    <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <select name="sort" class="form-select form-select-sm d-inline-block w-auto">
                                                <option value="">Trier par...</option>
                                                <option value="price_asc" <?php echo e(request('sort') == 'price_asc' ? 'selected' : ''); ?>>Prix croissant</option>
                                                <option value="price_desc" <?php echo e(request('sort') == 'price_desc' ? 'selected' : ''); ?>>Prix décroissant</option>
                                                <option value="rating" <?php echo e(request('sort') == 'rating' ? 'selected' : ''); ?>>Meilleures notes</option>
                                                <option value="popular" <?php echo e(request('sort') == 'popular' ? 'selected' : ''); ?>>Popularité</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                <div class="px-1">
                                    <?php echo $__env->make('components.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <?php if($searchQuery): ?>
                                        Aucun produit trouvé pour "<?php echo e($searchQuery); ?>". Essayez avec d'autres mots-clés.
                                    <?php else: ?>
                                        Aucun produit disponible pour le moment.
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($products->hasPages()): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <?php echo e($products->links('pagination.custom')); ?>

                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <div class="offcanvas offcanvas-start filter-offcanvas" tabindex="-1" id="searchFilters" aria-labelledby="searchFiltersLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title section-title" id="searchFiltersLabel">Filtres</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <?php echo $__env->make('components.search-filter-form', [
                    'categories' => $categories,
                    'priceRange' => $priceRange ?? null,
                    'formId' => 'searchFilterFormMobile',
                    'searchQuery' => $searchQuery ?? null
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
        <!-- SECTION END -->
    </main>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\search_product.blade.php ENDPATH**/ ?>