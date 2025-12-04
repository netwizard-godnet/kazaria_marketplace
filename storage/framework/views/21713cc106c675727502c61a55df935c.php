<?php $__env->startSection('title', 'Gestion des Marques'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Gestion des Marques</h4>
        <p class="text-muted">Gérez les marques affichées sur la page d'accueil (maximum 12 marques, 2 lignes de 6)</p>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6 class="mb-2"><i class="fas fa-exclamation-triangle mr-1"></i>Une ou plusieurs erreurs sont survenues :</h6>
            <ul class="mb-0 pl-3">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Ajouter une marque</h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.brands.store')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label>Nom de la marque</label>
                            <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Ex: Samsung" value="<?php echo e(old('name')); ?>">
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="form-group">
                            <label>Image de la marque <span class="text-danger">*</span></label>
                            <input type="file" name="image" class="form-control <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required accept="image/*">
                            <small class="text-muted">Tous formats acceptés, aucune limite de taille (optimisez vos fichiers).</small>
                            <?php $__errorArgs = ['image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="form-group">
                            <label>Lien (optionnel)</label>
                            <input type="url" name="link_url" class="form-control <?php $__errorArgs = ['link_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="https://..." value="<?php echo e(old('link_url')); ?>">
                            <small class="text-muted">Lien vers lequel la marque redirigera.</small>
                            <?php $__errorArgs = ['link_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="form-row">
                            <div class="col">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="sort_order" class="form-control" value="<?php echo e(old('sort_order', 0)); ?>" min="0">
                            </div>
                            <div class="col">
                                <label>Statut</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" <?php echo e(old('is_active', '1') == '1' ? 'selected' : ''); ?>>Actif</option>
                                    <option value="0" <?php echo e(old('is_active') == '0' ? 'selected' : ''); ?>>Inactif</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="fas fa-plus"></i> Ajouter la marque
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Liste des marques (<?php echo e($brands->count()); ?>/12)</h4>
                </div>
                <div class="card-body">
                    <?php if($brands->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Nom</th>
                                        <th>Lien</th>
                                        <th>Ordre</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <?php if($brand->image_url): ?>
                                                    <img src="<?php echo e($brand->image_url); ?>" alt="<?php echo e($brand->name); ?>" style="max-width: 80px; max-height: 50px; object-fit: contain;">
                                                <?php else: ?>
                                                    <span class="text-muted">Aucune image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if(filled($brand->name)): ?>
                                                    <strong><?php echo e($brand->name); ?></strong>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($brand->link_url): ?>
                                                    <a href="<?php echo e($brand->link_url); ?>" target="_blank" class="text-primary">
                                                        <i class="fas fa-external-link-alt"></i> Voir
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Aucun lien</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($brand->sort_order); ?></td>
                                            <td>
                                                <?php if($brand->is_active): ?>
                                                    <span class="badge badge-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info btn-edit-brand" data-toggle="modal" data-target="#editBrandModal<?php echo e($brand->id); ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="<?php echo e(route('admin.brands.destroy', $brand)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette marque ?');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune marque ajoutée pour le moment</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="editBrandModal<?php echo e($brand->id); ?>" tabindex="-1" aria-labelledby="editBrandLabel<?php echo e($brand->id); ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBrandLabel<?php echo e($brand->id); ?>">Modifier la marque</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="<?php echo e(route('admin.brands.update', $brand)); ?>" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nom de la marque</label>
                            <input type="text" name="name" class="form-control" value="<?php echo e($brand->name); ?>">
                        </div>
                        <div class="form-group">
                            <label>Image actuelle</label>
                            <?php if($brand->image_url): ?>
                                <div class="mb-2">
                                    <img src="<?php echo e($brand->image_url); ?>" alt="<?php echo e($brand->name); ?>" style="max-width: 200px; max-height: 100px; object-fit: contain;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Laisser vide pour conserver l'image actuelle.</small>
                        </div>
                        <div class="form-group">
                            <label>Lien (optionnel)</label>
                            <input type="url" name="link_url" class="form-control" value="<?php echo e($brand->link_url); ?>" placeholder="https://...">
                        </div>
                        <div class="form-row">
                            <div class="col">
                                <label>Ordre d'affichage</label>
                                <input type="number" name="sort_order" class="form-control" value="<?php echo e($brand->sort_order); ?>" min="0">
                            </div>
                            <div class="col">
                                <label>Statut</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" <?php echo e($brand->is_active ? 'selected' : ''); ?>>Actif</option>
                                    <option value="0" <?php echo e(!$brand->is_active ? 'selected' : ''); ?>>Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-edit-brand').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const targetSelector = this.getAttribute('data-target');

                if (!targetSelector) {
                    return;
                }

                const modal = document.querySelector(targetSelector);

                if (!modal) {
                    return;
                }

                if (typeof window.$ === 'function' && typeof window.$.fn.modal === 'function') {
                    window.$(modal).modal('show');
                } else if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
                    const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                    bsModal.show();
                }
            });
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\brands\index.blade.php ENDPATH**/ ?>