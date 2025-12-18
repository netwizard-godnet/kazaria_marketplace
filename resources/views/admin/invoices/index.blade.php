@extends('admin.layouts.app')

@section('title', 'Gestion des Factures')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Gestion des Factures</h4>
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
                <span>Factures</span>
            </li>
        </ul>
    </div>
    
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-warning">
                                <i class="flaticon-file text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Total</p>
                                <h4 class="card-title">{{ $stats['total'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-secondary">
                                <i class="flaticon-edit text-secondary"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Brouillons</p>
                                <h4 class="card-title">{{ $stats['draft'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-info">
                                <i class="flaticon-paper-plane text-info"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Envoyées</p>
                                <h4 class="card-title">{{ $stats['sent'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-success">
                                <i class="flaticon-check text-success"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Payées</p>
                                <h4 class="card-title">{{ $stats['paid'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-danger">
                                <i class="flaticon-time text-danger"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">En retard</p>
                                <h4 class="card-title">{{ $stats['overdue'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-primary">
                                <i class="flaticon-credit-card text-primary"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats">
                            <div class="numbers">
                                <p class="card-category">Montant payé</p>
                                <h4 class="card-title">{{ number_format($stats['total_amount'], 0, ',', ' ') }} FCFA</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Liste des Factures</h3>
                    <div class="card-tools">
                        @if(canAccess('create_invoices'))
                        <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Créer une facture
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtres -->
                    <form method="GET" action="{{ route('admin.invoices.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-control">
                                    <option value="">Tous les statuts</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Envoyée</option>
                                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payée</option>
                                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>En retard</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulée</option>
                                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Remboursée</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date début</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Recherche</label>
                                <input type="text" name="search" class="form-control" placeholder="N° facture, client..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
                                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-sm">Effacer</a>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>N° Facture</th>
                                    <th>Client</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Date d'émission</th>
                                    <th>Date d'échéance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                <tr>
                                    <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                    <td>
                                        {{ $invoice->client_name }}<br>
                                        <small class="text-muted">{{ $invoice->client_email }}</small>
                                    </td>
                                    <td><strong>{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $invoice->status_class }}">
                                            {{ $invoice->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($invoice->due_date)
                                            {{ $invoice->due_date->format('d/m/Y') }}
                                            @if($invoice->isOverdue())
                                                <span class="badge bg-danger">En retard</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-info btn-sm" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(canAccess('edit_invoices'))
                                            <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-warning btn-sm" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif
                                            <a href="{{ route('admin.invoices.download', $invoice) }}" class="btn btn-success btn-sm" title="Télécharger PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            @if(canAccess('delete_invoices'))
                                            @if($invoice->status !== 'paid')
                                            <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ? Cette action est irréversible.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                            @else
                                            <button type="button" class="btn btn-danger btn-sm" disabled title="Impossible de supprimer une facture payée">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <p class="text-muted py-4">Aucune facture trouvée.</p>
                                        @if(canAccess('create_invoices'))
                                        <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Créer une facture
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

