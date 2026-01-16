@extends('admin.layouts.app')

@section('title', 'Dashboard Admin - KAZARIA')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Dashboard</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
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
    
    <!-- Statistiques principales -->
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
                                <h4 class="card-title"><?php echo e(number_format($stats['total_users'] ?? 0, 0, ',', ' ')); ?></h4>
                                <?php if(isset($stats['users_growth_rate'])): ?>
                                <p class="card-category">
                                    <span class="text-<?php echo e($stats['users_growth_rate'] >= 0 ? 'success' : 'danger'); ?>">
                                        <i class="fas fa-arrow-<?php echo e($stats['users_growth_rate'] >= 0 ? 'up' : 'down'); ?>"></i>
                                        <?php echo e(abs($stats['users_growth_rate'])); ?>%
                                    </span> ce mois
                                </p>
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
                                <i class="fas fa-store"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Boutiques</p>
                                <h4 class="card-title"><?php echo e(number_format($stats['total_stores'] ?? 0, 0, ',', ' ')); ?></h4>
                                <?php if(isset($stats['sellers_count'])): ?>
                                <p class="card-category"><?php echo e($stats['sellers_count']); ?> vendeurs</p>
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
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Commandes</p>
                                <h4 class="card-title"><?php echo e(number_format($stats['total_orders'] ?? 0, 0, ',', ' ')); ?></h4>
                                <?php if(isset($stats['orders_growth_rate'])): ?>
                                <p class="card-category">
                                    <span class="text-<?php echo e($stats['orders_growth_rate'] >= 0 ? 'success' : 'danger'); ?>">
                                        <i class="fas fa-arrow-<?php echo e($stats['orders_growth_rate'] >= 0 ? 'up' : 'down'); ?>"></i>
                                        <?php echo e(abs($stats['orders_growth_rate'])); ?>%
                                    </span> ce mois
                                </p>
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
                            <div class="icon-big text-center icon-secondary bubble-shadow-small">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Produits</p>
                                <h4 class="card-title"><?php echo e(number_format($stats['total_products'] ?? 0, 0, ',', ' ')); ?></h4>
                                <?php if(isset($stats['active_products'])): ?>
                                <p class="card-category"><?php echo e($stats['active_products']); ?> actifs</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques de revenus -->
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Revenus Totaux</p>
                                <h4 class="card-title"><?php echo e(number_format($stats['total_revenue'] ?? 0, 0, ',', ' ')); ?> <small>FCFA</small></h4>
                                <?php if(isset($stats['revenue_growth_rate'])): ?>
                                <p class="card-category">
                                    <span class="text-<?php echo e($stats['revenue_growth_rate'] >= 0 ? 'success' : 'danger'); ?>">
                                        <i class="fas fa-arrow-<?php echo e($stats['revenue_growth_rate'] >= 0 ? 'up' : 'down'); ?>"></i>
                                        <?php echo e(abs($stats['revenue_growth_rate'])); ?>%
                                    </span> ce mois
                                </p>
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
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Revenus ce Mois</p>
                                <h4 class="card-title"><?php echo e(number_format($stats['monthly_revenue'] ?? 0, 0, ',', ' ')); ?> <small>FCFA</small></h4>
                                <p class="card-category">
                                    Aujourd'hui: <?php echo e(number_format($stats['today_revenue'] ?? 0, 0, ',', ' ')); ?> FCFA
                                </p>
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
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Panier Moyen</p>
                                <h4 class="card-title"><?php echo e(number_format($stats['average_order_value'] ?? 0, 0, ',', ' ')); ?> <small>FCFA</small></h4>
                                <?php if(isset($stats['total_items_sold'])): ?>
                                <p class="card-category"><?php echo e(number_format($stats['total_items_sold'], 0, ',', ' ')); ?> articles vendus</p>
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
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Stock Faible</p>
                                <h4 class="card-title"><?php echo e(number_format($stats['low_stock_products'] ?? 0, 0, ',', ' ')); ?></h4>
                                <?php if(isset($stats['out_of_stock_products'])): ?>
                                <p class="card-category"><?php echo e($stats['out_of_stock_products']); ?> en rupture</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques des commandes par statut -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Répartition des Commandes par Statut</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body text-center">
                                    <div class="icon-big text-center icon-warning">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <h4 class="card-title"><?php echo e(number_format($stats['pending_orders'] ?? 0, 0, ',', ' ')); ?></h4>
                                    <p class="card-category">En attente</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body text-center">
                                    <div class="icon-big text-center icon-info">
                                        <i class="fas fa-sync-alt"></i>
                                    </div>
                                    <h4 class="card-title"><?php echo e(number_format($stats['processing_orders'] ?? 0, 0, ',', ' ')); ?></h4>
                                    <p class="card-category">En traitement</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body text-center">
                                    <div class="icon-big text-center icon-success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h4 class="card-title"><?php echo e(number_format($stats['delivered_orders'] ?? 0, 0, ',', ' ')); ?></h4>
                                    <p class="card-category">Livrées</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="card card-stats card-round">
                                <div class="card-body text-center">
                                    <div class="icon-big text-center icon-danger">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <h4 class="card-title"><?php echo e(number_format($stats['cancelled_orders'] ?? 0, 0, ',', ' ')); ?></h4>
                                    <p class="card-category">Annulées</p>
                                </div>
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
                            <a href="{{ route('admin.reports.sales') }}" class="btn btn-label-success btn-round btn-sm me-2">
                                <span class="btn-label">
                                    <i class="fa fa-chart-bar"></i>
                                </span>
                                Voir le rapport
                            </a>
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-label-info btn-round btn-sm">
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
                    <div class="card-category">{{ date('F Y') }}</div>
                </div>
                <div class="card-body pb-0">
                    <div class="mb-4 mt-2">
                        <h1>{{ number_format($stats['monthly_revenue'] ?? 0, 0, ',', ' ') }} FCFA</h1>
                    </div>
                    <div class="pull-in">
                        <canvas id="monthlySalesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="card card-round">
                <div class="card-body pb-0">
                    <div class="h1 fw-bold float-end text-primary">+{{ $stats['growth_rate'] ?? 0 }}%</div>
                    <h2 class="mb-2">{{ $stats['active_users'] ?? 0 }}</h2>
                    <p class="text-muted">Utilisateurs actifs</p>
                    <div class="pull-in sparkline-fix">
                        <div id="lineChart"></div>
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
                        <div class="card-title">Ventes des 7 Derniers Jours</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 300px">
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Statut des Commandes</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 300px">
                        <canvas id="ordersStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Commandes Récentes</div>
                        <div class="card-tools">
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">Voir tout</a>
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
                                @forelse($recent_orders ?? [] as $order)
                                <tr>
                                    <td>#<?php echo e(Str::limit($order->order_number, 12)); ?></td>
                                    <td><?php echo e(Str::limit(($order->user->prenoms ?? 'N/A') . ' ' . ($order->user->nom ?? ''), 20)); ?></td>
                                    <td><?php echo e(number_format($order->total, 0, ',', ' ')); ?> FCFA</td>
                                    <td>
                                        @php
                                            $status = $order->status;
                                            $statusMap = [
                                                'pending'   => ['warning',  'En attente'],
                                                'processing'=> ['info',     'En cours'],
                                                'delivered' => ['success',  'Livrée'],
                                                'cancelled' => ['danger',   'Annulée'],
                                            ];
                                            [$badgeClass, $badgeLabel] = $statusMap[$status] ?? ['secondary', ucfirst($status)];
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">{{ $badgeLabel }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Aucune commande récente</td>
                                </tr>
                                @endforelse
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
                        <div class="card-title">Top Produits</div>
                        <div class="card-tools">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-primary">Voir tout</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Ventes</th>
                                    <th>Revenus</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($popular_products ?? [] as $product)
                                <tr>
                                    <td><?php echo e(Str::limit($product->name, 20)); ?></td>
                                    <td><span class="badge badge-info"><?php echo e($product->sales_count ?? 0); ?></span></td>
                                    <td><?php echo e(number_format($product->revenue ?? 0, 0, ',', ' ')); ?> FCFA</td>
                                    <td>
                                        <span class="badge badge-<?php echo e($product->stock > 0 ? ($product->stock < 10 ? 'warning' : 'success') : 'danger'); ?>">
                                            <?php echo e($product->stock); ?>

                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Aucun produit populaire</td>
                                </tr>
                                @endforelse
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
                        <div class="card-title">Top Clients</div>
                        <div class="card-tools">
                            <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-sm btn-primary">Voir tout</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Commandes</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $top_customers ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e(Str::limit(($customer->prenoms ?? 'N/A') . ' ' . ($customer->nom ?? ''), 25)); ?></td>
                                    <td><span class="badge badge-primary"><?php echo e($customer->orders_count ?? 0); ?></span></td>
                                    <td><?php echo e(number_format($customer->total_spent ?? 0, 0, ',', ' ')); ?> FCFA</td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="text-center">Aucun client</td>
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
@endsection

