<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <?php if(Auth::user()->avatar): ?>
                        <img src="<?php echo e(Auth::user()->avatar_url); ?>" alt="Avatar" class="img-fluid rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                            <i class="bi bi-person" style="font-size: 3rem; color: #6c757d;"></i>
                        </div>
                    <?php endif; ?>
                    <h5 class="card-title"><?php echo e(Auth::user()->name); ?></h5>
                    <p class="text-muted"><?php echo e(Auth::user()->email); ?></p>
                    <?php if(Auth::user()->is_seller): ?>
                        <span class="badge bg-success">Vendeur</span>
                    <?php else: ?>
                        <span class="badge bg-primary">Client</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informations du profil</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('profile.update')); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo e(Auth::user()->name); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo e(Auth::user()->email); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo e(Auth::user()->phone); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="birth_date" class="form-label">Date de naissance</label>
                                    <input type="date" class="form-control" id="birth_date" name="birth_date" value="<?php echo e(Auth::user()->birth_date); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo e(Auth::user()->address); ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="<?php echo e(route('store.dashboard')); ?>" class="btn btn-outline-primary">
                                <?php if(Auth::user()->is_seller && Auth::user()->store): ?>
                                    Ma boutique
                                <?php elseif(Auth::user()->is_seller): ?>
                                    Créer ma boutique
                                <?php else: ?>
                                    Devenir vendeur
                                <?php endif; ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/profile/index.blade.php ENDPATH**/ ?>