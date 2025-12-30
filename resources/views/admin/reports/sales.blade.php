@extends('admin.layouts.app')

@section('title', 'Rapport des ventes')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rapport des ventes</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="{{ route('admin.reports.index') }}">Rapports</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Ventes</span></li>
        </ul>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.sales') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from', now()->subMonth()->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>En cours</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Livrées</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulées</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Catégorie</label>
                    <select name="category_id" id="report_category" class="form-select">
                        <option value="">Toutes</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string)request('category_id') === (string)$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sous-catégorie</label>
                    <select name="subcategory_id" id="report_subcategory" class="form-select">
                        <option value="">Toutes</option>
                    </select>
                </div>
                <div class="col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrer</button>
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Réinitialiser</a>
                    <a href="{{ route('admin.reports.export', 'sales') }}" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques principales -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h5 class="card-category">Total Commandes</h5>
                    <h3 class="card-title">{{ number_format($totals['orders'] ?? 0, 0, ',', ' ') }}</h3>
                    <p class="text-muted">Payées: {{ number_format($totals['paid_orders'] ?? 0, 0, ',', ' ') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h5 class="card-category">Chiffre d'Affaires</h5>
                    <h3 class="card-title">{{ number_format($totals['amount'] ?? 0, 0, ',', ' ') }} <small>FCFA</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5 class="card-category">Panier Moyen</h5>
                    <h3 class="card-title">{{ number_format($totals['avg_order_value'] ?? 0, 0, ',', ' ') }} <small>FCFA</small></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-warning">
                        <i class="fas fa-box"></i>
                    </div>
                    <h5 class="card-category">Articles Vendus</h5>
                    <h3 class="card-title">{{ number_format($totals['total_items'] ?? 0, 0, ',', ' ') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Évolution des Ventes</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 400px">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Produits et Catégories -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Top 10 Produits</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Qté</th>
                                    <th>Revenus</th>
                                    <th>Commandes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts ?? [] as $product)
                                <tr>
                                    <td>{{ Str::limit($product->name, 30) }}</td>
                                    <td><span class="badge badge-info">{{ $product->total_quantity }}</span></td>
                                    <td>{{ number_format($product->total_revenue, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ $product->orders_count }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center">Aucun produit</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Top 10 Catégories</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th>Qté</th>
                                    <th>Revenus</th>
                                    <th>Commandes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCategories ?? [] as $category)
                                <tr>
                                    <td>{{ Str::limit($category->name, 30) }}</td>
                                    <td><span class="badge badge-primary">{{ $category->total_quantity }}</span></td>
                                    <td>{{ number_format($category->total_revenue, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ $category->orders_count }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center">Aucune catégorie</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau détaillé -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Détail des Ventes par Jour</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Nb commandes</th>
                            <th>Montant total</th>
                            <th>Panier moyen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesData as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                            <td><span class="badge badge-primary">{{ $row->orders_count }}</span></td>
                            <td><strong>{{ number_format($row->total_amount, 0, ',', ' ') }} FCFA</strong></td>
                            <td>{{ number_format($row->avg_amount ?? 0, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center">Aucune donnée</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($salesData) > 0)
                    <tfoot>
                        <tr class="table-primary">
                            <th>Total</th>
                            <th><span class="badge badge-primary">{{ $totals['orders'] }}</span></th>
                            <th><strong>{{ number_format($totals['amount'], 0, ',', ' ') }} FCFA</strong></th>
                            <th>{{ number_format($totals['avg_order_value'], 0, ',', ' ') }} FCFA</th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const categories = @json($categoriesJson);

document.addEventListener('DOMContentLoaded', () => {
    // Gestion des sous-catégories
    const c = document.getElementById('report_category');
    const s = document.getElementById('report_subcategory');
    if (c && s) {
        const selectedSub = '{{ request('subcategory_id') }}';
        function fillSubs(id){
            s.innerHTML = '<option value="">Toutes</option>';
            const cat = categories.find(x => String(x.id) === String(id));
            if (cat){
                cat.subcategories.forEach(sc => {
                    const opt = document.createElement('option');
                    opt.value = sc.id; opt.textContent = sc.name;
                    if (String(selectedSub) === String(sc.id)) opt.selected = true;
                    s.appendChild(opt);
                });
            }
        }
        if (c.value) fillSubs(c.value);
        c.addEventListener('change', e => fillSubs(e.target.value));
    }

    // Graphique des ventes
    const salesChartCtx = document.getElementById('salesChart');
    if (salesChartCtx) {
        const chartData = @json($chartData ?? []);
        new Chart(salesChartCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: chartData.labels || [],
                datasets: [
                    {
                        label: 'Revenus (FCFA)',
                        data: chartData.amounts || [],
                        borderColor: '#ff6b35',
                        backgroundColor: 'rgba(255, 107, 53, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y',
                        fill: true
                    },
                    {
                        label: 'Nombre de commandes',
                        data: chartData.orders || [],
                        borderColor: '#17a2b8',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1',
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return 'Revenus: ' + new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FCFA';
                                } else {
                                    return 'Commandes: ' + context.parsed.y;
                                }
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }
});
</script>
@endpush


