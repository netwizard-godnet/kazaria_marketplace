

<?php $__env->startSection('title', 'Détails de la Sous-catégorie'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Détails de la Sous-catégorie</h4>
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
                <a href="<?php echo e(route('admin.subcategories.index')); ?>">Sous-catégories</a>
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
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="card-title"><?php echo e($subcategory->name); ?></h4>
                        <div class="btn-group">
                            <a href="<?php echo e(route('admin.subcategories.edit', $subcategory)); ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <form action="<?php echo e(route('admin.subcategories.toggle-status', $subcategory)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-<?php echo e($subcategory->is_active ? 'secondary' : 'success'); ?> btn-sm" 
                                        onclick="return confirm('<?php echo e($subcategory->is_active ? 'Désactiver' : 'Activer'); ?> cette sous-catégorie ?')">
                                    <i class="fas fa-<?php echo e($subcategory->is_active ? 'pause' : 'play'); ?>"></i> 
                                    <?php echo e($subcategory->is_active ? 'Désactiver' : 'Activer'); ?>

                                </button>
                            </form>
                            <form action="<?php echo e(route('admin.subcategories.destroy', $subcategory)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Supprimer cette sous-catégorie ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?php if($subcategory->image): ?>
                                <img src="<?php echo e($subcategory->image_url); ?>" alt="<?php echo e($subcategory->name); ?>" 
                                     class="img-fluid rounded">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" 
                                     style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5>Informations générales</h5>
                            <ul class="list-unstyled">
                                <li><strong>ID :</strong> <?php echo e($subcategory->id); ?></li>
                                <li><strong>Nom :</strong> <?php echo e($subcategory->name); ?></li>
                                <li><strong>Slug :</strong> <?php echo e($subcategory->slug); ?></li>
                                <li><strong>Catégorie :</strong> <?php echo e($subcategory->category->name ?? 'N/A'); ?></li>
                                <li><strong>Statut :</strong> 
                                    <span class="badge badge-<?php echo e($subcategory->is_active ? 'success' : 'danger'); ?>">
                                        <?php echo e($subcategory->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </li>
                                <li><strong>Ordre :</strong> <?php echo e($subcategory->order ?? 0); ?></li>
                                <li><strong>Créée le :</strong> <?php echo e($subcategory->created_at->format('d/m/Y H:i')); ?></li>
                                <li><strong>Modifiée le :</strong> <?php echo e($subcategory->updated_at->format('d/m/Y H:i')); ?></li>
                            </ul>
                            
                            <?php if($subcategory->description): ?>
                                <h5>Description</h5>
                                <p><?php echo e($subcategory->description); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\subcategories\show.blade.php ENDPATH**/ ?>