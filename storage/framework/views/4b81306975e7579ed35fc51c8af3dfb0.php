<?php $__env->startSection('title', $pageTitle); ?>

<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar des filtres -->
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo e(request()->url()); ?>">
                        <!-- Recherche -->
                        <div class="mb-3">
                            <label for="q" class="form-label">Recherche</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="q" 
                                   name="q" 
                                   value="<?php echo e(request('q')); ?>" 
                                   placeholder="Rechercher un produit...">
                        </div>

                        <!-- Filtre par catégorie -->
                        <div class="mb-3">
                            <label class="form-label">Catégorie</label>
                            <select name="category" class="form-select">
                                <option value="">Toutes les catégories</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->slug); ?>" 
                                            <?php echo e(request('category') === $category->slug ? 'selected' : ''); ?>>
                                        <?php echo e($category->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Filtre par prix -->
                        <div class="mb-3">
                            <label class="form-label">Prix</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" 
                                           class="form-control" 
                                           name="min_price" 
                                           value="<?php echo e(request('min_price')); ?>" 
                                           placeholder="Min">
                                </div>
                                <div class="col-6">
                                    <input type="number" 
                                           class="form-control" 
                                           name="max_price" 
                                           value="<?php echo e(request('max_price')); ?>" 
                                           placeholder="Max">
                                </div>
                            </div>
                            <?php if($priceRange): ?>
                                <small class="text-muted">
                                    Prix: <?php echo e(number_format($priceRange->min_price)); ?> - <?php echo e(number_format($priceRange->max_price)); ?> FCFA
                                </small>
                            <?php endif; ?>
                        </div>

                        <!-- Filtres par attribut -->
                        <?php if($attributeValues->count() > 0): ?>
                        <div class="mb-3">
                            <label class="form-label"><?php echo e($attribute->name); ?></label>
                            <div class="list-group list-group-flush">
                                <a href="<?php echo e(route('products.by-attribute', $attribute->slug)); ?>" 
                                   class="list-group-item list-group-item-action <?php echo e(!request('value') ? 'active' : ''); ?>">
                                    Tous les <?php echo e(strtolower($attribute->name)); ?>s
                                </a>
                                <?php $__currentLoopData = $attributeValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(route('products.by-attribute-value', [$attribute->slug, $value->slug])); ?>" 
                                   class="list-group-item list-group-item-action <?php echo e(request('value') === $value->slug ? 'active' : ''); ?>">
                                    <?php echo e($value->value); ?>

                                </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Autres attributs -->
                        <?php $__currentLoopData = $otherAttributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $otherAttribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($otherAttribute->attributeValues->count() > 0): ?>
                        <div class="mb-3">
                            <label class="form-label"><?php echo e($otherAttribute->name); ?></label>
                            <div class="list-group list-group-flush">
                                <?php $__currentLoopData = $otherAttribute->attributeValues->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="attributes[<?php echo e($otherAttribute->slug); ?>][]" 
                                           value="<?php echo e($value->slug); ?>" 
                                           id="<?php echo e($otherAttribute->slug); ?>_<?php echo e($value->slug); ?>"
                                           <?php echo e(in_array($value->slug, request('attributes.'.$otherAttribute->slug, [])) ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="<?php echo e($otherAttribute->slug); ?>_<?php echo e($value->slug); ?>">
                                        <?php echo e($value->value); ?>

                                    </label>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Appliquer les filtres
                            </button>
                            <a href="<?php echo e(request()->url()); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Effacer
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="col-lg-9">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1"><?php echo e($pageTitle); ?></h2>
                    <p class="text-muted mb-0">
                        <?php echo e($products->total()); ?> produit(s) trouvé(s)
                    </p>
                </div>
                
                <!-- Tri -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-sort me-2"></i>
                        <?php switch(request('sort', 'name')):
                            case ('price_asc'): ?> Prix croissant <?php break; ?>
                            <?php case ('price_desc'): ?> Prix décroissant <?php break; ?>
                            <?php case ('newest'): ?> Plus récents <?php break; ?>
                            <?php case ('popular'): ?> Plus populaires <?php break; ?>
                            <?php default: ?> Nom A-Z <?php break; ?>
                        <?php endswitch; ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'name'])); ?>">Nom A-Z</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'price_asc'])); ?>">Prix croissant</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'price_desc'])); ?>">Prix décroissant</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'newest'])); ?>">Plus récents</a></li>
                        <li><a class="dropdown-item" href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'popular'])); ?>">Plus populaires</a></li>
                    </ul>
                </div>
            </div>

            <!-- Produits -->
            <?php if($products->count() > 0): ?>
                <div class="row g-4">
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card h-100 product-card">
                            <div class="position-relative">
                                <?php if($product->image): ?>
                                <img src="<?php echo e(str_starts_with($product->image, 'http') ? $product->image : asset('storage/' . $product->image)); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo e($product->name); ?>"
                                     style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                                <?php endif; ?>
                                
                                <?php if($product->is_new): ?>
                                <span class="badge bg-success position-absolute top-0 start-0 m-2">Nouveau</span>
                                <?php endif; ?>
                                
                                <?php if($product->old_price && $product->old_price > $product->price): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                    -<?php echo e(round((($product->old_price - $product->price) / $product->old_price) * 100)); ?>%
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <h6 class="card-title">
                                    <a href="<?php echo e(route('product-page', $product->slug)); ?>" class="text-decoration-none">
                                        <?php echo e(Str::limit($product->name, 50)); ?>

                                    </a>
                                </h6>
                                
                                <div class="mb-2">
                                    <?php if($product->old_price && $product->old_price > $product->price): ?>
                                    <span class="text-muted text-decoration-line-through me-2">
                                        <?php echo e(number_format($product->old_price)); ?> FCFA
                                    </span>
                                    <?php endif; ?>
                                    <span class="h5 text-primary mb-0">
                                        <?php echo e(number_format($product->price)); ?> FCFA
                                    </span>
                                </div>
                                
                                <!-- Attributs du produit -->
                                <?php if($product->attributeValues->count() > 0): ?>
                                <div class="mb-2">
                                    <?php $__currentLoopData = $product->attributeValues->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attrValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge bg-light text-dark me-1 mb-1">
                                        <?php echo e($attrValue->attribute->name); ?>: <?php echo e($attrValue->value); ?>

                                    </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($product->attributeValues->count() > 3): ?>
                                    <span class="badge bg-secondary">+<?php echo e($product->attributeValues->count() - 3); ?> autres</span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <?php if($product->rating > 0): ?>
                                            <div class="me-2">
                                                <i class="fas fa-star text-warning"></i>
                                                <small><?php echo e(number_format($product->rating, 1)); ?></small>
                                            </div>
                                            <?php endif; ?>
                                            <small class="text-muted"><?php echo e($product->views_count); ?> vues</small>
                                        </div>
                                        
                                        <a href="<?php echo e(route('product-page', $product->slug)); ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>Voir
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    <?php echo e($products->links('pagination.custom')); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Aucun produit trouvé</h4>
                    <p class="text-muted">Essayez de modifier vos critères de recherche.</p>
                    <a href="<?php echo e(route('boutique_officielle')); ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la boutique
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/products/by-attribute.blade.php ENDPATH**/ ?>