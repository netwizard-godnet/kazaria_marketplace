@extends('admin.layouts.app')

@section('title', 'Rapport utilisateurs')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rapport utilisateurs</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="{{ route('admin.reports.index') }}">Rapports</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Utilisateurs</span></li>
        </ul>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.users') }}" class="row g-3">
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
                    <a href="{{ route('admin.reports.users') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Réinitialiser</a>
                    <a href="{{ route('admin.reports.export', 'users') }}" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</a>
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
                    <h3 class="card-title">{{ number_format($totals['users'] ?? 0, 0, ',', ' ') }}</h3>
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
                    <h3 class="card-title">{{ number_format($totals['total_users'] ?? 0, 0, ',', ' ') }}</h3>
                    <p class="text-muted">Vérifiés: {{ number_format($totals['verified_users'] ?? 0, 0, ',', ' ') }}</p>
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
                    <h3 class="card-title">{{ number_format($totals['sellers'] ?? 0, 0, ',', ' ') }}</h3>
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
                    <h3 class="card-title">{{ number_format($totals['new_today'] ?? 0, 0, ',', ' ') }}</h3>
                    <p class="text-muted">Ce mois: {{ number_format($totals['new_this_month'] ?? 0, 0, ',', ' ') }}</p>
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
    @if(isset($topUsers) && $topUsers->count() > 0)
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
                                @foreach($topUsers as $user)
                                <tr>
                                    <td>{{ ($user->prenoms ?? '') . ' ' . ($user->nom ?? '') }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge badge-primary">{{ $user->orders_count }}</span></td>
                                    <td><strong>{{ number_format($user->total_spent, 0, ',', ' ') }} FCFA</strong></td>
                                    <td>
                                        @if($user->is_verified)
                                            <span class="badge badge-success">Vérifié</span>
                                        @else
                                            <span class="badge badge-warning">Non vérifié</span>
                                        @endif
                                        @if($user->is_seller)
                                            <span class="badge badge-info">Vendeur</span>
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
                        @forelse($usersData as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                            <td><span class="badge badge-primary">{{ $row->users_count }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center">Aucune donnée</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($usersData) > 0)
                    <tfoot>
                        <tr class="table-primary">
                            <th>Total</th>
                            <th><span class="badge badge-primary">{{ $totals['users'] }}</span></th>
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
    const usersChartCtx = document.getElementById('usersChart');
    if (usersChartCtx) {
        const chartData = @json($chartData ?? []);
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
@endpush
