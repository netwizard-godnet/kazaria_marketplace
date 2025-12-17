

<?php $__env->startSection('title', 'Gestion des Rôles et Permissions'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Gestion des Rôles et Permissions</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Rôles</span></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Liste des Rôles</h3>
                    <div class="card-tools">
                        <a href="<?php echo e(route('admin.roles.create')); ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nouveau Rôle
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Permissions</th>
                                    <th>Statut</th>
                                    <th>Utilisateurs</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($role->id); ?></td>
                                    <td>
                                        <strong><?php echo e($role->name); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo e($role->slug); ?></small>
                                    </td>
                                    <td><?php echo e($role->description ?? 'Aucune description'); ?></td>
                                    <td>
                                        <?php if($role->permissions->count() > 0): ?>
                                            <span class="badge badge-info"><?php echo e($role->permissions->count()); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($role->is_active): ?>
                                            <span class="badge badge-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo e($role->users->count()); ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo e(route('admin.roles.show', $role)); ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('admin.roles.edit', $role)); ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if($role->users->count() == 0): ?>
                                                <form action="<?php echo e(route('admin.roles.destroy', $role)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-danger btn-sm" disabled title="Impossible de supprimer : utilisateurs associés">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Aucun rôle trouvé</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(session('success')): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert('<?php echo e(session('success')); ?>');
    });
</script>
<?php endif; ?>

<?php if(session('error')): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert('<?php echo e(session('error')); ?>');
    });
</script>
<?php endif; ?>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\roles\index.blade.php ENDPATH**/ ?>