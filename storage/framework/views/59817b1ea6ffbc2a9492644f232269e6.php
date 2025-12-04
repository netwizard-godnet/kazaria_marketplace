

<?php $__env->startSection('title', 'Gestion des Attributs'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-tags me-2"></i>Gestion des Attributs
                    </h4>
                    <a href="<?php echo e(route('admin.attributes.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Ajouter un Attribut
                    </a>
                </div>
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if($attributes->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Filtrable</th>
                                        <th>Valeurs</th>
                                        <th>Ordre</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($attribute->name); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo e($attribute->slug); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo e($attribute->type === 'select' ? 'primary' : ($attribute->type === 'checkbox' ? 'success' : 'info')); ?>">
                                                <?php echo e(ucfirst($attribute->type)); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <?php if($attribute->is_filterable): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Oui
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times"></i> Non
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo e($attribute->attributeValues->count()); ?> valeurs</span>
                                            <?php if($attribute->attributeValues->count() > 0): ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo e($attribute->attributeValues->take(3)->pluck('value')->join(', ')); ?>

                                                    <?php if($attribute->attributeValues->count() > 3): ?>
                                                        ...
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo e($attribute->order); ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="<?php echo e(route('admin.attributes.show', $attribute)); ?>" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo e(route('admin.attributes.edit', $attribute)); ?>" 
                                                   class="btn btn-sm btn-outline-warning" 
                                                   title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="<?php echo e(route('admin.attributes.destroy', $attribute)); ?>" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet attribut ?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun attribut trouvé</h5>
                            <p class="text-muted">Commencez par créer votre premier attribut.</p>
                            <a href="<?php echo e(route('admin.attributes.create')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer un Attribut
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\attributes\index.blade.php ENDPATH**/ ?>