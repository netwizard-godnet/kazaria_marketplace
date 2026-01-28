

<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/store.css')); ?>">
<div class="container my-5 store-pending">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5 text-center">
                    <!-- Icône d'attente -->
                    <div class="mb-4">
                        <i class="bi bi-clock-history orange-color" style="font-size: 5rem;"></i>
                    </div>

                    <!-- Titre -->
                    <h2 class="fw-bold blue-color mb-3">Demande en cours de traitement</h2>
                    
                    <!-- Message -->
                    <p class="text-muted fs-5 mb-4">
                        Votre demande de création de boutique a bien été reçue !
                    </p>

                    <!-- Détails de la boutique -->
                    <div class="alert alert-info text-start mb-4">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-shop me-2"></i><?php echo e($store->name); ?>

                        </h5>
                        <p class="mb-2">
                            <strong>Email:</strong> <?php echo e($store->email); ?>

                        </p>
                        <p class="mb-2">
                            <strong>Téléphone:</strong> <?php echo e($store->phone); ?>

                        </p>
                        <p class="mb-2">
                            <strong>Catégorie:</strong> <?php echo e($store->category->name); ?>

                        </p>
                        <?php
                            $pendingLabel = $store->effective_kyc_status ? ucfirst($store->effective_kyc_status) : 'Inconnu';
                            $pendingClass = $store->isKycValidated() ? 'bg-success' : ($store->isKycPending() ? 'bg-warning text-dark' : ($store->isKycRejected() ? 'bg-danger' : 'bg-secondary'));
                        ?>
                        <p class="mb-0">
                            <strong>Statut:</strong>
                            <span class="badge <?php echo e($pendingClass); ?>"><?php echo e($pendingLabel); ?></span>
                        </p>
                    </div>

                    <?php if($store->isKycPending()): ?>
                        <!-- Timeline -->
                        <div class="my-5">
                            <h5 class="fw-bold mb-4">Prochaines étapes</h5>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success">
                                        <i class="bi bi-check text-white"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold">Demande soumise</h6>
                                        <p class="text-muted small"><?php echo e($store->created_at->format('d/m/Y à H:i')); ?></p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning">
                                        <i class="bi bi-hourglass-split text-white"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold">Vérification des documents</h6>
                                        <p class="text-muted small">En cours... (24-48h)</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-secondary">
                                        <i class="bi bi-star text-white"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold">Activation de votre boutique</h6>
                                        <p class="text-muted small">Bientôt...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations -->
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            Notre équipe examine actuellement vos documents. Vous recevrez un email dès que votre boutique sera activée.
                        </div>
                        <?php if($store->crm_validation_notes): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-chat-dots me-2"></i>
                                <strong>Note du support :</strong> <?php echo e($store->crm_validation_notes); ?>

                            </div>
                        <?php endif; ?>
                        <?php if($store->crm_validated_at): ?>
                            <p class="text-muted small">Dernière mise à jour du support : <?php echo e($store->crm_validated_at->format('d/m/Y à H:i')); ?></p>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($store->isKycRejected()): ?>
                        <div class="alert alert-danger">
                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>Demande rejetée
                            </h5>
                            <p>Malheureusement, votre demande n'a pas pu être approuvée. Veuillez nous contacter pour plus d'informations.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Boutons -->
                    <div class="mt-4">
                        <a href="<?php echo e(route('accueil')); ?>" class="btn btn-outline-primary btn-sm me-2">
                            <i class="bi bi-house me-2"></i>Retour à l'accueil
                        </a>
                        <a href="<?php echo e(route('profil')); ?>?token=<?php echo e(request()->token); ?>" class="btn orange-bg text-white">
                            <i class="bi bi-person me-2"></i>Mon profil
                        </a>
                    </div>

                    <!-- Contact -->
                    <div class="mt-5 pt-4 border-top">
                        <p class="text-muted mb-2">
                            <strong>Besoin d'aide ?</strong>
                        </p>
                        <p class="text-muted">
                            <i class="bi bi-envelope me-2"></i>
                            <a href="mailto:vendeurs@kazaria.com" class="orange-color">vendeurs@kazaria.com</a>
                        </p>
                        <p class="text-muted">
                            <i class="bi bi-telephone me-2"></i>
                            <?php
                                $contactPhone = $settings['contact_phone'] ?? '+225 07 00 00 00 00';
                                $phoneLink = str_replace(['+', ' ', '-'], '', $contactPhone);
                            ?>
                            <a href="tel:<?php echo e($phoneLink); ?>" class="orange-color"><?php echo e($contactPhone); ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\store\pending.blade.php ENDPATH**/ ?>