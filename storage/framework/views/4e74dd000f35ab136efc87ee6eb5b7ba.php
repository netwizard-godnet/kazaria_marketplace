

<?php $__env->startSection('title', 'Gestion des Boutiques'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Gestion des Boutiques</h4>
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
                <span>Boutiques</span>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestion des Boutiques</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nouvelle Boutique
                        </button>
                    </div>
                </div>
                
                <!-- Filtres -->
                <div class="card-body border-bottom">
                    <form method="GET" action="<?php echo e(route('admin.stores.index')); ?>" class="row g-3">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search" placeholder="Rechercher par nom..." value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>En attente</option>
                                <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>Active</option>
                                <option value="suspended" <?php echo e(request('status') === 'suspended' ? 'selected' : ''); ?>>Suspendue</option>
                                <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Rejetée</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="validation">
                                <option value="">Toutes les validations</option>
                                <option value="pending" <?php echo e(request('validation') === 'pending' ? 'selected' : ''); ?>>En attente</option>
                                <option value="approved" <?php echo e(request('validation') === 'approved' ? 'selected' : ''); ?>>Approuvée</option>
                                <option value="rejected" <?php echo e(request('validation') === 'rejected' ? 'selected' : ''); ?>>Rejetée</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="documents">
                                <option value="">Tous les documents</option>
                                <option value="complete" <?php echo e(request('documents') === 'complete' ? 'selected' : ''); ?>>Documents complets</option>
                                <option value="incomplete" <?php echo e(request('documents') === 'incomplete' ? 'selected' : ''); ?>>Documents incomplets</option>
                                <option value="no_documents" <?php echo e(request('documents') === 'no_documents' ? 'selected' : ''); ?>>Aucun document</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?php echo e(route('admin.stores.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Effacer
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Logo</th>
                                    <th>Nom</th>
                                    <th>Propriétaire</th>
                                    <th>Email</th>
                                    <th>Statut</th>
                                    <th>Date création</th>
                                    <th>Documents</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($store->id); ?></td>
                                    <td>
                                        <?php if($store->logo): ?>
                                            <img src="<?php echo e($store->logo_url); ?>" alt="<?php echo e($store->name); ?>" class="img-thumbnail" width="50">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="fas fa-store text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo e($store->name); ?></strong>
                                        <?php if($store->is_official): ?>
                                            <span class="badge bg-warning text-dark ms-2" title="Boutique officielle">
                                                <i class="fas fa-certificate"></i> Officielle
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($store->user->prenoms ?? 'N/A'); ?> <?php echo e($store->user->nom ?? ''); ?></td>
                                    <td><?php echo e($store->user->email ?? 'N/A'); ?></td>
                                    <td>
                                        <?php if($store->approved_at): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Approuvée
                                            </span>
                                        <?php elseif($store->rejected_at): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Rejetée
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>En attente
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($store->created_at->format('d/m/Y')); ?></td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <?php if($store->logo): ?>
                                                <span class="badge bg-info text-white" title="Logo fourni">
                                                    <i class="fas fa-image me-1"></i>Logo
                                                </span>
                                            <?php endif; ?>
                                            <?php if($store->banner): ?>
                                                <span class="badge bg-info text-white" title="Bannière fournie">
                                                    <i class="fas fa-image me-1"></i>Bannière
                                                </span>
                                            <?php endif; ?>
                                            <?php if($store->dfe_document): ?>
                                                <span class="badge bg-success text-white" title="Document DFE fourni">
                                                    <i class="fas fa-file-pdf me-1"></i>DFE
                                                </span>
                                            <?php endif; ?>
                                            <?php if($store->commerce_register): ?>
                                                <span class="badge bg-success text-white" title="Registre de commerce fourni">
                                                    <i class="fas fa-file-pdf me-1"></i>RC
                                                </span>
                                            <?php endif; ?>
                                            <?php if(!$store->logo && !$store->banner && !$store->dfe_document && !$store->commerce_register): ?>
                                                <span class="text-muted small">Aucun document</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo e(route('admin.stores.show', $store)); ?>" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if(!$store->approved_at && !$store->rejected_at): ?>
                                                <form action="<?php echo e(route('admin.stores.approve', $store)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approuver cette boutique ?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo e($store->id); ?>">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            <?php else: ?>
                                                <form action="<?php echo e(route('admin.stores.toggle-status', $store)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-<?php echo e($store->status === 'active' ? 'warning' : 'success'); ?> btn-sm" 
                                                            onclick="return confirm('<?php echo e($store->status === 'active' ? 'Suspendre' : 'Réactiver'); ?> cette boutique ?')">
                                                        <i class="fas fa-<?php echo e($store->status === 'active' ? 'pause' : 'play'); ?>"></i>
                                                    </button>
                                                </form>
                                                <?php if($store->approved_at): ?>
                                                    <form action="<?php echo e(route('admin.stores.toggle-official', $store)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-<?php echo e($store->is_official ? 'secondary' : 'warning'); ?> btn-sm" 
                                                                title="<?php echo e($store->is_official ? 'Désactiver boutique officielle' : 'Activer boutique officielle'); ?>"
                                                                onclick="return confirm('<?php echo e($store->is_official ? 'Désactiver le statut officiel' : 'Activer le statut officiel'); ?> pour cette boutique ?')">
                                                            <i class="fas fa-certificate"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Aucune boutique trouvée</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals de rejet -->
<?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $store): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if(!$store->approved_at && !$store->rejected_at): ?>
<div class="modal fade" id="rejectModal<?php echo e($store->id); ?>" tabindex="-1" aria-labelledby="rejectModalLabel<?php echo e($store->id); ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel<?php echo e($store->id); ?>">Rejeter la boutique</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo e(route('admin.stores.reject', $store)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <p>Vous êtes sur le point de rejeter la boutique <strong><?php echo e($store->name); ?></strong>.</p>
                    <div class="mb-3">
                        <label for="rejection_reason<?php echo e($store->id); ?>" class="form-label">Raison du rejet <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason<?php echo e($store->id); ?>" name="rejection_reason" rows="4" 
                                  placeholder="Expliquez pourquoi cette boutique est rejetée..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Rejeter la boutique</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/admin/stores/index.blade.php ENDPATH**/ ?>