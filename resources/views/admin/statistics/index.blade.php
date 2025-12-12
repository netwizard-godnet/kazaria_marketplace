@extends('admin.layouts.app')

@section('title', 'Statistiques - KAZARIA Admin')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Statistiques</h4>
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
                <span>Statistiques</span>
            </li>
        </ul>
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
                                <h4 class="card-title">{{ number_format($stats['total_visits'] ?? 0, 0, ',', ' ') }}</h4>
                                @if(isset($growth['visits_today_vs_yesterday']))
                                    <small class="text-{{ $growth['visits_today_vs_yesterday'] >= 0 ? 'success' : 'danger' }}">
                                        <i class="fas fa-arrow-{{ $growth['visits_today_vs_yesterday'] >= 0 ? 'up' : 'down' }}"></i>
                                        {{ abs($growth['visits_today_vs_yesterday']) }}% vs hier
                                    </small>
                                @endif
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
                                <h4 class="card-title">{{ number_format($stats['total_clicks'] ?? 0, 0, ',', ' ') }}</h4>
                                @if(isset($growth['clicks_today_vs_yesterday']))
                                    <small class="text-{{ $growth['clicks_today_vs_yesterday'] >= 0 ? 'success' : 'danger' }}">
                                        <i class="fas fa-arrow-{{ $growth['clicks_today_vs_yesterday'] >= 0 ? 'up' : 'down' }}"></i>
                                        {{ abs($growth['clicks_today_vs_yesterday']) }}% vs hier
                                    </small>
                                @endif
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
                                <h4 class="card-title">{{ number_format($stats['unique_visitors_this_month'] ?? 0, 0, ',', ' ') }}</h4>
                                <small class="text-muted">Ce mois</small>
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
                                <h4 class="card-title">{{ number_format($stats['total_product_views'] ?? 0, 0, ',', ' ') }}</h4>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques détaillées -->
    <div class="row">
        <div class="col-md-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Visites Aujourd'hui</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <h2 class="text-primary">{{ number_format($stats['total_visits_today'] ?? 0, 0, ',', ' ') }}</h2>
                            <p class="text-muted">Pages visitées</p>
                        </div>
                        <div class="col-6">
                            <h2 class="text-success">{{ number_format($stats['total_clicks_today'] ?? 0, 0, ',', ' ') }}</h2>
                            <p class="text-muted">Clics effectués</p>
                        </div>
                        <div class="col-12 mt-3">
                            <hr>
                            <h5 class="text-muted">Visiteurs uniques aujourd'hui</h5>
                            <h3>{{ number_format($stats['unique_visitors_today'] ?? 0, 0, ',', ' ') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                            <h2 class="text-info">{{ number_format($stats['total_visits_this_week'] ?? 0, 0, ',', ' ') }}</h2>
                            <p class="text-muted">Visites cette semaine</p>
                        </div>
                        <div class="col-6">
                            <h2 class="text-warning">{{ number_format($stats['unique_visitors_this_week'] ?? 0, 0, ',', ' ') }}</h2>
                            <p class="text-muted">Visiteurs uniques</p>
                        </div>
                        <div class="col-12 mt-3">
                            <hr>
                            <h5 class="text-muted">Visites ce mois</h5>
                            <h3>{{ number_format($stats['total_visits_this_month'] ?? 0, 0, ',', ' ') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        <div class="col-md-8">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Visites par Jour (30 derniers jours)</div>
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
                        <div class="card-title">Visites par Heure (24h)</div>
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
                                @forelse($top_pages ?? [] as $index => $page)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $page->page_name ?? $page->page_path }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $page->page_path }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ number_format($page->visit_count, 0, ',', ' ') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Aucune donnée disponible</td>
                                </tr>
                                @endforelse
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
                                @forelse($top_clicked_pages ?? [] as $index => $page)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $page->page_name ?? $page->page_path }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $page->page_path }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-success">{{ number_format($page->total_clicks, 0, ',', ' ') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Aucune donnée disponible</td>
                                </tr>
                                @endforelse
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
                            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-primary">Voir tous</a>
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
                                @forelse($top_products ?? [] as $index => $product)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ Str::limit($product->name, 50) }}</td>
                                    <td>
                                        <span class="badge badge-info">{{ number_format($product->views_count, 0, ',', ' ') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Aucun produit consulté</td>
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
                        <div class="card-title">Interactions IA</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @forelse($ai_interactions ?? [] as $interaction)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ ucfirst($interaction->type) }}</span>
                            <span class="badge badge-primary badge-pill">{{ number_format($interaction->count, 0, ',', ' ') }}</span>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted">
                            Aucune interaction IA
                        </div>
                        @endforelse
                    </div>
                    <div class="mt-3">
                        <h5 class="text-muted">Total Interactions</h5>
                        <h3>{{ number_format($stats['total_ai_interactions'] ?? 0, 0, ',', ' ') }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Graphique des visites par jour
    const visitsByDayCtx = document.getElementById('visitsByDayChart');
    if (visitsByDayCtx) {
        new Chart(visitsByDayCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: @json($chart_data['visits_by_day_labels'] ?? []),
                datasets: [{
                    label: 'Visites',
                    data: @json($chart_data['visits_by_day_values'] ?? []),
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
                labels: @json($chart_data['visits_by_hour_labels'] ?? []),
                datasets: [{
                    label: 'Visites',
                    data: @json($chart_data['visits_by_hour_values'] ?? []),
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
@endpush

