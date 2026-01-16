<?php $__env->startSection('title', 'Détails du Rôle'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Détails du Rôle: <?php echo e($role->name); ?></h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.roles.index')); ?>">Rôles</a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span><?php echo e($role->name); ?></span></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informations du Rôle</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Nom:</strong> <?php echo e($role->name); ?>

                        </li>
                        <li class="list-group-item">
                            <strong>Slug:</strong> <code><?php echo e($role->slug); ?></code>
                        </li>
                        <li class="list-group-item">
                            <strong>Description:</strong>
                            <p class="mb-0"><?php echo e($role->description ?? 'Aucune description'); ?></p>
                        </li>
                        <li class="list-group-item">
                            <strong>Statut:</strong>
                            <?php if($role->is_active): ?>
                                <span class="badge badge-success">Actif</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactif</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item">
                            <strong>Créé le:</strong> <?php echo e($role->created_at->format('d/m/Y H:i')); ?>

                        </li>
                        <li class="list-group-item">
                            <strong>Mis à jour le:</strong> <?php echo e($role->updated_at->format('d/m/Y H:i')); ?>

                        </li>
                    </ul>
                </div>
                <div class="card-footer">
                    <div class="btn-group w-100">
                        <a href="<?php echo e(route('admin.roles.edit', $role)); ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <?php if($role->users->count() == 0): ?>
                            <form action="<?php echo e(route('admin.roles.destroy', $role)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Permissions (<?php echo e($role->permissions->count()); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if($role->permissions->count() > 0): ?>
                        <?php $__currentLoopData = $role->permissions->groupBy('module'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $permissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="mb-3">
                                <h5 class="text-primary">
                                    <i class="fas fa-folder"></i> <?php echo e(ucfirst($module)); ?>

                                    <span class="badge badge-info"><?php echo e($permissions->count()); ?></span>
                                </h5>
                                <div class="row">
                                    <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="alert alert-info mb-2 py-2">
                                                <i class="fas fa-check-circle text-success"></i>
                                                <strong><?php echo e($permission->name); ?></strong>
                                                <?php if($permission->description): ?>
                                                    <br>
                                                    <small class="text-muted"><?php echo e($permission->description); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <p class="text-muted">Aucune permission associée à ce rôle.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Utilisateurs avec ce rôle (<?php echo e($role->users->count()); ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if($role->users->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $role->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($user->id); ?></td>
                                            <td><?php echo e($user->nom); ?> <?php echo e($user->prenoms); ?></td>
                                            <td><?php echo e($user->email); ?></td>
                                            <td>
                                                <?php if($user->is_verified): ?>
                                                    <span class="badge badge-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo e(route('admin.users.show', $user)); ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Aucun utilisateur n'utilise ce rôle actuellement.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/roles/show.blade.php ENDPATH**/ ?>