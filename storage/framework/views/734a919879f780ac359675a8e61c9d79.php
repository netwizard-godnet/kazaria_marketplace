<?php $__env->startSection('title', 'Détails de la boutique'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Détails de la boutique</h4>
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
                <a href="<?php echo e(route('admin.stores.index')); ?>">Boutiques</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span><?php echo e($store->name); ?></span>
            </li>
        </ul>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h4 class="card-title"><?php echo e($store->name); ?></h4>
                        <div class="btn-group">
                            <?php if($store->isKycValidated()): ?>
                                <form action="<?php echo e(route('admin.stores.toggle-official', $store)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-<?php echo e($store->is_official ? 'secondary' : 'warning'); ?> btn-sm" 
                                                title="<?php echo e($store->is_official ? 'Désactiver boutique officielle' : 'Activer boutique officielle'); ?>"
                                                onclick="return confirm('<?php echo e($store->is_official ? 'Désactiver le statut officiel' : 'Activer le statut officiel'); ?> pour cette boutique ?')">
                                            <i class="fas fa-certificate"></i> 
                                            <?php echo e($store->is_official ? 'Désactiver Officielle' : 'Activer Officielle'); ?>

                                        </button>
                                    </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?php if($store->logo): ?>
                                <img src="<?php echo e($store->logo_url); ?>" alt="<?php echo e($store->name); ?>" class="img-fluid rounded">
                            <?php else: ?>
                                <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 200px;">
                                    <i class="fas fa-store fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5>Description</h5>
                            <p class="text-muted"><?php echo e($store->description ?? 'Aucune description'); ?></p>
                            
                            <h5>Informations</h5>
                            <ul class="list-unstyled">
                                <li><strong>ID :</strong> <?php echo e($store->id); ?></li>
                                <li><strong>Nom :</strong> <?php echo e($store->name); ?></li>
                                <li><strong>Slug :</strong> <?php echo e($store->slug ?? 'N/A'); ?></li>
                                <?php
                                    $kycLabel = $store->effective_kyc_status ? ucfirst($store->effective_kyc_status) : 'Inconnu';
                                    $kycClass = $store->isKycValidated() ? 'success' : ($store->isKycPending() ? 'warning' : ($store->isKycRejected() ? 'danger' : 'secondary'));
                                ?>
                                <li><strong>Statut KYC :</strong> 
                                    <span class="badge badge-<?php echo e($kycClass); ?>">
                                        <i class="fas fa-id-card me-1"></i><?php echo e($kycLabel); ?>

                                    </span>
                                </li>
                                <li><strong>Vérifiée :</strong> 
                                    <span class="badge badge-<?php echo e($store->is_verified ? 'success' : 'danger'); ?>">
                                        <?php echo e($store->is_verified ? 'Oui' : 'Non'); ?>

                                    </span>
                                </li>
                                <li><strong>Taux commission (CRM) :</strong> <?php echo e(number_format($store->effective_commission_rate, 2, ',', ' ')); ?>%</li>
                                <?php if($store->crm_scoring !== null): ?>
                                    <li><strong>Scoring CRM :</strong> <?php echo e(number_format($store->crm_scoring, 1, ',', ' ')); ?></li>
                                <?php endif; ?>
                                <?php if($store->crm_validated_at): ?>
                                    <li><strong>Validé par CRM le :</strong> <?php echo e($store->crm_validated_at->format('d/m/Y H:i')); ?></li>
                                <?php endif; ?>
                                <?php if($store->crm_validation_notes): ?>
                                    <li><strong>Notes CRM :</strong> <?php echo e($store->crm_validation_notes); ?></li>
                                <?php endif; ?>
                                <li><strong>Officielle :</strong> 
                                    <?php if($store->is_official): ?>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-certificate"></i> Oui - Affichée sur /boutique-officielle
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Non</span>
                                    <?php endif; ?>
                                </li>
                                <li><strong>Créée le :</strong> <?php echo e($store->created_at->format('d/m/Y H:i')); ?></li>
                                <?php if($store->approved_at): ?>
                                    <li><strong>Approuvée le :</strong> <?php echo e($store->approved_at->format('d/m/Y H:i')); ?></li>
                                    <li><strong>Approuvée par :</strong> <?php echo e($store->approver ? $store->approver->name : 'N/A'); ?></li>
                                <?php endif; ?>
                                <?php if($store->rejected_at): ?>
                                    <li><strong>Rejetée le :</strong> <?php echo e($store->rejected_at->format('d/m/Y H:i')); ?></li>
                                    <li><strong>Rejetée par :</strong> <?php echo e($store->rejector ? $store->rejector->name : 'N/A'); ?></li>
                                    <?php if($store->rejection_reason): ?>
                                        <li><strong>Raison du rejet :</strong> <?php echo e($store->rejection_reason); ?></li>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Propriétaire</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Nom :</strong><br>
                        <?php echo e($store->user->nom); ?> <?php echo e($store->user->prenoms); ?>

                    </div>
                    <div class="mb-3">
                        <strong>Email :</strong><br>
                        <?php echo e($store->user->email); ?>

                    </div>
                    <div class="mb-3">
                        <strong>Téléphone :</strong><br>
                        <?php echo e($store->user->telephone ?? 'N/A'); ?>

                    </div>
                    <div class="mb-3">
                        <strong>Vendeur depuis :</strong><br>
                        <?php echo e($store->user->created_at->format('d/m/Y')); ?>

                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Statistiques</h4>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h3 class="text-primary"><?php echo e($store->products->count()); ?></h3>
                            <p class="text-muted">Produits</p>
                        </div>
                        <div class="col-6">
                            <h3 class="text-success"><?php echo e($store->total_orders ?? 0); ?></h3>
                            <p class="text-muted">Commandes</p>
                        </div>
                    </div>
                    <div class="row text-center mt-3">
                        <div class="col-6">
                            <h3 class="text-info"><?php echo e(number_format($store->total_sales ?? 0, 0, ',', ' ')); ?> FCFA</h3>
                            <p class="text-muted">Ventes</p>
                        </div>
                        <div class="col-6">
                            <h3 class="text-warning"><?php echo e($store->rating ?? 0); ?>/5</h3>
                            <p class="text-muted">Note</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if($store->products->count() > 0): ?>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Produits récents</h4>
                </div>
                <div class="card-body">
                    <?php $__currentLoopData = $store->products->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong><?php echo e($product->name); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo e(number_format($product->price, 0, ',', ' ')); ?> FCFA</small>
                            </div>
                            <span class="badge badge-<?php echo e($product->is_active ? 'success' : 'danger'); ?>">
                                <?php echo e($product->is_active ? 'Actif' : 'Inactif'); ?>

                            </span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Section Documents -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fas fa-file-alt me-2"></i>Documents soumis
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Logo -->
                            <div class="col-md-6 mb-4">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="fas fa-image me-2"></i>Logo de la boutique
                                        </h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if($store->logo_url): ?>
                                            <img src="<?php echo e($store->logo_url); ?>" alt="Logo <?php echo e($store->name); ?>" 
                                                 class="img-fluid mb-3" style="max-height: 200px; max-width: 100%;">
                                            <div>
                                                <a href="<?php echo e($store->logo_url); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i>Voir en grand
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted">
                                                <i class="fas fa-image fa-3x mb-3"></i>
                                                <p>Aucun logo fourni</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Bannière -->
                            <div class="col-md-6 mb-4">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="fas fa-image me-2"></i>Bannière de la boutique
                                        </h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if($store->banner_url): ?>
                                            <img src="<?php echo e($store->banner_url); ?>" alt="Bannière <?php echo e($store->name); ?>" 
                                                 class="img-fluid mb-3" style="max-height: 200px; max-width: 100%;">
                                            <div>
                                                <a href="<?php echo e($store->banner_url); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i>Voir en grand
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted">
                                                <i class="fas fa-image fa-3x mb-3"></i>
                                                <p>Aucune bannière fournie</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Document DFE -->
                            <div class="col-md-6 mb-4">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="fas fa-file-pdf me-2"></i>Document DFE
                                        </h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if($store->dfe_document_url): ?>
                                            <div class="mb-3">
                                                <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                            </div>
                                            <p class="text-muted mb-3">Document DFE soumis</p>
                                            <div>
                                                <a href="<?php echo e($store->dfe_document_url); ?>" target="_blank" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-download me-1"></i>Télécharger
                                                </a>
                                                <a href="<?php echo e($store->dfe_document_url); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-eye me-1"></i>Voir
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted">
                                                <i class="fas fa-file-pdf fa-3x mb-3"></i>
                                                <p>Aucun document DFE fourni</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Registre de Commerce -->
                            <div class="col-md-6 mb-4">
                                <div class="card border">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="fas fa-file-pdf me-2"></i>Registre de Commerce
                                        </h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if($store->commerce_register_url): ?>
                                            <div class="mb-3">
                                                <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                            </div>
                                            <p class="text-muted mb-3">Registre de commerce soumis</p>
                                            <div>
                                                <a href="<?php echo e($store->commerce_register_url); ?>" target="_blank" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-download me-1"></i>Télécharger
                                                </a>
                                                <a href="<?php echo e($store->commerce_register_url); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-eye me-1"></i>Voir
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted">
                                                <i class="fas fa-file-pdf fa-3x mb-3"></i>
                                                <p>Aucun registre de commerce fourni</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/stores/show.blade.php ENDPATH**/ ?>