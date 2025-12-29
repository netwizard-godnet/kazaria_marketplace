

<?php $__env->startSection('title', 'Gestion des popups'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Popups marketing</h4>
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
                <span>Popups</span>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fa fa-window-restore me-2"></i>
                        Popups configurées
                    </h4>
                    <a href="<?php echo e(route('admin.popups.create')); ?>" class="btn btn-primary">
                        <i class="fa fa-plus me-1"></i> Nouvelle popup
                    </a>
                </div>
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle me-2"></i>
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if($popups->isEmpty()): ?>
                        <div class="text-center py-5">
                            <i class="fa fa-window-restore fa-4x text-muted mb-3"></i>
                            <h5 class="mt-3 text-muted">Aucune popup configurée</h5>
                            <p class="text-muted mb-4">Créez votre première popup marketing pour communiquer avec vos visiteurs.</p>
                            <a href="<?php echo e(route('admin.popups.create')); ?>" class="btn btn-primary">
                                <i class="fa fa-plus me-1"></i> Créer une popup
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 200px;">Popup</th>
                                        <th style="width: 150px;">Planning</th>
                                        <th style="width: 150px;">Affichage</th>
                                        <th style="width: 120px;">Fréquence</th>
                                        <th style="width: 80px;" class="text-center">Priorité</th>
                                        <th style="width: 100px;" class="text-center">Statut</th>
                                        <th style="width: 120px;">Modifiée</th>
                                        <th style="width: 120px;" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $popups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $popup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if($popup->image): ?>
                                                        <img src="<?php echo e(asset('storage/' . $popup->image)); ?>" 
                                                             alt="<?php echo e($popup->title); ?>" 
                                                             class="me-2 rounded" 
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 50px; height: 50px;">
                                                            <i class="fa fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold"><?php echo e($popup->title ?: 'Sans titre'); ?></div>
                                                        <small class="text-muted"><?php echo e($popup->slug); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($popup->display_start): ?>
                                                    <small class="d-block text-muted">
                                                        <i class="fa fa-calendar text-primary me-1"></i>
                                                        <?php echo e($popup->display_start->format('d/m/Y H:i')); ?>

                                                    </small>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Immédiat</span>
                                                <?php endif; ?>
                                                <?php if($popup->display_end): ?>
                                                    <small class="d-block text-muted mt-1">
                                                        <i class="fa fa-calendar-check text-danger me-1"></i>
                                                        <?php echo e($popup->display_end->format('d/m/Y H:i')); ?>

                                                    </small>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary mt-1">Sans fin</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="mb-1">
                                                    <small class="text-muted d-block mb-1">Pages :</small>
                                                    <?php if(!empty($popup->display_pages)): ?>
                                                        <?php $__currentLoopData = array_slice($popup->display_pages, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="badge bg-info-subtle text-info me-1 mb-1">
                                                                <?php echo e($page); ?>

                                                            </span>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if(count($popup->display_pages) > 2): ?>
                                                            <span class="badge bg-secondary">+<?php echo e(count($popup->display_pages) - 2); ?></span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-success-subtle text-success">Toutes</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <small class="text-muted d-block mb-1">Appareils :</small>
                                                    <?php if(!empty($popup->display_devices)): ?>
                                                        <?php $__currentLoopData = $popup->display_devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="badge bg-light text-dark border me-1 text-capitalize">
                                                                <?php echo e($device); ?>

                                                            </span>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-success-subtle text-success">Tous</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary mb-1">
                                                    <?php echo e($frequencies[$popup->frequency] ?? $popup->frequency); ?>

                                                </span>
                                                <div>
                                                    <small class="text-muted">
                                                        Délai : <?php echo e($popup->delay_seconds); ?>s
                                                    </small>
                                                </div>
                                                <?php if($popup->max_impressions): ?>
                                                    <small class="text-muted d-block">
                                                        Max : <?php echo e($popup->max_impressions); ?>

                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info text-dark fs-6">#<?php echo e($popup->priority); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <form action="<?php echo e(route('admin.popups.toggle', $popup)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" 
                                                            class="btn btn-sm <?php echo e($popup->is_active ? 'btn-success' : 'btn-outline-secondary'); ?>"
                                                            title="<?php echo e($popup->is_active ? 'Désactiver' : 'Activer'); ?>">
                                                        <i class="fa <?php echo e($popup->is_active ? 'fa-toggle-on' : 'fa-toggle-off'); ?>"></i>
                                                        <?php echo e($popup->is_active ? 'Actif' : 'Inactif'); ?>

                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo e($popup->updated_at->format('d/m/Y')); ?><br>
                                                    <span class="text-muted"><?php echo e($popup->updated_at->format('H:i')); ?></span>
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo e(route('admin.popups.edit', $popup)); ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Modifier">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <form action="<?php echo e(route('admin.popups.destroy', $popup)); ?>" 
                                                          method="POST" 
                                                          class="d-inline" 
                                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette popup ?');">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="Supprimer">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <?php echo e($popups->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\popups\index.blade.php ENDPATH**/ ?>