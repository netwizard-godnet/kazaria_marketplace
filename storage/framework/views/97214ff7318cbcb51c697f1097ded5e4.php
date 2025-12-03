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
                        <?php if(isset($subcategory) && $subcategory): ?>
                            <li class="breadcrumb-item">
                                <a href="<?php echo e(route('categorie', $category->slug)); ?>" class="text-decoration-none" style="color: #f04e27;"><?php echo e($category->name); ?></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page" style="color: #f04e27;"><?php echo e($subcategory->name); ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page" style="color: #f04e27;"><?php echo e($category->name); ?></li>
                        <?php endif; ?>
                    </ol>
                </nav>
                <h1 class="fw-bold mb-0" style="font-size: 1.6rem; color: #333;">
                    <?php if(isset($subcategory) && $subcategory): ?>
                        <?php echo e($subcategory->name); ?>

                    <?php else: ?>
                        <?php echo e($category->name); ?>

                    <?php endif; ?>
                </h1>
            </div>
        </section>
        <!-- SECTION BREADCRUMB ET TITRE END -->

        <!-- SECTION MEILLEURES OFFRES -->
        <section class="multi-carousel pb-5 border-top" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h4 class="section-title mb-0 me-4">Meilleures offres</h4>
            </div>
            <div class="multi-carousel-track d-flex">
                <?php $__empty_1 = true; $__currentLoopData = $bestOffers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="multi-carousel-item px-2">
                    <?php echo $__env->make('components.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12">
                    <p class="text-muted text-center py-4">Aucune offre disponible pour le moment.</p>
                </div>
                <?php endif; ?>
            </div>
            <?php if($bestOffers->count() > 0): ?>
            <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="multi-carousel-dots text-center mt-2"></div>
            <?php endif; ?>
        </section>
        <!-- SECTION MEILLEURES OFFRES END -->

        <!-- SECTION AFFICHES -->
        <section class="py-5">
            <div class="row g-3">
                <div class="col-md-4">
                    <!-- Catégorie pub 1 -->
                    <?php
                        $categoriePub1 = App\Models\Banner::getCategoriePub1();
                        $categoriePub1Image = $categoriePub1 ? $categoriePub1->image_url : null;
                    ?>
                    <?php if($categoriePub1 && $categoriePub1Image): ?>
                        <div class="<?php echo e($categoriePub1->visibility_classes ?? ''); ?>">
                            <?php if($categoriePub1->link_url): ?>
                                <a href="<?php echo e($categoriePub1->link_url); ?>" target="_blank" rel="noopener" class="d-block">
                            <?php endif; ?>
                        <img src="<?php echo e($categoriePub1Image); ?>" class="w-100 object-fit-cover" alt="Catégorie Pub 1">
                            <?php if($categoriePub1->link_url): ?></a><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="w-100 h-200px bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image text-muted fa-3x"></i>
                        </div>
                    <?php endif; ?>
                    <!-- Catégorie pub 1 end -->
                </div>
                <div class="col-md-4">
                    <!-- Catégorie pub 2 -->
                    <?php
                        $categoriePub2 = App\Models\Banner::getCategoriePub2();
                        $categoriePub2Image = $categoriePub2 ? $categoriePub2->image_url : null;
                    ?>
                    <?php if($categoriePub2 && $categoriePub2Image): ?>
                        <div class="<?php echo e($categoriePub2->visibility_classes ?? ''); ?>">
                            <?php if($categoriePub2->link_url): ?>
                                <a href="<?php echo e($categoriePub2->link_url); ?>" target="_blank" rel="noopener" class="d-block">
                            <?php endif; ?>
                        <img src="<?php echo e($categoriePub2Image); ?>" class="w-100 object-fit-cover" alt="Catégorie Pub 2">
                            <?php if($categoriePub2->link_url): ?></a><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="w-100 h-200px bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image text-muted fa-3x"></i>
                        </div>
                    <?php endif; ?>
                    <!-- Catégorie pub 2 end -->
                </div>
                <div class="col-md-4">
                    <!-- Catégorie pub 3 -->
                    <?php
                        $categoriePub3 = App\Models\Banner::getCategoriePub3();
                        $categoriePub3Image = $categoriePub3 ? $categoriePub3->image_url : null;
                    ?>
                    <?php if($categoriePub3 && $categoriePub3Image): ?>
                        <div class="<?php echo e($categoriePub3->visibility_classes ?? ''); ?>">
                            <?php if($categoriePub3->link_url): ?>
                                <a href="<?php echo e($categoriePub3->link_url); ?>" target="_blank" rel="noopener" class="d-block">
                            <?php endif; ?>
                        <img src="<?php echo e($categoriePub3Image); ?>" class="w-100 object-fit-cover" alt="Catégorie Pub 3">
                            <?php if($categoriePub3->link_url): ?></a><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="w-100 h-200px bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image text-muted fa-3x"></i>
                        </div>
                    <?php endif; ?>
                    <!-- Catégorie pub 3 end -->
                </div>
            </div>
        </section>
        <!-- SECTION AFFICHES END -->

        <!-- SECTION NOUVEAUTES -->
        <section class="multi-carousel py-5" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
                <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                    <h4 class="section-title mb-0 me-4">Nouveautés</h4>
            </div>
            <div class="multi-carousel-track d-flex">
                <?php $__empty_1 = true; $__currentLoopData = $newProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="multi-carousel-item px-2">
                    <?php echo $__env->make('components.product-card', ['product' => $product], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12">
                    <p class="text-muted text-center py-4">Aucune nouveauté disponible pour le moment.</p>
                </div>
                <?php endif; ?>
            </div>
            <?php if($newProducts->count() > 0): ?>
            <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="multi-carousel-dots text-center mt-2"></div>
            <?php endif; ?>
        </section>
        <!-- SECTION NOUVEAUTES END -->

        <?php
            $hasAttributeFilters = collect(request('attributes', []))->flatten()->filter()->isNotEmpty();
            $activeFilters = 0;
            if(request()->filled('subcategory')) $activeFilters++;
            if(request()->filled('min_price')) $activeFilters++;
            if(request()->filled('max_price')) $activeFilters++;
            if(request()->filled('min_rating')) $activeFilters++;
            if($hasAttributeFilters) $activeFilters++;
        ?>
        <!-- SECTION -->
        <section class="py-5">
            <div class="d-sm-none mb-3">
                <button class="btn blue-bg text-white w-100 d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileFilters" aria-controls="mobileFilters">
                    <span>Filtrer les résultats<i class="bi bi-funnel ms-2"></i></span>
                    <?php if($activeFilters > 0): ?>
                        <span class="badge bg-white text-dark"><?php echo e($activeFilters); ?> actif(s)</span>
                    <?php endif; ?>
                </button>
            </div>
            <div class="row g-4 align-items-start">
                <div class="col-lg-3 col-xl-2 d-none d-lg-block">
                    <?php echo $__env->make('components.category-filter-form', [
                        'category' => $category,
                        'priceRange' => $priceRange ?? null,
                        'attributes' => $attributes ?? null,
                        'formId' => 'desktopFilterForm',
                        'inputPrefix' => 'desktop-',
                        'wrapperClass' => 'sticky-top'
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
                <div class="col-12 col-lg-9 col-xl-10">
                    <div id="productResults">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="bg-light d-flex align-items-center justify-content-between mb-4 border-bottom p-2">
                                    <p class="mb-0 me-4"><?php echo e($category->name); ?> (<?php echo e($products->total()); ?> résultats)</p>
                                    <div class="">
                                        <form method="GET" action="<?php echo e(route('categorie', $category->slug)); ?>" class="d-inline" id="categorySortForm">
                                            <?php $__currentLoopData = request()->except(['sort', 'order']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if(is_array($value)): ?>
                                                    <?php $__currentLoopData = $value; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subKey => $subValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if(is_array($subValue)): ?>
                                                            <?php $__currentLoopData = $subValue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <input type="hidden" name="<?php echo e($key); ?>[<?php echo e($subKey); ?>][]" value="<?php echo e($item); ?>">
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php else: ?>
                                                            <input type="hidden" name="<?php echo e($key); ?>[<?php echo e($subKey); ?>]" value="<?php echo e($subValue); ?>">
                                                        <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php else: ?>
                                                    <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <select name="sort" class="form-select form-select-sm d-inline-block w-auto">
                                                <option value="">Trier par...</option>
                                                <option value="price_asc" <?php echo e(request('sort') == 'price_asc' ? 'selected' : ''); ?>>Prix croissant</option>
                                                <option value="price_desc" <?php echo e(request('sort') == 'price_desc' ? 'selected' : ''); ?>>Prix décroissant</option>
                                                <option value="rating" <?php echo e(request('sort') == 'rating' ? 'selected' : ''); ?>>Meilleures notes</option>
                                                <option value="popular" <?php echo e(request('sort') == 'popular' ? 'selected' : ''); ?>>Popularité</option>
                                                <option value="created_at" <?php echo e(request('sort') == 'created_at' ? 'selected' : ''); ?>>Nouveautés</option>
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
                                    Aucun produit disponible dans cette catégorie pour le moment.
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
        <div class="offcanvas offcanvas-start filter-offcanvas" tabindex="-1" id="mobileFilters" aria-labelledby="mobileFiltersLabel">
            <div class="offcanvas-header">
                            <h5 class="offcanvas-title section-title" id="mobileFiltersLabel">Filtres</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <?php echo $__env->make('components.category-filter-form', [
                    'category' => $category,
                    'priceRange' => $priceRange ?? null,
                    'attributes' => $attributes ?? null,
                    'formId' => 'mobileFilterForm',
                    'inputPrefix' => 'mobile-',
                    'wrapperClass' => 'mb-0'
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
        <!-- SECTION END -->

        <!-- SECTION AFFICHES -->
        <section class="py-5">
            <div class="row g-3">
                <div class="col-md-8">
                    <!-- Catégorie pub 4 -->
                    <?php
                        $categoriePub4 = App\Models\Banner::getCategoriePub4();
                        $categoriePub4Image = $categoriePub4 ? $categoriePub4->image_url : null;
                    ?>
                    <?php if($categoriePub4 && $categoriePub4Image): ?>
                        <div class="<?php echo e($categoriePub4->visibility_classes ?? ''); ?>">
                            <?php if($categoriePub4->link_url): ?>
                                <a href="<?php echo e($categoriePub4->link_url); ?>" target="_blank" rel="noopener" class="d-block">
                            <?php endif; ?>
                        <img src="<?php echo e($categoriePub4Image); ?>" class="w-100 object-fit-cover" alt="Catégorie Pub 4">
                            <?php if($categoriePub4->link_url): ?></a><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="w-100 h-300px bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image text-muted fa-3x"></i>
                        </div>
                    <?php endif; ?>
                    <!-- Catégorie pub 4 end -->
                </div>
                <div class="col-md-4">
                    <!-- Catégorie pub 5 -->
                    <?php
                        $categoriePub5 = App\Models\Banner::getCategoriePub5();
                        $categoriePub5Image = $categoriePub5 ? $categoriePub5->image_url : null;
                    ?>
                    <?php if($categoriePub5 && $categoriePub5Image): ?>
                        <div class="<?php echo e($categoriePub5->visibility_classes ?? ''); ?>">
                            <?php if($categoriePub5->link_url): ?>
                                <a href="<?php echo e($categoriePub5->link_url); ?>" target="_blank" rel="noopener" class="d-block">
                            <?php endif; ?>
                        <img src="<?php echo e($categoriePub5Image); ?>" class="w-100 object-fit-cover" alt="Catégorie Pub 5">
                            <?php if($categoriePub5->link_url): ?></a><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="w-100 h-300px bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-image text-muted fa-3x"></i>
                        </div>
                    <?php endif; ?>
                    <!-- Catégorie pub 5 end -->
                </div>
            </div>
        </section>
        <!-- SECTION AFFICHES END -->
    </main>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/categorie.blade.php ENDPATH**/ ?>