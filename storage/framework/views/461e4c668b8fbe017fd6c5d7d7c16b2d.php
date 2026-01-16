<?php $__env->startSection('title', 'Détails de l\'Attribut'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-tag me-2"></i>Détails de l'Attribut: <?php echo e($attribute->name); ?>

                    </h4>
                    <div class="btn-group">
                        <a href="<?php echo e(route('admin.attributes.edit', $attribute)); ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                        <a href="<?php echo e(route('admin.attributes.index')); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5>Informations générales</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Nom:</strong></td>
                                        <td><?php echo e($attribute->name); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Slug:</strong></td>
                                        <td><code><?php echo e($attribute->slug); ?></code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Type:</strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($attribute->type === 'select' ? 'primary' : ($attribute->type === 'checkbox' ? 'success' : 'info')); ?>">
                                                <?php echo e(ucfirst($attribute->type)); ?>

                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Filtrable:</strong></td>
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
                                    </tr>
                                    <tr>
                                        <td><strong>Ordre:</strong></td>
                                        <td><span class="badge bg-secondary"><?php echo e($attribute->order); ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Créé le:</strong></td>
                                        <td><?php echo e($attribute->created_at->format('d/m/Y à H:i')); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Modifié le:</strong></td>
                                        <td><?php echo e($attribute->updated_at->format('d/m/Y à H:i')); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5>Valeurs de l'attribut</h5>
                                <?php if($attribute->attributeValues->count() > 0): ?>
                                    <div class="list-group">
                                        <?php $__currentLoopData = $attribute->attributeValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo e($value->value); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo e($value->slug); ?></small>
                                            </div>
                                            <span class="badge bg-info">Ordre: <?php echo e($value->order); ?></span>
                                        </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Aucune valeur définie pour cet attribut.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if($attribute->attributeValues->count() > 0): ?>
                    <div class="row">
                        <div class="col-12">
                            <h5>Statistiques d'utilisation</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h3><?php echo e($attribute->attributeValues->count()); ?></h3>
                                            <p class="mb-0">Valeurs définies</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3>0</h3>
                                            <p class="mb-0">Produits utilisant cet attribut</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h3><?php echo e($attribute->is_filterable ? 'Oui' : 'Non'); ?></h3>
                                            <p class="mb-0">Utilisable comme filtre</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h3><?php echo e(ucfirst($attribute->type)); ?></h3>
                                            <p class="mb-0">Type d'affichage</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/attributes/show.blade.php ENDPATH**/ ?>