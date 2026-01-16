<?php $__env->startSection('content'); ?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <!-- Icône d'erreur -->
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    
                    <!-- Titre -->
                    <h2 class="text-danger mb-3">Boutique Rejetée</h2>
                    
                    <!-- Message principal -->
                    <p class="lead text-muted mb-4">
                        Votre demande de création de boutique <strong>"<?php echo e($store->name); ?>"</strong> a été rejetée.
                    </p>
                    
                    <!-- Informations de rejet -->
                    <div class="alert alert-danger text-start mb-4">
                        <h5 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>Détails du rejet
                        </h5>
                        <hr>
                        <?php
                            $rejectedAt = $store->crm_validated_at ?? $store->rejected_at;
                            $rejectionNotes = $store->crm_validation_notes ?? $store->rejection_reason;
                        ?>
                        <?php if($rejectedAt): ?>
                            <p class="mb-2">
                                <strong>Date de rejet :</strong> <?php echo e($rejectedAt->format('d/m/Y à H:i')); ?>

                            </p>
                        <?php endif; ?>
                        <?php if($rejectionNotes): ?>
                            <p class="mb-0">
                                <strong>Motif :</strong> <?php echo e($rejectionNotes); ?>

                            </p>
                        <?php else: ?>
                            <p class="mb-0">
                                Aucune raison détaillée n'a été transmise par le support.
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Actions possibles -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="<?php echo e(route('store.create')); ?>" class="btn btn-primary btn-sm me-md-2">
                            <i class="fas fa-plus me-2"></i>Créer une nouvelle boutique
                        </a>
                        <a href="<?php echo e(route('contact')); ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-envelope me-2"></i>Nous contacter
                        </a>
                    </div>
                    
                    <!-- Conseils -->
                    <div class="mt-5">
                        <h5 class="text-muted mb-3">Conseils pour votre prochaine demande :</h5>
                        <div class="row text-start">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Vérifiez que tous les documents sont lisibles
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Assurez-vous que les informations sont correctes
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Rédigez une description détaillée
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Choisissez une catégorie appropriée
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/store/rejected.blade.php ENDPATH**/ ?>