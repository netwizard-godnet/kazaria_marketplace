

<?php $__env->startSection('title', 'Rapport produits'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rapport produits</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="<?php echo e(route('admin.dashboard')); ?>"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="<?php echo e(route('admin.reports.index')); ?>">Rapports</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Produits</span></li>
        </ul>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.reports.products')); ?>" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Du</label>
                    <input type="date" name="date_from" value="<?php echo e(request('date_from', now()->subMonth()->format('Y-m-d'))); ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Au</label>
                    <input type="date" name="date_to" value="<?php echo e(request('date_to', now()->format('Y-m-d'))); ?>" class="form-control">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrer</button>
                    <a href="<?php echo e(route('admin.reports.products')); ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Réinitialiser</a>
                    <a href="<?php echo e(route('admin.reports.export', 'products')); ?>" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-primary">
                        <i class="fas fa-box"></i>
                    </div>
                    <h5 class="card-category">Nouveaux Produits</h5>
                    <h3 class="card-title"><?php echo e(number_format($totals['products'] ?? 0, 0, ',', ' ')); ?></h3>
                    <p class="text-muted">Période sélectionnée</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h5 class="card-category">Total Produits</h5>
                    <h3 class="card-title"><?php echo e(number_format($totals['total_products'] ?? 0, 0, ',', ' ')); ?></h3>
                    <p class="text-muted">Actifs: <?php echo e(number_format($totals['active_products'] ?? 0, 0, ',', ' ')); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h5 class="card-category">Stock Faible</h5>
                    <h3 class="card-title"><?php echo e(number_format($totals['low_stock'] ?? 0, 0, ',', ' ')); ?></h3>
                    <p class="text-muted">Rupture: <?php echo e(number_format($totals['out_of_stock'] ?? 0, 0, ',', ' ')); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-info">
                        <i class="fas fa-star"></i>
                    </div>
                    <h5 class="card-category">Produits Mis en Avant</h5>
                    <h3 class="card-title"><?php echo e(number_format($totals['featured_products'] ?? 0, 0, ',', ' ')); ?></h3>
                    <p class="text-muted">Dans la période</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Évolution des Produits Ajoutés</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 400px">
                        <canvas id="productsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Produits Consultés -->
    <?php if(isset($topViewed) && $topViewed->count() > 0): ?>
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Top 10 Produits les Plus Consultés</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Prix</th>
                                    <th>Stock</th>
                                    <th>Vues</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $topViewed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e(Str::limit($product->name, 40)); ?></td>
                                    <td><?php echo e(number_format($product->price, 0, ',', ' ')); ?> FCFA</td>
                                    <td>
                                        <span class="badge badge-<?php echo e($product->stock > 0 ? ($product->stock < 10 ? 'warning' : 'success') : 'danger'); ?>">
                                            <?php echo e($product->stock); ?>

                                        </span>
                                    </td>
                                    <td><span class="badge badge-info"><?php echo e(number_format($product->views_count ?? 0, 0, ',', ' ')); ?></span></td>
                                    <td>
                                        <?php if($product->is_active): ?>
                                            <span class="badge badge-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactif</span>
                                        <?php endif; ?>
                                        <?php if($product->is_featured): ?>
                                            <span class="badge badge-primary">Mis en avant</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tableau détaillé -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Produits Ajoutés par Jour</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Nombre de produits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $productsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e(\Carbon\Carbon::parse($row->date)->format('d/m/Y')); ?></td>
                            <td><span class="badge badge-primary"><?php echo e($row->products_count); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="2" class="text-center">Aucune donnée</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if(count($productsData) > 0): ?>
                    <tfoot>
                        <tr class="table-primary">
                            <th>Total</th>
                            <th><span class="badge badge-primary"><?php echo e($totals['products']); ?></span></th>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const productsChartCtx = document.getElementById('productsChart');
    if (productsChartCtx) {
        const chartData = <?php echo json_encode($chartData ?? [], 15, 512) ?>;
        new Chart(productsChartCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    label: 'Nombre de produits',
                    data: chartData.values || [],
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\admin\reports\products.blade.php ENDPATH**/ ?>