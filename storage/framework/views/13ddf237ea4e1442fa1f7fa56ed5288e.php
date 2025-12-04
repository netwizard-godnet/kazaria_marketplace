<?php $__env->startSection('title', 'Dashboard Admin - KAZARIA'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Dashboard</h4>
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
                <span>Dashboard</span>
            </li>
        </ul>
    </div>
    
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Utilisateurs</p>
                                <h4 class="card-title"><?php echo e($stats['total_users'] ?? 0); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-store"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Boutiques</p>
                                <h4 class="card-title"><?php echo e($stats['total_stores'] ?? 0); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Commandes</p>
                                <h4 class="card-title"><?php echo e($stats['total_orders'] ?? 0); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Produits</p>
                                <h4 class="card-title"><?php echo e($stats['total_products'] ?? 0); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Statistiques des Ventes</div>
                        <div class="card-tools">
                            <a href="<?php echo e(route('admin.reports.sales')); ?>" class="btn btn-label-success btn-round btn-sm me-2">
                                <span class="btn-label">
                                    <i class="fa fa-chart-bar"></i>
                                </span>
                                Voir le rapport
                            </a>
                            <a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-label-info btn-round btn-sm">
                                <span class="btn-label">
                                    <i class="fa fa-list"></i>
                                </span>
                                Toutes les commandes
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 375px">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-primary card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Ventes du Mois</div>
                        <div class="card-tools">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-label-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Exporter
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="#">PDF</a>
                                    <a class="dropdown-item" href="#">Excel</a>
                                    <a class="dropdown-item" href="#">CSV</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-category"><?php echo e(date('F Y')); ?></div>
                </div>
                <div class="card-body pb-0">
                    <div class="mb-4 mt-2">
                        <h1><?php echo e(number_format($stats['monthly_revenue'] ?? 0, 0, ',', ' ')); ?> FCFA</h1>
                    </div>
                    <div class="pull-in">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="card card-round">
                <div class="card-body pb-0">
                    <div class="h1 fw-bold float-end text-primary">+<?php echo e($stats['growth_rate'] ?? 0); ?>%</div>
                    <h2 class="mb-2"><?php echo e($stats['active_users'] ?? 0); ?></h2>
                    <p class="text-muted">Utilisateurs actifs</p>
                    <div class="pull-in sparkline-fix">
                        <div id="lineChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Commandes Récentes</div>
                        <div class="card-tools">
                            <a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-sm btn-primary">Voir tout</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Commande</th>
                                    <th>Client</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $recent_orders ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>#<?php echo e($order->order_number); ?></td>
                                    <td><?php echo e($order->user->prenoms ?? 'N/A'); ?> <?php echo e($order->user->nom ?? ''); ?></td>
                                    <td><?php echo e(number_format($order->total, 0, ',', ' ')); ?> FCFA</td>
                                    <td>
                                        <?php
                                            $status = $order->status;
                                            $statusMap = [
                                                'pending'   => ['warning',  'En cours de validation'],
                                                'processing'=> ['info',     'En cours de livraison'],
                                                'delivered' => ['success',  'Livrée'],
                                                'cancelled' => ['danger',   'Annulée'],
                                            ];
                                            [$badgeClass, $badgeLabel] = $statusMap[$status] ?? ['secondary', ucfirst($status)];
                                        ?>
                                        <span class="badge badge-<?php echo e($badgeClass); ?>"><?php echo e($badgeLabel); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Aucune commande récente</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Produits Populaires</div>
                        <div class="card-tools">
                            <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-sm btn-primary">Voir tout</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Prix</th>
                                    <th>Ventes</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $popular_products ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(Str::limit($product->name, 30)); ?></td>
                                    <td><?php echo e(number_format($product->price, 0, ',', ' ')); ?> FCFA</td>
                                    <td><?php echo e($product->sales_count ?? 0); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo e($product->stock > 0 ? 'success' : 'danger'); ?>">
                                            <?php echo e($product->stock); ?>

                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center">Aucun produit populaire</td>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Graphique des ventes
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo $chart_data['sales_labels'] ?? "['Jan','Fév','Mar','Avr','Mai','Jun']"; ?>,
            datasets: [{
                label: 'Ventes (FCFA)',
                data: [<?php echo e($chart_data['sales'] ?? '0,0,0,0,0,0'); ?>],
                borderColor: '#ff6b35',
                backgroundColor: 'rgba(255, 107, 53, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Graphique des ventes mensuelles
    const monthlyCtx = document.getElementById('monthlySalesChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'doughnut',
        data: {
            labels: ['Validées', 'En cours', 'Annulées'],
            datasets: [{
                data: [<?php echo e($chart_data['monthly'] ?? '0,0,0'); ?>],
                backgroundColor: ['#28a745', '#17a2b8', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Graphique linéaire des utilisateurs actifs
    $("#lineChart").sparkline([<?php echo e($chart_data['active_users'] ?? '0,0,0,0,0,0,0'); ?>], {
        type: "line",
        height: "70",
        width: "100%",
        lineWidth: "2",
        lineColor: "#ff6b35",
        fillColor: "rgba(255, 107, 53, 0.14)",
    });
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\kaz_final\kazaria_marketplace\resources\views/admin/dashboard/index.blade.php ENDPATH**/ ?>