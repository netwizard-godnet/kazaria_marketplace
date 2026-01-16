<?php $__env->startSection('title', 'Statistiques - KAZARIA Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Statistiques</h4>
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
                <span>Statistiques</span>
            </li>
        </ul>
    </div>

    <!-- Filtres -->
    <div class="row">
        <div class="col-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            <i class="fas fa-filter me-2"></i>Filtres
                            <?php if($pagePath || ($period && $period != 'month')): ?>
                                <span class="badge badge-info ms-2">
                                    <i class="fas fa-check-circle me-1"></i>Filtres actifs
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-tools">
                            <?php if($pagePath || ($period && $period != 'month')): ?>
                                <span class="badge badge-warning me-2">
                                    <?php if($pagePath): ?>
                                        Page: <?php echo e($all_pages->firstWhere('page_path', $pagePath)->page_name ?? $pagePath); ?>

                                    <?php endif; ?>
                                    <?php if($period && $period != 'month'): ?>
                                        | Période: <?php echo e(ucfirst($period)); ?>

                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="resetFilters()">
                                <i class="fas fa-redo me-1"></i>Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('admin.statistics.index')); ?>" id="filterForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="period" class="form-label">Période</label>
                                <select class="form-control" id="period" name="period" onchange="toggleCustomDates()">
                                    <option value="today" <?php echo e($period == 'today' ? 'selected' : ''); ?>>Aujourd'hui</option>
                                    <option value="week" <?php echo e($period == 'week' ? 'selected' : ''); ?>>Cette semaine</option>
                                    <option value="month" <?php echo e($period == 'month' ? 'selected' : ''); ?>>Ce mois</option>
                                    <option value="year" <?php echo e($period == 'year' ? 'selected' : ''); ?>>Cette année</option>
                                    <option value="custom" <?php echo e($period == 'custom' ? 'selected' : ''); ?>>Personnalisé</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3" id="dateFromGroup" style="display: <?php echo e($period == 'custom' ? 'block' : 'none'); ?>;">
                                <label for="date_from" class="form-label">Date de début</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="<?php echo e($dateFrom ?? ''); ?>" 
                                       <?php echo e($period == 'custom' ? 'required' : ''); ?>>
                            </div>
                            <div class="col-md-3 mb-3" id="dateToGroup" style="display: <?php echo e($period == 'custom' ? 'block' : 'none'); ?>;">
                                <label for="date_to" class="form-label">Date de fin</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" 
                                       value="<?php echo e($dateTo ?? ''); ?>" 
                                       <?php echo e($period == 'custom' ? 'required' : ''); ?>>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="page_path" class="form-label">Page spécifique</label>
                                <select class="form-control" id="page_path" name="page_path">
                                    <option value="">Toutes les pages</option>
                                    <?php $__currentLoopData = $all_pages ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($page->page_path); ?>" <?php echo e($pagePath == $page->page_path ? 'selected' : ''); ?>>
                                            <?php echo e($page->page_name ?? $page->page_path); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Appliquer les filtres
                                </button>
                                <?php if($pagePath || ($period && $period != 'month')): ?>
                                    <a href="<?php echo e(route('admin.statistics.index')); ?>" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Effacer
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cartes de statistiques principales -->
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-eye"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total Visites</p>
                                <h4 class="card-title"><?php echo e(number_format($stats['total_visits'] ?? 0, 0, ',', ' ')); ?></h4>
                                <?php if(isset($growth['visits_today_vs_yesterday']) && !$pagePath): ?>
                                    <small class="text-<?php echo e($growth['visits_today_vs_yesterday'] >= 0 ? 'success' : 'danger'); ?>">
                                        <i class="fas fa-arrow-<?php echo e($growth['visits_today_vs_yesterday'] >= 0 ? 'up' : 'down'); ?>"></i>
                                        <?php echo e(abs($growth['visits_today_vs_yesterday'])); ?>% vs hier
                                    </small>
                                <?php endif; ?>
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
                                <i class="fas fa-mouse-pointer"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total Clics</p>
                                <h4 class="card-title"><?php echo e(number_format($stats['total_clicks'] ?? 0, 0, ',', ' ')); ?></h4>
                                <?php if(isset($growth['clicks_today_vs_yesterday']) && !$pagePath): ?>
                                    <small class="text-<?php echo e($growth['clicks_today_vs_yesterday'] >= 0 ? 'success' : 'danger'); ?>">
                                        <i class="fas fa-arrow-<?php echo e($growth['clicks_today_vs_yesterday'] >= 0 ? 'up' : 'down'); ?>"></i>
                                        <?php echo e(abs($growth['clicks_today_vs_yesterday'])); ?>% vs hier
                                    </small>
                                <?php endif; ?>
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
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Visiteurs Uniques</p>
                                <h4 class="card-title"><?php echo e(number_format($stats['unique_visitors'] ?? 0, 0, ',', ' ')); ?></h4>
                                <small class="text-muted">
                                    <?php if($pagePath): ?>
                                        Page sélectionnée
                                    <?php elseif($period == 'today'): ?>
                                        Aujourd'hui
                                    <?php elseif($period == 'week'): ?>
                                        Cette semaine
                                    <?php elseif($period == 'month'): ?>
                                        Ce mois
                                    <?php elseif($period == 'year'): ?>
                                        Cette année
                                    <?php else: ?>
                                        Période sélectionnée
                                    <?php endif; ?>
                                </small>
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
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Vues Produits</p>
                                <h4 class="card-title"><?php echo e(number_format($stats['total_product_views'] ?? 0, 0, ',', ' ')); ?></h4>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques détaillées -->
    <?php if(!$pagePath): ?>
    <div class="row">
        <div class="col-md-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            <?php if(isset($stats['total_visits_today'])): ?>
                                Visites Aujourd'hui
                            <?php else: ?>
                                Statistiques de la période
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h2 class="text-primary"><?php echo e(number_format($stats['total_visits_today'] ?? $stats['total_visits'] ?? 0, 0, ',', ' ')); ?></h2>
                            <p class="text-muted">Pages visitées</p>
                        </div>
                        <div class="col-6">
                            <h2 class="text-success"><?php echo e(number_format($stats['total_clicks_today'] ?? $stats['total_clicks'] ?? 0, 0, ',', ' ')); ?></h2>
                            <p class="text-muted">Clics effectués</p>
                        </div>
                        <div class="col-12 mt-3">
                            <hr>
                            <h5 class="text-muted">Visiteurs uniques</h5>
                            <h3><?php echo e(number_format($stats['unique_visitors_today'] ?? $stats['unique_visitors'] ?? 0, 0, ',', ' ')); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php if(isset($stats['total_visits_this_week'])): ?>
        <div class="col-md-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Statistiques Hebdomadaires</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h2 class="text-info"><?php echo e(number_format($stats['total_visits_this_week'] ?? 0, 0, ',', ' ')); ?></h2>
                            <p class="text-muted">Visites cette semaine</p>
                        </div>
                        <div class="col-6">
                            <h2 class="text-warning"><?php echo e(number_format($stats['unique_visitors_this_week'] ?? 0, 0, ',', ' ')); ?></h2>
                            <p class="text-muted">Visiteurs uniques</p>
                        </div>
                        <div class="col-12 mt-3">
                            <hr>
                            <h5 class="text-muted">Visites ce mois</h5>
                            <h3><?php echo e(number_format($stats['total_visits_this_month'] ?? 0, 0, ',', ' ')); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Graphiques -->
    <div class="row">
        <div class="col-md-8">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            Visites par Jour
                            <?php if($pagePath): ?>
                                - <?php echo e($all_pages->firstWhere('page_path', $pagePath)->page_name ?? $pagePath); ?>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 300px">
                        <canvas id="visitsByDayChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">
                            Visites par Heure
                            <?php if($pagePath): ?>
                                - <?php echo e($all_pages->firstWhere('page_path', $pagePath)->page_name ?? $pagePath); ?>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 300px">
                        <canvas id="visitsByHourChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top pages et produits -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Pages les Plus Visitées</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Page</th>
                                    <th>Visites</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $top_pages ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td>
                                        <strong><?php echo e($page->page_name ?? $page->page_path); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo e($page->page_path); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo e(number_format($page->visit_count, 0, ',', ' ')); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Aucune donnée disponible</td>
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
                        <div class="card-title">Pages avec le Plus de Clics</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Page</th>
                                    <th>Clics</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $top_clicked_pages ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td>
                                        <strong><?php echo e($page->page_name ?? $page->page_path); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo e($page->page_path); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-success"><?php echo e(number_format($page->total_clicks, 0, ',', ' ')); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Aucune donnée disponible</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Produits populaires et interactions AI -->
    <div class="row">
        <div class="col-md-8">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Produits les Plus Consultés</div>
                        <div class="card-tools">
                            <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-sm btn-primary">Voir tous</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Produit</th>
                                    <th>Vues</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $top_products ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e(Str::limit($product->name, 50)); ?></td>
                                    <td>
                                        <span class="badge badge-info"><?php echo e(number_format($product->views_count, 0, ',', ' ')); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Aucun produit consulté</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Interactions IA</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php $__empty_1 = true; $__currentLoopData = $ai_interactions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $interaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?php echo e(ucfirst($interaction->type)); ?></span>
                            <span class="badge badge-primary badge-pill"><?php echo e(number_format($interaction->count, 0, ',', ' ')); ?></span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="list-group-item text-center text-muted">
                            Aucune interaction IA
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3">
                        <h5 class="text-muted">Total Interactions</h5>
                        <h3><?php echo e(number_format($stats['total_ai_interactions'] ?? 0, 0, ',', ' ')); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Fonction pour afficher/masquer les champs de dates personnalisées
    function toggleCustomDates() {
        const period = document.getElementById('period').value;
        const dateFromGroup = document.getElementById('dateFromGroup');
        const dateToGroup = document.getElementById('dateToGroup');
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');
        
        if (period === 'custom') {
            dateFromGroup.style.display = 'block';
            dateToGroup.style.display = 'block';
            dateFrom.setAttribute('required', 'required');
            dateTo.setAttribute('required', 'required');
        } else {
            dateFromGroup.style.display = 'none';
            dateToGroup.style.display = 'none';
            dateFrom.removeAttribute('required');
            dateTo.removeAttribute('required');
            dateFrom.value = '';
            dateTo.value = '';
        }
    }
    
    // Initialiser l'état des champs au chargement
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomDates();
    });
    
    // Fonction pour réinitialiser les filtres
    function resetFilters() {
        window.location.href = '<?php echo e(route("admin.statistics.index")); ?>';
    }
    
    // Graphique des visites par jour
    const visitsByDayCtx = document.getElementById('visitsByDayChart');
    if (visitsByDayCtx) {
        new Chart(visitsByDayCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_data['visits_by_day_labels'] ?? [], 15, 512) ?>,
                datasets: [{
                    label: 'Visites',
                    data: <?php echo json_encode($chart_data['visits_by_day_values'] ?? [], 15, 512) ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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

    // Graphique des visites par heure
    const visitsByHourCtx = document.getElementById('visitsByHourChart');
    if (visitsByHourCtx) {
        new Chart(visitsByHourCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_data['visits_by_hour_labels'] ?? [], 15, 512) ?>,
                datasets: [{
                    label: 'Visites',
                    data: <?php echo json_encode($chart_data['visits_by_hour_values'] ?? [], 15, 512) ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.8)',
                    borderColor: '#28a745',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/statistics/index.blade.php ENDPATH**/ ?>