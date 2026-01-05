@extends('admin.layouts.app')

@section('title', 'Rapport produits')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rapport produits</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="{{ route('admin.reports.index') }}">Rapports</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Produits</span></li>
        </ul>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.products') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from', now()->subMonth()->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrer</button>
                    <a href="{{ route('admin.reports.products') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Réinitialiser</a>
                    <a href="{{ route('admin.reports.export', 'products') }}" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</a>
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
                    <h3 class="card-title">{{ number_format($totals['products'] ?? 0, 0, ',', ' ') }}</h3>
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
                    <h3 class="card-title">{{ number_format($totals['total_products'] ?? 0, 0, ',', ' ') }}</h3>
                    <p class="text-muted">Actifs: {{ number_format($totals['active_products'] ?? 0, 0, ',', ' ') }}</p>
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
                    <h3 class="card-title">{{ number_format($totals['low_stock'] ?? 0, 0, ',', ' ') }}</h3>
                    <p class="text-muted">Rupture: {{ number_format($totals['out_of_stock'] ?? 0, 0, ',', ' ') }}</p>
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
                    <h3 class="card-title">{{ number_format($totals['featured_products'] ?? 0, 0, ',', ' ') }}</h3>
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
    @if(isset($topViewed) && $topViewed->count() > 0)
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
                                @foreach($topViewed as $product)
                                <tr>
                                    <td>{{ Str::limit($product->name, 40) }}</td>
                                    <td>{{ number_format($product->price, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        <span class="badge badge-{{ $product->stock > 0 ? ($product->stock < 10 ? 'warning' : 'success') : 'danger' }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                    <td><span class="badge badge-info">{{ number_format($product->views_count ?? 0, 0, ',', ' ') }}</span></td>
                                    <td>
                                        @if($product->is_active)
                                            <span class="badge badge-success">Actif</span>
                                        @else
                                            <span class="badge badge-secondary">Inactif</span>
                                        @endif
                                        @if($product->is_featured)
                                            <span class="badge badge-primary">Mis en avant</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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
                        @forelse($productsData as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                            <td><span class="badge badge-primary">{{ $row->products_count }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center">Aucune donnée</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($productsData) > 0)
                    <tfoot>
                        <tr class="table-primary">
                            <th>Total</th>
                            <th><span class="badge badge-primary">{{ $totals['products'] }}</span></th>
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
document.addEventListener('DOMContentLoaded', () => {
    const productsChartCtx = document.getElementById('productsChart');
    if (productsChartCtx) {
        const chartData = @json($chartData ?? []);
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
@endpush
