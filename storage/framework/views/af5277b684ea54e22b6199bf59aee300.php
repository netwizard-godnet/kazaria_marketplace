

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
                            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if(session('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if(session('warning')): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo e(session('warning')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                                            <img src="<?php echo e($subcategory->image_url); ?>" alt="<?php echo e($subcategory->name); ?>" 
                                                 class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"
                                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'bg-light d-flex align-items-center justify-content-center\' style=\'width: 50px; height: 50px; border-radius: 4px;\'><i class=\'fas fa-image text-muted\'></i></div>';">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px; border-radius: 4px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($subcategory->name); ?></td>
                                    <td><?php echo e($subcategory->category->name ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo e($subcategory->is_active ? 'success' : 'danger'); ?>">
                                            <i class="fas fa-<?php echo e($subcategory->is_active ? 'eye' : 'eye-slash'); ?>"></i>
                                            <?php echo e($subcategory->is_active ? 'Visible' : 'Masquée'); ?>

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
                                                <button type="submit" class="btn btn-<?php echo e($subcategory->is_active ? 'warning' : 'success'); ?> btn-sm" 
                                                        title="<?php echo e($subcategory->is_active ? 'Masquer sur le site' : 'Afficher sur le site'); ?>"
                                                        onclick="return confirm('<?php echo e($subcategory->is_active ? 'Masquer' : 'Afficher'); ?> cette sous-catégorie sur le site ?')">
                                                    <i class="fas fa-<?php echo e($subcategory->is_active ? 'eye-slash' : 'eye'); ?>"></i>
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
                    <?php if($subcategories->hasPages()): ?>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Affichage de <?php echo e($subcategories->firstItem()); ?> à <?php echo e($subcategories->lastItem()); ?> sur <?php echo e($subcategories->total()); ?> sous-catégories
                        </div>
                        <div class="pagination-wrapper">
                            <?php echo e($subcategories->appends(request()->query())->links('pagination.bootstrap-4')); ?>

                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Styles pour la pagination */
.pagination-wrapper {
    margin-top: 0;
}

.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    color: #007bff;
    background-color: #fff;
    border: 1px solid #dee2e6;
    padding: 0.5rem 0.75rem;
    margin: 0 2px;
    border-radius: 4px;
    transition: all 0.3s ease;
    font-weight: 500;
    min-width: 40px;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.pagination .page-link:hover {
    color: #0056b3;
    background-color: #e9ecef;
    border-color: #adb5bd;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: #fff;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(0,123,255,0.3);
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
    cursor: not-allowed;
    opacity: 0.6;
}

.pagination .page-item.disabled .page-link:hover {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
    transform: none;
    box-shadow: none;
}

/* Responsive pour la pagination */
@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
        align-items: center !important;
    }
    
    .pagination-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
    }
    
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .pagination .page-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
        min-width: 35px;
    }
}

/* Amélioration du tableau */
.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    vertical-align: middle;
}

/* Amélioration des boutons d'action */
.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Amélioration des badges */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
}

.badge-success {
    background-color: #28a745;
    color: #fff;
}

.badge-danger {
    background-color: #dc3545;
    color: #fff;
}

/* Amélioration du tableau */
.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,.02);
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}

/* Styles pour les alertes */
.alert {
    margin-bottom: 1rem;
    border-radius: 0.375rem;
    padding: 0.75rem 1rem;
}

.alert .btn-close {
    padding: 0.5rem 0.75rem;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier et afficher les messages de session
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(function(alert) {
        // S'assurer que l'alerte est visible
        alert.style.display = 'block';
        alert.style.opacity = '1';
        
        // Gérer la fermeture pour Bootstrap 4 et 5
        const closeButton = alert.querySelector('.btn-close, .close');
        if (closeButton) {
            closeButton.addEventListener('click', function(e) {
                e.preventDefault();
                alert.style.transition = 'opacity 0.15s linear';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 150);
            });
        }
    });
    
    // Auto-fermer les alertes après 5 secondes
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert && alert.parentNode) {
                alert.style.transition = 'opacity 0.15s linear';
                alert.style.opacity = '0';
                setTimeout(function() {
                    if (alert && alert.parentNode) {
                        alert.remove();
                    }
                }, 150);
            }
        }, 5000);
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\subcategories\index.blade.php ENDPATH**/ ?>