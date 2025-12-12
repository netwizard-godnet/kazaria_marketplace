

<?php $__env->startSection('title', 'Rapports et Statistiques'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rapports et Statistiques</h4>
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
                <span>Rapports</span>
            </li>
        </ul>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Ventes</p>
                                <h4 class="card-title">Rapport</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?php echo e(route('admin.reports.sales')); ?>" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-eye"></i> Voir
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Utilisateurs</p>
                                <h4 class="card-title">Rapport</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?php echo e(route('admin.reports.users')); ?>" class="btn btn-info btn-sm btn-block">
                        <i class="fas fa-eye"></i> Voir
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Produits</p>
                                <h4 class="card-title">Rapport</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?php echo e(route('admin.reports.products')); ?>" class="btn btn-success btn-sm btn-block">
                        <i class="fas fa-eye"></i> Voir
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-download"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Export</p>
                                <h4 class="card-title">Données</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button class="btn btn-warning btn-sm btn-block" onclick="exportData()">
                        <i class="fas fa-download"></i> Exporter
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Rapports Rapides</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Statistiques Générales</h5>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Total des ventes ce mois
                                    <span class="badge badge-primary badge-pill"><?php echo e(number_format($stats['monthly_revenue'] ?? 0, 0, ',', ' ')); ?> FCFA</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Commandes en attente
                                    <span class="badge badge-warning badge-pill"><?php echo e($stats['pending_orders'] ?? 0); ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Produits en stock faible
                                    <span class="badge badge-danger badge-pill"><?php echo e($stats['low_stock_products'] ?? 0); ?></span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Actions Rapides</h5>
                            <div class="d-grid gap-2">
                                <a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-list"></i> Voir toutes les commandes
                                </a>
                                <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-outline-success">
                                    <i class="fas fa-box"></i> Gérer les produits
                                </a>
                                <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-info">
                                    <i class="fas fa-users"></i> Gérer les utilisateurs
                                </a>
                                <a href="<?php echo e(route('admin.stores.index')); ?>" class="btn btn-outline-warning">
                                    <i class="fas fa-store"></i> Gérer les boutiques
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function exportData() {
    // Fonction pour exporter les données
    alert('Fonctionnalité d\'export en cours de développement');
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\reports\index.blade.php ENDPATH**/ ?>