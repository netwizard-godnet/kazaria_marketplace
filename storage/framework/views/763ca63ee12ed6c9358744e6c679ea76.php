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
                    <h4 class="card-title mb-0">Popups configurées</h4>
                    <a href="<?php echo e(route('admin.popups.create')); ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> Nouvelle popup
                    </a>
                </div>
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                    <?php endif; ?>

                    <?php if($popups->isEmpty()): ?>
                        <div class="text-center py-5">
                            <i class="fa fa-window-restore fa-3x text-muted"></i>
                            <h5 class="mt-3 text-muted">Aucune popup configurée</h5>
                            <p class="text-muted">Créez votre première popup marketing pour communiquer avec vos visiteurs.</p>
                            <a href="<?php echo e(route('admin.popups.create')); ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus me-1"></i> Créer une popup
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Popup</th>
                                        <th>Planning</th>
                                        <th>Affichage</th>
                                        <th>Fréquence</th>
                                        <th>Priorité</th>
                                        <th class="text-center">Statut</th>
                                        <th>Modifiée le</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $popups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $popup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo e($popup->title); ?></div>
                                                <small class="text-muted">Slug : <?php echo e($popup->slug); ?></small>
                                                <?php if($popup->image): ?>
                                                    <div>
                                                        <small class="text-muted">Image : <?php echo e($popup->image); ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted d-block">
                                                    <?php if($popup->display_start): ?>
                                                        <i class="fa fa-calendar me-1"></i>
                                                        Du <?php echo e($popup->display_start->format('d/m/Y H:i')); ?>

                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Dès activation</span>
                                                    <?php endif; ?>
                                                </small>
                                                <small class="text-muted d-block">
                                                    <?php if($popup->display_end): ?>
                                                        <i class="fa fa-calendar-check me-1"></i>
                                                        Au <?php echo e($popup->display_end->format('d/m/Y H:i')); ?>

                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Sans fin</span>
                                                    <?php endif; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="mb-1">
                                                    <small class="text-muted">Pages :</small>
                                                    <?php if(!empty($popup->display_pages)): ?>
                                                        <span class="badge bg-light text-dark border">
                                                            <?php echo e(implode(', ', $popup->display_pages)); ?>

                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Toutes</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <small class="text-muted">Appareils :</small>
                                                    <?php if(!empty($popup->display_devices)): ?>
                                                        <?php $__currentLoopData = $popup->display_devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="badge bg-light text-dark border text-capitalize"><?php echo e($device); ?></span>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Tous</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary">
                                                    <?php echo e($frequencies[$popup->frequency] ?? ucfirst(str_replace('_', ' ', $popup->frequency))); ?>

                                                </span>
                                                <div>
                                                    <small class="text-muted">
                                                        Délai : <?php echo e($popup->delay_seconds); ?>s
                                                    </small>
                                                </div>
                                                <?php if($popup->max_impressions): ?>
                                                    <small class="text-muted d-block">
                                                        Max. affichages : <?php echo e($popup->max_impressions); ?>

                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-dark">#<?php echo e($popup->priority); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <form action="<?php echo e(route('admin.popups.toggle', $popup)); ?>" method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="btn btn-sm <?php echo e($popup->is_active ? 'btn-success' : 'btn-outline-secondary'); ?>">
                                                        <i class="fa <?php echo e($popup->is_active ? 'fa-toggle-on' : 'fa-toggle-off'); ?> me-1"></i>
                                                        <?php echo e($popup->is_active ? 'Actif' : 'Inactif'); ?>

                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo e($popup->updated_at?->format('d/m/Y H:i') ?? '—'); ?>

                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?php echo e(route('admin.popups.edit', $popup)); ?>" class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <form action="<?php echo e(route('admin.popups.destroy', $popup)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette popup ?');">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
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


<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/popups/index.blade.php ENDPATH**/ ?>