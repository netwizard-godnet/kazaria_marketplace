

<?php $__env->startSection('title', 'Gestion des Sous-catégories'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Sous-catégories</h4>
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
                <span>Sous-catégories</span>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="card-title">Liste des Sous-catégories</h4>
                        <a href="<?php echo e(route('admin.subcategories.create')); ?>" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nouvelle Sous-catégorie
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="<?php echo e(route('admin.subcategories.index')); ?>" class="row g-3">
                                <div class="col-md-3">
                                    <input type="text" class="form-control" name="search" placeholder="Rechercher par nom..." value="<?php echo e(request('search')); ?>">
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="category">
                                        <option value="">Toutes les catégories</option>
                                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($category->id); ?>" <?php echo e(request('category') == $category->id ? 'selected' : ''); ?>>
                                                <?php echo e($category->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" name="status">
                                        <option value="">Tous les statuts</option>
                                        <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Actives</option>
                                        <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>>Inactives</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i> Filtrer
                                    </button>
                                </div>
                                <div class="col-md-2">
                                    <a href="<?php echo e(route('admin.subcategories.index')); ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Effacer
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Messages -->
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

                    <!-- Tableau -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Statut</th>
                                    <th>Ordre</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($subcategory->id); ?></td>
                                    <td>
                                        <?php if($subcategory->image): ?>
                                            <img src="<?php echo e(Storage::url($subcategory->image)); ?>" alt="<?php echo e($subcategory->name); ?>" 
                                                 class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($subcategory->name); ?></td>
                                    <td><?php echo e($subcategory->category->name ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo e($subcategory->is_active ? 'success' : 'danger'); ?>">
                                            <?php echo e($subcategory->is_active ? 'Active' : 'Inactive'); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($subcategory->order ?? 0); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo e(route('admin.subcategories.show', $subcategory)); ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('admin.subcategories.edit', $subcategory)); ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="<?php echo e(route('admin.subcategories.toggle-status', $subcategory)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-<?php echo e($subcategory->is_active ? 'secondary' : 'success'); ?> btn-sm" 
                                                        onclick="return confirm('<?php echo e($subcategory->is_active ? 'Désactiver' : 'Activer'); ?> cette sous-catégorie ?')">
                                                    <i class="fas fa-<?php echo e($subcategory->is_active ? 'pause' : 'play'); ?>"></i>
                                                </button>
                                            </form>
                                            <form action="<?php echo e(route('admin.subcategories.destroy', $subcategory)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Supprimer cette sous-catégorie ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Aucune sous-catégorie trouvée</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        <?php echo e($subcategories->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\subcategories\index.blade.php ENDPATH**/ ?>