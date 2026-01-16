<?php $__env->startSection('title', 'Rapport utilisateurs'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rapport utilisateurs</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="<?php echo e(route('admin.dashboard')); ?>"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="<?php echo e(route('admin.reports.index')); ?>">Rapports</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Utilisateurs</span></li>
        </ul>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.reports.users')); ?>" class="row g-3">
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
                    <a href="<?php echo e(route('admin.reports.users')); ?>" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Réinitialiser</a>
                    <a href="<?php echo e(route('admin.reports.export', 'users')); ?>" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</a>
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
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="card-category">Nouveaux Utilisateurs</h5>
                    <h3 class="card-title"><?php echo e(number_format($totals['users'] ?? 0, 0, ',', ' ')); ?></h3>
                    <p class="text-muted">Période sélectionnée</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h5 class="card-category">Total Utilisateurs</h5>
                    <h3 class="card-title"><?php echo e(number_format($totals['total_users'] ?? 0, 0, ',', ' ')); ?></h3>
                    <p class="text-muted">Vérifiés: <?php echo e(number_format($totals['verified_users'] ?? 0, 0, ',', ' ')); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-info">
                        <i class="fas fa-store"></i>
                    </div>
                    <h5 class="card-category">Vendeurs</h5>
                    <h3 class="card-title"><?php echo e(number_format($totals['sellers'] ?? 0, 0, ',', ' ')); ?></h3>
                    <p class="text-muted">Dans la période</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-warning">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <h5 class="card-category">Nouveaux Aujourd'hui</h5>
                    <h3 class="card-title"><?php echo e(number_format($totals['new_today'] ?? 0, 0, ',', ' ')); ?></h3>
                    <p class="text-muted">Ce mois: <?php echo e(number_format($totals['new_this_month'] ?? 0, 0, ',', ' ')); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphique -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Évolution des Inscriptions</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 400px">
                        <canvas id="usersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Utilisateurs -->
    <?php if(isset($topUsers) && $topUsers->count() > 0): ?>
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Top 10 Utilisateurs par Commandes</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email</th>
                                    <th>Nombre de commandes</th>
                                    <th>Total dépensé</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $topUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e(($user->prenoms ?? '') . ' ' . ($user->nom ?? '')); ?></td>
                                    <td><?php echo e($user->email); ?></td>
                                    <td><span class="badge badge-primary"><?php echo e($user->orders_count); ?></span></td>
                                    <td><strong><?php echo e(number_format($user->total_spent, 0, ',', ' ')); ?> FCFA</strong></td>
                                    <td>
                                        <?php if($user->is_verified): ?>
                                            <span class="badge badge-success">Vérifié</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Non vérifié</span>
                                        <?php endif; ?>
                                        <?php if($user->is_seller): ?>
                                            <span class="badge badge-info">Vendeur</span>
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
            <h4 class="card-title">Inscriptions par Jour</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Nombre d'utilisateurs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $usersData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e(\Carbon\Carbon::parse($row->date)->format('d/m/Y')); ?></td>
                            <td><span class="badge badge-primary"><?php echo e($row->users_count); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="2" class="text-center">Aucune donnée</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if(count($usersData) > 0): ?>
                    <tfoot>
                        <tr class="table-primary">
                            <th>Total</th>
                            <th><span class="badge badge-primary"><?php echo e($totals['users']); ?></span></th>
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
    const usersChartCtx = document.getElementById('usersChart');
    if (usersChartCtx) {
        const chartData = <?php echo json_encode($chartData ?? [], 15, 512) ?>;
        new Chart(usersChartCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartData.labels || [],
                datasets: [{
                    label: 'Nombre d\'utilisateurs',
                    data: chartData.values || [],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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

<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/reports/users.blade.php ENDPATH**/ ?>