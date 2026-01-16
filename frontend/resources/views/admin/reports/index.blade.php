@extends('admin.layouts.app')

@section('title', 'Rapports et Statistiques')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rapports et Statistiques</h4>
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
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-primary btn-sm btn-block">
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
                    <a href="{{ route('admin.reports.users') }}" class="btn btn-info btn-sm btn-block">
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
                    <a href="{{ route('admin.reports.products') }}" class="btn btn-success btn-sm btn-block">
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
                                <i class="fas fa-file-invoice"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Factures</p>
                                <h4 class="card-title">Rapport</h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?php echo e(route('admin.reports.invoices')); ?>" class="btn btn-warning btn-sm btn-block">
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
    
    <!-- Statistiques détaillées -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <h4 class="card-title">Statistiques Détaillées</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-chart-bar"></i> Statistiques Générales</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <td><strong>Total des ventes</strong></td>
                                            <td class="text-end">
                                                <span class="badge badge-success"><?php echo e(number_format($stats['total_revenue'] ?? 0, 0, ',', ' ')); ?> FCFA</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ventes ce mois</strong></td>
                                            <td class="text-end">
                                                <span class="badge badge-primary"><?php echo e(number_format($stats['monthly_revenue'] ?? 0, 0, ',', ' ')); ?> FCFA</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Commandes totales</strong></td>
                                            <td class="text-end">
                                                <span class="badge badge-info"><?php echo e(number_format($stats['total_orders'] ?? 0, 0, ',', ' ')); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Commandes complétées</strong></td>
                                            <td class="text-end">
                                                <span class="badge badge-success"><?php echo e(number_format($stats['completed_orders'] ?? 0, 0, ',', ' ')); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Commandes en attente</strong></td>
                                            <td class="text-end">
                                                <span class="badge badge-warning"><?php echo e(number_format($stats['pending_orders'] ?? 0, 0, ',', ' ')); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Produits en stock faible</strong></td>
                                            <td class="text-end">
                                                <span class="badge badge-danger"><?php echo e(number_format($stats['low_stock_products'] ?? 0, 0, ',', ' ')); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total factures</strong></td>
                                            <td class="text-end">
                                                <span class="badge badge-warning"><?php echo e(number_format($stats['total_invoices'] ?? 0, 0, ',', ' ')); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Factures payées</strong></td>
                                            <td class="text-end">
                                                <span class="badge badge-success"><?php echo e(number_format($stats['paid_invoices'] ?? 0, 0, ',', ' ')); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Montant factures payées</strong></td>
                                            <td class="text-end">
                                                <span class="badge badge-primary"><?php echo e(number_format($stats['total_invoice_amount'] ?? 0, 0, ',', ' ')); ?> FCFA</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Factures en retard</strong></td>
                                            <td class="text-end">
                                                <span class="badge badge-danger"><?php echo e(number_format($stats['overdue_invoices'] ?? 0, 0, ',', ' ')); ?></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-bolt"></i> Actions Rapides</h5>
                            <div class="d-grid gap-2">
                                <a href="<?php echo e(route('admin.orders.index')); ?>" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-list"></i> Voir toutes les commandes
                                </a>
                                <a href="<?php echo e(route('admin.products.index')); ?>" class="btn btn-outline-success btn-block">
                                    <i class="fas fa-box"></i> Gérer les produits
                                </a>
                                <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-info btn-block">
                                    <i class="fas fa-users"></i> Gérer les utilisateurs
                                </a>
                                <a href="<?php echo e(route('admin.stores.index')); ?>" class="btn btn-outline-warning btn-block">
                                    <i class="fas fa-store"></i> Gérer les boutiques
                                </a>
                                <a href="<?php echo e(route('admin.statistics.index')); ?>" class="btn btn-outline-secondary btn-block">
                                    <i class="fas fa-chart-pie"></i> Voir les statistiques détaillées
                                </a>
                                    <i class="fas fa-store"></i> Gérer les boutiques
                                </a>
                                <a href="<?php echo e(route('admin.statistics.index')); ?>" class="btn btn-outline-secondary btn-block">
                                    <i class="fas fa-chart-pie"></i> Voir les statistiques détaillées
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportData() {
    // Menu déroulant pour choisir le type d'export
    const types = ['sales', 'users', 'products'];
    const typeNames = {
        'sales': 'Ventes',
        'users': 'Utilisateurs',
        'products': 'Produits'
    };
    
    let message = 'Choisissez le type de rapport à exporter:\n\n';
    types.forEach((type, index) => {
        message += (index + 1) + '. ' + typeNames[type] + '\n';
    });
    message += '\nOu cliquez sur les boutons "Export CSV" dans chaque section de rapport.';
    
    alert(message);
}
</script>
@endpush
