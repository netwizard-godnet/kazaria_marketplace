

<?php $__env->startSection('title', 'Détails du Produit'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Détails du Produit</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.products.index')); ?>">Produits</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span>Détails</span>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title"><?php echo e($product->name); ?></h4>
                        <div class="btn-group">
                            <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informations générales</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td><?php echo e($product->id); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Nom:</strong></td>
                                    <td><?php echo e($product->name); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Slug:</strong></td>
                                    <td><?php echo e($product->slug); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Prix:</strong></td>
                                    <td><?php echo e(number_format($product->price, 0, ',', ' ')); ?> FCFA</td>
                                </tr>
                                <?php if($product->old_price): ?>
                                <tr>
                                    <td><strong>Ancien prix:</strong></td>
                                    <td><?php echo e(number_format($product->old_price, 0, ',', ' ')); ?> FCFA</td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td><strong>Stock:</strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo e($product->stock > 0 ? 'success' : 'danger'); ?>">
                                            <?php echo e($product->stock); ?>

                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Statut:</strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo e($product->is_active ? 'success' : 'danger'); ?>">
                                            <?php echo e($product->is_active ? 'Actif' : 'Inactif'); ?>

                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Catégorisation</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Catégorie:</strong></td>
                                    <td><?php echo e($product->category->name ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Sous-catégorie:</strong></td>
                                    <td><?php echo e($product->subcategory->name ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Boutique:</strong></td>
                                    <td><?php echo e($product->store->name ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Marque:</strong></td>
                                    <td><?php echo e($product->brand ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Modèle:</strong></td>
                                    <td><?php echo e($product->model ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Garantie:</strong></td>
                                    <td><?php echo e($product->warranty ?? 'N/A'); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Description</h6>
                            <p class="text-muted"><?php echo e($product->description ?? 'Aucune description'); ?></p>
                        </div>
                    </div>

                    <?php if($product->images && is_array($product->images) && count($product->images) > 0): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Images (<?php echo e(count($product->images)); ?>)</h6>
                            <div class="row">
                                <?php
                                    $imagesUrls = $product->images_urls ?? [];
                                ?>
                                <?php $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <img src="<?php echo e($imagesUrls[$index] ?? asset('storage/' . $image)); ?>" class="card-img-top" alt="Image <?php echo e($index + 1); ?>" style="height: 150px; object-fit: cover; cursor: pointer;" onclick="window.open(this.src, '_blank')">
                                        <div class="card-body p-2">
                                            <small class="text-muted">Image <?php echo e($index + 1); ?></small>
                                            <?php if($index === 0): ?>
                                                <span class="badge badge-primary ml-1">Principale</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>Statistiques</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Note:</strong></td>
                                    <td>
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa-solid fa-star <?php echo e($i <= floor($product->rating) ? 'text-warning' : 'text-secondary'); ?>"></i>
                                        <?php endfor; ?>
                                        (<?php echo e($product->rating ?? 0); ?>)
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Nombre d'avis:</strong></td>
                                    <td><?php echo e($product->reviews_count ?? 0); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Vues:</strong></td>
                                    <td><?php echo e($product->views_count ?? 0); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Dates</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Créé le:</strong></td>
                                    <td><?php echo e($product->created_at->format('d/m/Y H:i')); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Modifié le:</strong></td>
                                    <td><?php echo e($product->updated_at->format('d/m/Y H:i')); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if(isset($product->status)): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Statut d'approbation</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Statut:</strong></td>
                                    <td>
                                        <?php if($product->status == 'approved'): ?>
                                            <span class="badge badge-success">Approuvé</span>
                                        <?php elseif($product->status == 'rejected'): ?>
                                            <span class="badge badge-danger">Rejeté</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">En attente</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($product->is_featured || $product->is_trending || $product->is_new || $product->is_best_offer): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Badges et labels</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if($product->is_featured): ?>
                                    <span class="badge badge-primary"><i class="fas fa-star"></i> Produit vedette</span>
                                <?php endif; ?>
                                <?php if($product->is_trending): ?>
                                    <span class="badge badge-info"><i class="fas fa-fire"></i> Tendance</span>
                                <?php endif; ?>
                                <?php if($product->is_new): ?>
                                    <span class="badge badge-success"><i class="fas fa-tag"></i> Nouveau</span>
                                <?php endif; ?>
                                <?php if($product->is_best_offer): ?>
                                    <span class="badge badge-warning"><i class="fas fa-percent"></i> Meilleure offre</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($product->tags && count($product->tags) > 0): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Tags</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <?php $__currentLoopData = $product->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge badge-secondary"><?php echo e($tag); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($product->attributeValues && $product->attributeValues->count() > 0): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Attributs</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Attribut</th>
                                            <th>Valeur(s)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $attributesGrouped = $product->attributeValues->groupBy(function($item) {
                                                return $item->attribute->name ?? 'Autres';
                                            });
                                        ?>
                                        <?php $__currentLoopData = $attributesGrouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attributeName => $values): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td><strong><?php echo e($attributeName); ?></strong></td>
                                                <td>
                                                    <?php $__currentLoopData = $values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <span class="badge badge-info mr-1"><?php echo e($value->value); ?></span>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($product->variations && $product->variations->count() > 0): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Variations du produit</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>SKU</th>
                                            <th>Attributs</th>
                                            <th>Prix</th>
                                            <th>Ancien prix</th>
                                            <th>Stock</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $product->variations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="<?php echo e($variation->is_default ? 'table-warning' : ''); ?>">
                                                <td>
                                                    <?php echo e($variation->sku ?? 'N/A'); ?>

                                                    <?php if($variation->is_default): ?>
                                                        <span class="badge badge-warning ml-1">Par défaut</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($variation->attributeValues && $variation->attributeValues->count() > 0): ?>
                                                        <?php
                                                            $variationAttributesGrouped = $variation->attributeValues->groupBy(function($item) {
                                                                return $item->attribute->name ?? 'Autres';
                                                            });
                                                        ?>
                                                        <?php $__currentLoopData = $variationAttributesGrouped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attrName => $values): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <strong><?php echo e($attrName); ?>:</strong>
                                                            <?php $__currentLoopData = $values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <span class="badge badge-secondary"><?php echo e($value->value); ?></span>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            <?php if(!$loop->last): ?> <br> <?php endif; ?>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Aucun attribut</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo e(number_format($variation->price, 0, ',', ' ')); ?> FCFA</strong>
                                                    <?php if($variation->old_price && $variation->old_price > $variation->price): ?>
                                                        <br><small class="text-danger">Promo: <?php echo e(number_format($variation->price, 0, ',', ' ')); ?> FCFA</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($variation->old_price && $variation->old_price > $variation->price): ?>
                                                        <span class="text-muted"><s><?php echo e(number_format($variation->old_price, 0, ',', ' ')); ?> FCFA</s></span>
                                                        <?php if($variation->discount_percentage): ?>
                                                            <br><small class="text-success">-<?php echo e($variation->discount_percentage); ?>%</small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo e($variation->stock > 0 ? 'success' : 'danger'); ?>">
                                                        <?php echo e($variation->stock); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo e($variation->is_active ? 'success' : 'danger'); ?>">
                                                        <?php echo e($variation->is_active ? 'Actif' : 'Inactif'); ?>

                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if($product->meta_description || $product->meta_keywords): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Métadonnées SEO</h6>
                            <table class="table table-borderless">
                                <?php if($product->meta_description): ?>
                                <tr>
                                    <td><strong>Meta description:</strong></td>
                                    <td><?php echo e($product->meta_description); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if($product->meta_keywords): ?>
                                <tr>
                                    <td><strong>Meta keywords:</strong></td>
                                    <td><?php echo e($product->meta_keywords); ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('admin.products.edit', $product)); ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Modifier le produit
                        </a>
                        
                        <?php if(isset($product->status)): ?>
                            <?php if($product->status == 'pending'): ?>
                                <form action="<?php echo e(route('admin.products.approve', $product)); ?>" method="POST" class="d-grid">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Approuver ce produit ?')">
                                        <i class="fas fa-check-circle"></i> Approuver
                                    </button>
                                </form>
                                
                                <form action="<?php echo e(route('admin.products.reject', $product)); ?>" method="POST" class="d-grid">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Rejeter ce produit ?')">
                                        <i class="fas fa-times-circle"></i> Rejeter
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <form action="<?php echo e(route('admin.products.toggle-status', $product)); ?>" method="POST" class="d-grid">
                            <?php echo csrf_field(); ?>
                            <?php if($product->is_active): ?>
                                <button type="submit" class="btn btn-secondary" onclick="return confirm('Êtes-vous sûr de vouloir désactiver ce produit ?')">
                                    <i class="fas fa-ban"></i> Désactiver
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-success" onclick="return confirm('Êtes-vous sûr de vouloir activer ce produit ?')">
                                    <i class="fas fa-check"></i> Activer
                                </button>
                            <?php endif; ?>
                        </form>
                        
                        <form action="<?php echo e(route('admin.products.destroy', $product)); ?>" method="POST" class="d-grid">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ? Cette action est irréversible.')">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <?php if($product->store): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title">Informations boutique</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nom:</strong> <?php echo e($product->store->name); ?></p>
                    <p><strong>Description:</strong> <?php echo e(Str::limit($product->store->description ?? 'N/A', 100)); ?></p>
                    <p><strong>Statut:</strong> 
                        <?php
                            $storeStatus = $product->store->effective_kyc_status ?? $product->store->status ?? 'pending';
                            $statusLabels = [
                                'active' => ['label' => 'Actif', 'class' => 'success'],
                                'pending' => ['label' => 'En attente', 'class' => 'warning'],
                                'suspended' => ['label' => 'Suspendu', 'class' => 'danger'],
                                'rejected' => ['label' => 'Rejeté', 'class' => 'danger'],
                                'validated' => ['label' => 'Validé', 'class' => 'success'],
                                'approved' => ['label' => 'Approuvé', 'class' => 'success'],
                                'approve' => ['label' => 'Approuvé', 'class' => 'success'],
                            ];
                            $statusInfo = $statusLabels[strtolower($storeStatus)] ?? ['label' => ucfirst($storeStatus), 'class' => 'secondary'];
                        ?>
                        <span class="badge badge-<?php echo e($statusInfo['class']); ?>">
                            <?php echo e($statusInfo['label']); ?>

                        </span>
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\products\show.blade.php ENDPATH**/ ?>