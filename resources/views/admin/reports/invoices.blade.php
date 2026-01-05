@extends('admin.layouts.app')

@section('title', 'Rapport des factures')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Rapport des Factures</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><a href="{{ route('admin.reports.index') }}">Rapports</a></li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>Factures</span></li>
        </ul>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.invoices') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from', now()->subMonth()->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Envoyée</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payée</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>En retard</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Remboursée</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrer</button>
                    <a href="{{ route('admin.reports.invoices') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Réinitialiser</a>
                    <a href="{{ route('admin.reports.export', 'invoices') }}" class="btn btn-success"><i class="fas fa-download"></i> Export CSV</a>
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
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h5 class="card-category">Total Factures</h5>
                    <h3 class="card-title">{{ number_format($totals['invoices'] ?? 0, 0, ',', ' ') }}</h3>
                    <p class="text-muted">Payées: {{ number_format($totals['paid_invoices'] ?? 0, 0, ',', ' ') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h5 class="card-category">Montant Total</h5>
                    <h3 class="card-title">{{ number_format($totals['total_amount'] ?? 0, 0, ',', ' ') }} <small>FCFA</small></h3>
                    <p class="text-muted">Payé: {{ number_format($totals['paid_amount'] ?? 0, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h5 class="card-category">Montant Impayé</h5>
                    <h3 class="card-title">{{ number_format($totals['unpaid_amount'] ?? 0, 0, ',', ' ') }} <small>FCFA</small></h3>
                    <p class="text-muted">En retard: {{ number_format($totals['overdue_invoices'] ?? 0, 0, ',', ' ') }} factures</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body text-center">
                    <div class="icon-big text-center icon-info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5 class="card-category">Facture Moyenne</h5>
                    <h3 class="card-title">{{ number_format($totals['avg_invoice_amount'] ?? 0, 0, ',', ' ') }} <small>FCFA</small></h3>
                    <p class="text-muted">En attente: {{ number_format($totals['pending_invoices'] ?? 0, 0, ',', ' ') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Évolution des Factures</h4>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 400px">
                        <canvas id="invoicesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Clients et Factures en Retard -->
    <div class="row mb-3">
        <div class="col-md-6">
            @if(isset($topClients) && $topClients->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Top 10 Clients par Factures</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Nb Factures</th>
                                    <th>Total Facturé</th>
                                    <th>Total Payé</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topClients as $client)
                                <tr>
                                    <td>{{ ($client->prenoms ?? '') . ' ' . ($client->nom ?? '') }}</td>
                                    <td><span class="badge badge-primary">{{ $client->invoices_count }}</span></td>
                                    <td>{{ number_format($client->total_invoiced, 0, ',', ' ') }} FCFA</td>
                                    <td><strong class="text-success">{{ number_format($client->total_paid, 0, ',', ' ') }} FCFA</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <div class="col-md-6">
            @if(isset($overdueInvoices) && $overdueInvoices->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title text-danger">Factures en Retard</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Facture</th>
                                    <th>Client</th>
                                    <th>Montant</th>
                                    <th>Échéance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueInvoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ ($invoice->user->prenoms ?? '') . ' ' . ($invoice->user->nom ?? '') }}</td>
                                    <td><strong>{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</strong></td>
                                    <td>
                                        <span class="badge badge-danger">
                                            {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Tableau détaillé -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Détail des Factures par Jour</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Nb factures</th>
                            <th>Montant total</th>
                            <th>Montant payé</th>
                            <th>Facture moyenne</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoicesData as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                            <td><span class="badge badge-primary">{{ $row->invoices_count }}</span></td>
                            <td><strong>{{ number_format($row->total_amount, 0, ',', ' ') }} FCFA</strong></td>
                            <td class="text-success"><strong>{{ number_format($row->paid_amount, 0, ',', ' ') }} FCFA</strong></td>
                            <td>{{ number_format($row->avg_amount ?? 0, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">Aucune donnée</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($invoicesData) > 0)
                    <tfoot>
                        <tr class="table-primary">
                            <th>Total</th>
                            <th><span class="badge badge-primary">{{ $totals['invoices'] }}</span></th>
                            <th><strong>{{ number_format($totals['total_amount'], 0, ',', ' ') }} FCFA</strong></th>
                            <th class="text-success"><strong>{{ number_format($totals['paid_amount'], 0, ',', ' ') }} FCFA</strong></th>
                            <th>{{ number_format($totals['avg_invoice_amount'], 0, ',', ' ') }} FCFA</th>
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
    const invoicesChartCtx = document.getElementById('invoicesChart');
    if (invoicesChartCtx) {
        const chartData = @json($chartData ?? []);
        new Chart(invoicesChartCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: chartData.labels || [],
                datasets: [
                    {
                        label: 'Montant Total (FCFA)',
                        data: chartData.amounts || [],
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y',
                        fill: true
                    },
                    {
                        label: 'Montant Payé (FCFA)',
                        data: chartData.paid_amounts || [],
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y',
                        fill: true
                    },
                    {
                        label: 'Nombre de factures',
                        data: chartData.counts || [],
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1',
                        fill: false
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
                                if (context.datasetIndex === 2) {
                                    return 'Nombre: ' + context.parsed.y;
                                } else {
                                    return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FCFA';
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

