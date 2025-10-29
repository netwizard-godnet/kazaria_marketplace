<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
    <main class="container-fluid">
        <!-- SECTION BREADCRUMB -->
        <section class="bg-light py-2">
            <div class="container-fluid">
                <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb" class="">
                    <ol class="breadcrumb" class="">
                        <li class="breadcrumb-item mb-0"><a href="<?php echo e(route('accueil')); ?>" class="fs-7">Accueil</a></li>
                        <li class="breadcrumb-item mb-0 active fs-7" aria-current="page"><?php echo e($category->name); ?></li>
                    </ol>
                </nav>
            </div>
        </section>
        <!-- SECTION BREADCRUMB END -->

        <!-- SECTION MEILLEURES OFFRES -->
        <section class="multi-carousel pb-5 border-top" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Meilleures offres</h5>
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
                    <?php if($categoriePub1Image): ?>
                        <img src="<?php echo e($categoriePub1Image); ?>" class="w-100 h-200px object-fit-cover" alt="Catégorie Pub 1">
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
                    <?php if($categoriePub2Image): ?>
                        <img src="<?php echo e($categoriePub2Image); ?>" class="w-100 h-200px object-fit-cover" alt="Catégorie Pub 2">
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
                    <?php if($categoriePub3Image): ?>
                        <img src="<?php echo e($categoriePub3Image); ?>" class="w-100 h-200px object-fit-cover" alt="Catégorie Pub 3">
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
                <h5 class="mb-0 me-4">Nouveautés</h5>
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

        <!-- SECTION -->
        <section class="py-5">
            <div class="row g-3">
                <div class="col-12 col-sm-3 col-md-2" style="position: sticky; top: 0;">
                    <div class="blue-bg rounded-2 p-3 text-white">
                        <p class="mb-3 fw-bold d-flex align-items-center justify-content-between">
                            <span>
                        <?php if($category->image && !empty($category->image)): ?>
                        <img src="<?php echo e(str_starts_with($category->image, 'http') ? $category->image : (str_starts_with($category->image, 'images/') ? asset($category->image) : Storage::url($category->image))); ?>" alt="<?php echo e($category->name); ?>" style="width: 20px; height: 20px; object-fit: contain;" class="me-2">
                        <?php endif; ?>
                                Filtres
                            </span>
                            <a href="<?php echo e(route('categorie', $category->slug)); ?>" class="btn btn-sm btn-outline-light">
                                <i class="fa-solid fa-rotate-right"></i>
                            </a>
                        </p>
                        
                        <form method="GET" action="<?php echo e(route('categorie', $category->slug)); ?>" id="filterForm">
                            
                            <!-- Sous-catégories -->
                            <?php if($category->subcategories->count() > 0): ?>
                            <div class="mb-3">
                                <p class="fw-bold mb-2 fs-7">Sous-catégories</p>
                                <?php $__currentLoopData = $category->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="radio" name="subcategory" value="<?php echo e($subcategory->id); ?>" 
                                        id="subcat<?php echo e($subcategory->id); ?>" <?php echo e(request('subcategory') == $subcategory->id ? 'checked' : ''); ?>>
                                    <label class="form-check-label fs-8" for="subcat<?php echo e($subcategory->id); ?>">
                            <?php if($subcategory->image && !empty($subcategory->image)): ?>
                            <img src="<?php echo e(str_starts_with($subcategory->image, 'http') ? $subcategory->image : (str_starts_with($subcategory->image, 'images/') ? asset($subcategory->image) : Storage::url($subcategory->image))); ?>" alt="<?php echo e($subcategory->name); ?>" style="width: 16px; height: 16px; object-fit: contain;" class="me-1">
                            <?php endif; ?>
                                        <?php echo e($subcategory->name); ?>

                                    </label>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <hr class="text-white">
                            <?php endif; ?>
                            
                            <!-- Prix -->
                            <?php if(isset($priceRange)): ?>
                            <div class="mb-3">
                                <p class="fw-bold mb-2 fs-7">Prix (FCFA)</p>
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
                                        id="rating<?php echo e($i); ?>" <?php echo e(request('min_rating') == $i ? 'checked' : ''); ?>>
                                    <label class="form-check-label fs-8" for="rating<?php echo e($i); ?>">
                                        <?php for($j = 1; $j <= $i; $j++): ?>
                                            <i class="fa-solid fa-star text-warning"></i>
                                        <?php endfor; ?>
                                        & plus
                                    </label>
                                </div>
                                <?php endfor; ?>
                            </div>
                            <hr class="text-white">
                            
                            <!-- Attributs -->
                            <?php if(isset($attributes)): ?>
                                <?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="mb-3">
                                    <p class="fw-bold mb-2 fs-7"><?php echo e($attribute->name); ?></p>
                                    <?php $__currentLoopData = $attribute->attributeValues->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="form-check mb-1">
                                        <input class="form-check-input" type="checkbox" 
                                            name="attributes[<?php echo e($attribute->id); ?>][]" 
                                            value="<?php echo e($value->id); ?>" 
                                            id="attr<?php echo e($value->id); ?>"
                                            <?php echo e(in_array($value->id, request('attributes.'.$attribute->id, [])) ? 'checked' : ''); ?>>
                                        <label class="form-check-label fs-8" for="attr<?php echo e($value->id); ?>">
                                            <?php echo e($value->value); ?>

                                        </label>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($attribute->attributeValues->count() > 5): ?>
                                    <a href="#" class="text-white fs-8">Voir plus...</a>
                                    <?php endif; ?>
                                </div>
                                <?php if(!$loop->last): ?>
                                <hr class="text-white">
                                <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                            
                        </form>
                    </div>
                </div>
                <div class="col-12 col-sm-9 col-md-10">
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
                    <?php if($categoriePub4Image): ?>
                        <img src="<?php echo e($categoriePub4Image); ?>" class="w-100 h-300px object-fit-cover" alt="Catégorie Pub 4">
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
                    <?php if($categoriePub5Image): ?>
                        <img src="<?php echo e($categoriePub5Image); ?>" class="w-100 h-300px object-fit-cover" alt="Catégorie Pub 5">
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