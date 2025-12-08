<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <?php if($success): ?>
                            <div class="mb-3">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="fw-bold text-success mb-3"><?php echo e($title); ?></h2>
                        <?php else: ?>
                            <div class="mb-3">
                                <i class="bi bi-x-circle-fill text-danger" style="font-size: 4rem;"></i>
                            </div>
                            <h2 class="fw-bold text-danger mb-3"><?php echo e($title); ?></h2>
                        <?php endif; ?>
                    </div>

                    <div class="text-center mb-4">
                        <p class="lead text-muted"><?php echo e($message); ?></p>
                        
                        <?php if($success && isset($user)): ?>
                            <div class="alert alert-success border-0 bg-light">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-circle me-2"></i>
                                    <div>
                                        <strong><?php echo e($user->nom); ?> <?php echo e($user->prenoms); ?></strong><br>
                                        <small class="text-muted"><?php echo e($user->email); ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="text-center">
                        <?php if($success): ?>
                            <div class="mb-3">
                                <p class="text-muted">Vous pouvez maintenant vous connecter à votre compte.</p>
                            </div>
                            <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Se connecter
                            </a>
                        <?php else: ?>
                            <div class="mb-3">
                                <p class="text-muted">Le lien de vérification n'est plus valide. Veuillez demander un nouveau lien.</p>
                            </div>
                            <a href="<?php echo e(route('register')); ?>" class="btn btn-outline-primary btn-lg px-4 me-2">
                                <i class="bi bi-person-plus me-2"></i>
                                Créer un compte
                            </a>
                            <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-lg px-4">
                                <i class="bi bi-box-arrow-in-right me-2"></i>
                                Se connecter
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="text-center mt-4">
                        <a href="<?php echo e(route('accueil')); ?>" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>

            <?php if($success): ?>
            <div class="text-center mt-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-shield-check text-success mb-2" style="font-size: 2rem;"></i>
                                <h6 class="card-title">Compte sécurisé</h6>
                                <small class="text-muted">Votre compte est maintenant vérifié</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-cart-check text-primary mb-2" style="font-size: 2rem;"></i>
                                <h6 class="card-title">Achetez en ligne</h6>
                                <small class="text-muted">Commencez vos achats dès maintenant</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <i class="bi bi-heart text-danger mb-2" style="font-size: 2rem;"></i>
                                <h6 class="card-title">Favoris</h6>
                                <small class="text-muted">Sauvegardez vos produits préférés</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 1rem;
}

.btn {
    border-radius: 0.5rem;
}

.alert {
    border-radius: 0.75rem;
}

.bi {
    vertical-align: -0.125em;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/auth/email-verification.blade.php ENDPATH**/ ?>