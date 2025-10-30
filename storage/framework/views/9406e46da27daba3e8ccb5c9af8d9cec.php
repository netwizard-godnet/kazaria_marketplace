<?php $__env->startSection('title', 'Modifier le Produit'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Modifier le Produit</h4>
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
                <span>Modifier</span>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informations du produit</h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.products.update', $product)); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="form-group">
                            <label for="name">Nom du produit</label>
                            <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="name" name="name" value="<?php echo e(old('name', $product->name)); ?>" required>
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
                            <label for="description">Description</label>
                            <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="description" name="description" rows="4" required><?php echo e(old('description', $product->description)); ?></textarea>
                            <?php $__errorArgs = ['description'];
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price">Prix (FCFA)</label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="price" name="price" value="<?php echo e(old('price', $product->price)); ?>" min="0" step="0.01" required>
                                    <?php $__errorArgs = ['price'];
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
                                <div class="form-group">
                                    <label for="stock">Stock</label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['stock'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="stock" name="stock" value="<?php echo e(old('stock', $product->stock)); ?>" min="0" required>
                                    <?php $__errorArgs = ['stock'];
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
                                <div class="form-group">
                                    <label for="category_id">Catégorie</label>
                                    <select class="form-control <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="category_id" name="category_id" required>
                                        <option value="">Sélectionner une catégorie</option>
                                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($category->id); ?>" <?php echo e(old('category_id', $product->category_id) == $category->id ? 'selected' : ''); ?>>
                                                <?php echo e($category->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['category_id'];
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
                                <div class="form-group">
                                    <label for="status">Statut</label>
                                    <select class="form-control <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="status" name="status" required>
                                        <option value="pending" <?php echo e(old('status', $product->status) == 'pending' ? 'selected' : ''); ?>>En attente</option>
                                        <option value="approved" <?php echo e(old('status', $product->status) == 'approved' ? 'selected' : ''); ?>>Approuvé</option>
                                        <option value="rejected" <?php echo e(old('status', $product->status) == 'rejected' ? 'selected' : ''); ?>>Rejeté</option>
                                    </select>
                                    <?php $__errorArgs = ['status'];
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

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?php echo e(old('is_active', $product->is_active) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="is_active">
                                    Produit actif
                                </label>
                            </div>
                        </div>

                        <!-- Section Images -->
                        <div class="form-group">
                            <label>Images du produit</label>
                            
                            <!-- Images actuelles -->
                            <?php if($product->images && is_array($product->images) && count($product->images) > 0): ?>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6>Images actuelles :</h6>
                                    <div class="row">
                                        <?php $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-3 mb-2">
                                            <div class="card">
                                                <img src="<?php echo e($product->images_urls[$index] ?? asset('storage/' . $image)); ?>" class="card-img-top" alt="Image <?php echo e($index + 1); ?>" style="height: 100px; object-fit: cover;">
                                                <div class="card-body p-2">
                                                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeImage(<?php echo e($index); ?>)">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Upload de nouvelles images -->
                            <div class="row">
                                <div class="col-12">
                                    <h6>Ajouter de nouvelles images :</h6>
                                    <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">
                                    <small class="text-muted">Vous pouvez sélectionner plusieurs images. Formats acceptés: JPG, PNG, GIF. Taille max: 5MB par image.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Section Attributs -->
                        <div class="form-group">
                            <label>Attributs du produit</label>
                            <div class="row">
                                <?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attribute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0"><?php echo e($attribute->name); ?></h6>
                                            <small class="text-muted"><?php echo e(ucfirst($attribute->type)); ?></small>
                                        </div>
                                        <div class="card-body">
                                            <?php if($attribute->type === 'radio'): ?>
                                                <?php $__currentLoopData = $attribute->attributeValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="radio" 
                                                           name="attributes[<?php echo e($attribute->id); ?>]" 
                                                           value="<?php echo e($value->id); ?>" 
                                                           id="attr_<?php echo e($attribute->id); ?>_<?php echo e($value->id); ?>"
                                                           <?php echo e($product->attributeValues->contains($value->id) ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="attr_<?php echo e($attribute->id); ?>_<?php echo e($value->id); ?>">
                                                        <?php echo e($value->value); ?>

                                                    </label>
                                                </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php else: ?>
                                                <?php $__currentLoopData = $attribute->attributeValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           name="attributes[<?php echo e($attribute->id); ?>][]" 
                                                           value="<?php echo e($value->id); ?>" 
                                                           id="attr_<?php echo e($attribute->id); ?>_<?php echo e($value->id); ?>"
                                                           <?php echo e($product->attributeValues->contains($value->id) ? 'checked' : ''); ?>>
                                                    <label class="form-check-label" for="attr_<?php echo e($attribute->id); ?>_<?php echo e($value->id); ?>">
                                                        <?php echo e($value->value); ?>

                                                    </label>
                                                </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php if($attributes->count() === 0): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Aucun attribut disponible. <a href="<?php echo e(route('admin.attributes.create')); ?>">Créer des attributs</a> pour pouvoir les assigner aux produits.
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-action">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Variables pour stocker les images à supprimer
let imagesToRemove = [];

function removeImage(index) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) {
        const imageCard = event.target.closest('.col-md-3');
        const productId = <?php echo e($product->id); ?>;
        
        // Suppression AJAX
        fetch(`/admin/products/${productId}/images/${index}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Masquer l'image visuellement
                imageCard.style.display = 'none';
                // Optionnel: recharger la page pour mettre à jour l'interface
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                alert('Erreur lors de la suppression: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la suppression de l\'image');
        });
    }
}

// Aperçu des nouvelles images
document.getElementById('images').addEventListener('change', function(e) {
    const files = e.target.files;
    const previewContainer = document.createElement('div');
    previewContainer.className = 'row mt-3';
    previewContainer.innerHTML = '<div class="col-12"><h6>Aperçu des nouvelles images :</h6></div>';
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-3 mb-2';
                col.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 100px; object-fit: cover;">
                        <div class="card-body p-2">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    </div>
                `;
                previewContainer.appendChild(col);
            };
            reader.readAsDataURL(file);
        }
    }
    
    // Supprimer l'ancien aperçu s'il existe
    const oldPreview = document.querySelector('.preview-container');
    if (oldPreview) {
        oldPreview.remove();
    }
    
    previewContainer.className += ' preview-container';
    document.querySelector('.form-group:last-of-type').appendChild(previewContainer);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/admin/products/edit.blade.php ENDPATH**/ ?>