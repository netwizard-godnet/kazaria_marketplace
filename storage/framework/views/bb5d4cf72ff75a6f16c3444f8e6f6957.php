

<?php $__env->startSection('title', 'Mon Profil - KAZARIA Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Mon Profil</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Tableau de bord</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Mon Profil</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 col-sm-12 text-right">
                    <a href="<?php echo e(route('admin.profile.edit')); ?>" class="btn btn-primary">
                        <i class="fa fa-edit"></i> Modifier le profil
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="row">
            <div class="col-lg-4 col-md-12">
                <!-- Profile Card -->
                <div class="card">
                    <div class="card-body text-center">
                        <div class="profile-photo mb-3">
                            <?php if($user->profile_pic_url): ?>
                                <img src="<?php echo e(asset($user->profile_pic_url)); ?>" alt="Photo de profil" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                    <span class="text-white" style="font-size: 48px; font-weight: bold;">
                                        <?php echo e(strtoupper(substr($user->prenoms, 0, 1) . substr($user->nom, 0, 1))); ?>

                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h4 class="mb-1"><?php echo e($user->prenoms); ?> <?php echo e($user->nom); ?></h4>
                        <p class="text-muted mb-3"><?php echo e($user->email); ?></p>
                        
                        <?php if($user->telephone): ?>
                            <p class="mb-1">
                                <i class="fa fa-phone text-primary me-2"></i>
                                <?php echo e($user->telephone); ?>

                            </p>
                        <?php endif; ?>
                        
                        <?php if($user->is_admin): ?>
                            <span class="badge bg-success mb-3">Administrateur</span>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <a href="<?php echo e(route('admin.profile.edit')); ?>" class="btn btn-primary btn-sm me-2">
                                <i class="fa fa-edit"></i> Modifier
                            </a>
                            <a href="<?php echo e(route('admin.profile.password')); ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-key"></i> Mot de passe
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">Statistiques rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary"><?php echo e(\App\Models\Order::count()); ?></h4>
                                <p class="text-muted mb-0">Commandes</p>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success"><?php echo e(\App\Models\Product::count()); ?></h4>
                                <p class="text-muted mb-0">Produits</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-12">
                <!-- Profile Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Informations du profil</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Prénoms</label>
                                    <p class="form-control-plaintext"><?php echo e($user->prenoms); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Nom</label>
                                    <p class="form-control-plaintext"><?php echo e($user->nom); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <p class="form-control-plaintext"><?php echo e($user->email); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Téléphone</label>
                                    <p class="form-control-plaintext"><?php echo e($user->telephone ?? 'Non renseigné'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Adresse</label>
                            <p class="form-control-plaintext"><?php echo e($user->adresse ?? 'Non renseignée'); ?></p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Membre depuis</label>
                                    <p class="form-control-plaintext"><?php echo e($user->created_at->format('d/m/Y à H:i')); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Dernière connexion</label>
                                    <p class="form-control-plaintext"><?php echo e($user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : 'Jamais'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Security -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">Sécurité du compte</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-shield-alt text-success me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Mot de passe</h6>
                                        <p class="text-muted mb-0">Dernière modification : <?php echo e($user->updated_at->format('d/m/Y')); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="<?php echo e(route('admin.profile.password')); ?>" class="btn btn-outline-primary">
                                    <i class="fa fa-key"></i> Changer le mot de passe
                                </a>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-envelope text-info me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Email vérifié</h6>
                                        <p class="text-muted mb-0">
                                            <?php if($user->email_verified_at): ?>
                                                <span class="text-success">Oui (<?php echo e($user->email_verified_at->format('d/m/Y')); ?>)</span>
                                            <?php else: ?>
                                                <span class="text-warning">Non</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-user-shield text-primary me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Rôle</h6>
                                        <p class="text-muted mb-0">
                                            <?php if($user->is_admin): ?>
                                                <span class="badge bg-success">Administrateur</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Utilisateur</span>
                                            <?php endif; ?>
                                        </p>
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

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\profile\index.blade.php ENDPATH**/ ?>