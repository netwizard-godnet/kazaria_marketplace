

<?php $__env->startSection('title', 'Modifier l\'Attribut'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Modifier l'Attribut: <?php echo e($attribute->name); ?>

                    </h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.attributes.update', $attribute)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom de l'attribut <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="name" 
                                           name="name" 
                                           value="<?php echo e(old('name', $attribute->name)); ?>" 
                                           placeholder="Ex: Couleur, Taille, Marque"
                                           required>
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
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type d'attribut <span class="text-danger">*</span></label>
                                    <select class="form-select <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="type" 
                                            name="type" 
                                            required>
                                        <option value="">Sélectionner un type</option>
                                        <option value="select" <?php echo e(old('type', $attribute->type) === 'select' ? 'selected' : ''); ?>>Liste déroulante</option>
                                        <option value="checkbox" <?php echo e(old('type', $attribute->type) === 'checkbox' ? 'selected' : ''); ?>>Cases à cocher</option>
                                        <option value="radio" <?php echo e(old('type', $attribute->type) === 'radio' ? 'selected' : ''); ?>>Boutons radio</option>
                                    </select>
                                    <?php $__errorArgs = ['type'];
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
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order" class="form-label">Ordre d'affichage</label>
                                    <input type="number" 
                                           class="form-control <?php $__errorArgs = ['order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="order" 
                                           name="order" 
                                           value="<?php echo e(old('order', $attribute->order)); ?>" 
                                           min="0">
                                    <?php $__errorArgs = ['order'];
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
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_filterable" 
                                               name="is_filterable" 
                                               value="1" 
                                               <?php echo e(old('is_filterable', $attribute->is_filterable) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_filterable">
                                            Utilisable comme filtre
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Permet aux clients de filtrer les produits par cet attribut
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Valeurs de l'attribut</label>
                            <div id="values-container">
                                <?php if($attribute->attributeValues->count() > 0): ?>
                                    <?php $__currentLoopData = $attribute->attributeValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="input-group mb-2">
                                        <input type="text" 
                                               class="form-control" 
                                               name="values[]" 
                                               value="<?php echo e($value->value); ?>"
                                               placeholder="Ex: Rouge, Bleu, Vert">
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="removeValue(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php else: ?>
                                    <div class="input-group mb-2">
                                        <input type="text" 
                                               class="form-control" 
                                               name="values[]" 
                                               placeholder="Ex: Rouge, Bleu, Vert">
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="removeValue(this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <button type="button" 
                                    class="btn btn-outline-primary btn-sm" 
                                    onclick="addValue()">
                                <i class="fas fa-plus me-2"></i>Ajouter une valeur
                            </button>
                            <small class="form-text text-muted d-block mt-2">
                                Ajoutez les valeurs possibles pour cet attribut (ex: Rouge, Bleu, Vert pour Couleur)
                            </small>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo e(route('admin.attributes.index')); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addValue() {
    const container = document.getElementById('values-container');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" class="form-control" name="values[]" placeholder="Ex: Rouge, Bleu, Vert">
        <button type="button" class="btn btn-outline-danger" onclick="removeValue(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    container.appendChild(div);
}

function removeValue(button) {
    button.parentElement.remove();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\attributes\edit.blade.php ENDPATH**/ ?>