@push('scripts')
<script>
    // Graphique des ventes (6 derniers mois)
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?php echo $chart_data['sales_labels'] ?? "['Jan','Fév','Mar','Avr','Mai','Jun']"; ?>,
                datasets: [{
                    label: 'Ventes (FCFA)',
                    data: [<?php echo e($chart_data['sales'] ?? '0,0,0,0,0,0'); ?>],
                    borderColor: '#ff6b35',
                    backgroundColor: 'rgba(255, 107, 53, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return 'Ventes: ' + new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FCFA';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                            }
                        }
                    }
                }
            }
        });
    }

    // Graphique des ventes mensuelles
    const monthlyCtx = document.getElementById('monthlySalesChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx.getContext('2d'), {
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
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Graphique des ventes quotidiennes (7 derniers jours)
    const dailySalesCtx = document.getElementById('dailySalesChart');
    if (dailySalesCtx) {
        new Chart(dailySalesCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?php echo $chart_data['daily_labels'] ?? "[]"; ?>,
                datasets: [{
                    label: 'Revenus (FCFA)',
                    data: [<?php echo e($chart_data['daily_sales'] ?? ''); ?>],
                    backgroundColor: 'rgba(255, 107, 53, 0.8)',
                    borderColor: '#ff6b35',
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenus: ' + new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FCFA';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                            }
                        }
                    }
                }
            }
        });
    }

    // Graphique des statuts de commandes
    const ordersStatusCtx = document.getElementById('ordersStatusChart');
    if (ordersStatusCtx) {
        const ordersByStatus = <?php echo $chart_data['orders_by_status'] ?? '{}'; ?>;
        const statusLabels = {
            'pending': 'En attente',
            'processing': 'En cours',
            'delivered': 'Livrées',
            'cancelled': 'Annulées',
            'paid': 'Payées',
            'shipped': 'Expédiées',
            'refunded': 'Remboursées'
        };
        
        const labels = Object.keys(ordersByStatus).map(key => statusLabels[key] || key);
        const data = Object.values(ordersByStatus);
        
        new Chart(ordersStatusCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        '#ffc107',
                        '#17a2b8',
                        '#28a745',
                        '#dc3545',
                        '#007bff',
                        '#6f42c1',
                        '#fd7e14'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Graphique linéaire des utilisateurs actifs
    if (typeof $("#lineChart") !== 'undefined' && $("#lineChart").length > 0) {
        $("#lineChart").sparkline([<?php echo e($chart_data['active_users'] ?? '0,0,0,0,0,0,0'); ?>], {
            type: "line",
            height: "70",
            width: "100%",
            lineWidth: "2",
            lineColor: "#ff6b35",
            fillColor: "rgba(255, 107, 53, 0.14)",
        });
    }
</script>
@endpush

