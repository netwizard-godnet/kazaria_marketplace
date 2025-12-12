

<?php $__env->startSection('content'); ?>
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold blue-color">Black Friday KAZARIA</h1>
        <p class="lead text-muted">Des offres incroyables sur toute la marketplace. Profitez-en avant qu'il ne soit trop tard !</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <h5 class="card-title text-uppercase text-muted">Jusqu'à</h5>
                    <h2 class="display-4 fw-bold orange-color">-60%</h2>
                    <p class="text-muted">Sur une sélection d'électronique, smartphones et accessoires.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <h5 class="card-title text-uppercase text-muted">Livraison</h5>
                    <h2 class="display-6 fw-bold blue-color">Gratuite</h2>
                    <p class="text-muted">Sur toutes vos commandes pendant la période Black Friday.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">
                    <h5 class="card-title text-uppercase text-muted">Paiement</h5>
                    <h2 class="display-6 fw-bold blue-color">100% sécurisé</h2>
                    <p class="text-muted">Payez en toute confiance avec nos partenaires certifiés.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <a href="<?php echo e(route('accueil')); ?>" class="btn orange-bg text-white btn-lg px-4">
            Découvrir toutes les offres
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\blackfriday.blade.php ENDPATH**/ ?>