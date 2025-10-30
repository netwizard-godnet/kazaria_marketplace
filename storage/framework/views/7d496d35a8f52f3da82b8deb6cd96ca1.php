
<?php $__env->startSection('title', 'Ajouter un produit'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Ajouter un produit</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="<?php echo e(route('admin.products.index')); ?>">Produits</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Ajouter</span></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header"><h3>Ajouter un produit</h3></div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('admin.products.store')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="form-group mb-3">
                            <label>Nom</label>
                            <input type="text" name="name" class="form-control" required autofocus>
                        </div>
                        <div class="form-group mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group mb-3">
                            <label>Prix</label>
                            <input type="number" name="price" class="form-control" required min="0">
                        </div>
                        <div class="form-group mb-3">
                            <label>Stock</label>
                            <input type="number" name="stock" class="form-control" required min="0">
                        </div>
                        <div class="form-group mb-3">
                            <label>Catégorie</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Choisir une catégorie --</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cat->id); ?>"><?php echo e($cat->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label>Images</label>
                            <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-success">Enregistrer</button>
                        <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-link">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/admin/products/create.blade.php ENDPATH**/ ?>