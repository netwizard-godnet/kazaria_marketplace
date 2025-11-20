<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
    <main class="container-fluid">
        <!-- SECTION BREADCRUMB -->
        <section class="bg-light py-2">
            <div class="container-fluid">
                <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb" class="">
                    <ol class="breadcrumb" class="">
                        <li class="breadcrumb-item mb-0"><a href="<?php echo e(route('accueil')); ?>" class="fs-7">Accueil</a></li>
                        <li class="breadcrumb-item mb-0 active fs-7" aria-current="page">Recherche</li>
                    </ol>
                </nav>
            </div>
        </section>
        <!-- SECTION BREADCRUMB END -->

        <!-- SECTION -->
        <section class="py-3">
            <div class="row g-3">
                <div class="col-12 col-sm-3 col-md-2" style="position: sticky; top: 0;">
                    <div class="blue-bg rounded-2 p-3 text-white">
                        <p class="mb-3 fw-bold d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-filter me-2"></i>Filtres</span>
                            <a href="<?php echo e(route('search_product')); ?>" class="btn btn-sm btn-outline-light">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        </p>
                        
                        <form method="GET" action="<?php echo e(route('search_product')); ?>" id="searchFilterForm">
                            <input type="hidden" name="q" value="<?php echo e($searchQuery ?? ''); ?>">
                            
                            <!-- Catégories -->
                            <div class="mb-3">
                                <p class="fw-bold mb-2 fs-7">Catégories</p>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="category_id" 
                                        value="<?php echo e($cat->id); ?>" id="cat<?php echo e($cat->id); ?>"
                                        <?php echo e(request('category_id') == $cat->id ? 'checked' : ''); ?>>
                                    <label class="form-check-label fs-8" for="cat<?php echo e($cat->id); ?>">
                                        <?php if($cat->icon): ?>
                                        <i class="<?php echo e($cat->icon); ?> me-1"></i>
                                        <?php endif; ?>
                                        <?php echo e($cat->name); ?>

                                    </label>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <hr class="text-white">
                            
                            <!-- Prix -->
                            <?php if(isset($priceRange)): ?>
                            <div class="mb-3">
                                <p class="fw-bold mb-2 fs-7">Prix (FCFA)</p>
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
                                <button type="submit" class="btn btn-sm btn-outline-light w-100 mt-2">Appliquer</button>
                            </div>
                            <hr class="text-white">
                            <?php endif; ?>
                            
                            <!-- Note minimum -->
                            <div class="mb-3">
                                <p class="fw-bold mb-2 fs-7">Note minimum</p>
                                <?php for($i = 5; $i >= 1; $i--): ?>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="min_rating" value="<?php echo e($i); ?>" 
                                        id="searchRating<?php echo e($i); ?>" <?php echo e(request('min_rating') == $i ? 'checked' : ''); ?>>
                                    <label class="form-check-label fs-8" for="searchRating<?php echo e($i); ?>">
                                        <?php for($j = 1; $j <= $i; $j++): ?>
                                            <i class="fa-solid fa-star text-warning"></i>
                                        <?php endfor; ?>
                                        & plus
                                    </label>
                                </div>
                                <?php endfor; ?>
                            </div>
                            
                        </form>
                    </div>
                </div>
                <div class="col-12 col-sm-9 col-md-10 bg-light z-index-7x">
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
        <!-- SECTION END -->
    </main>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/search_product.blade.php ENDPATH**/ ?>