@extends('admin.layouts.app')

@section('title', 'Gestion des Boutiques')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Gestion des Boutiques</h4>
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
                <span>Boutiques</span>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestion des Boutiques</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nouvelle Boutique
                        </button>
                    </div>
                </div>
                
                <!-- Filtres -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('admin.stores.index') }}" class="row g-3">
                        <div class="col-md-2">
                            <input type="text" class="form-control" name="search" placeholder="Rechercher par nom..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="status">
                                <option value="">Tous les statuts</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendue</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="validation">
                                <option value="">Toutes les validations</option>
                                <option value="pending" {{ request('validation') === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="approved" {{ request('validation') === 'approved' ? 'selected' : '' }}>Approuvée</option>
                                <option value="rejected" {{ request('validation') === 'rejected' ? 'selected' : '' }}>Rejetée</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="documents">
                                <option value="">Tous les documents</option>
                                <option value="complete" {{ request('documents') === 'complete' ? 'selected' : '' }}>Documents complets</option>
                                <option value="incomplete" {{ request('documents') === 'incomplete' ? 'selected' : '' }}>Documents incomplets</option>
                                <option value="no_documents" {{ request('documents') === 'no_documents' ? 'selected' : '' }}>Aucun document</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.stores.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Effacer
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Logo</th>
                                    <th>Nom</th>
                                    <th>Propriétaire</th>
                                    <th>Email</th>
                                    <th>Statut</th>
                                    <th>Date création</th>
                                    <th>Documents</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stores as $store)
                                <tr>
                                    <td>{{ $store->id }}</td>
                                    <td>
                                        @if($store->logo)
                                            <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="img-thumbnail" width="50">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="fas fa-store text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $store->name }}</strong>
                                        @if($store->is_official)
                                            <span class="badge bg-warning text-dark ms-2" title="Boutique officielle">
                                                <i class="fas fa-certificate"></i> Officielle
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $store->user->prenoms ?? 'N/A' }} {{ $store->user->nom ?? '' }}</td>
                                    <td>{{ $store->user->email ?? 'N/A' }}</td>
                                    <td>
                                        @if($store->approved_at)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Approuvée
                                            </span>
                                        @elseif($store->rejected_at)
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times me-1"></i>Rejetée
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>En attente
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $store->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($store->logo)
                                                <span class="badge bg-info text-white" title="Logo fourni">
                                                    <i class="fas fa-image me-1"></i>Logo
                                                </span>
                                            @endif
                                            @if($store->banner)
                                                <span class="badge bg-info text-white" title="Bannière fournie">
                                                    <i class="fas fa-image me-1"></i>Bannière
                                                </span>
                                            @endif
                                            @if($store->dfe_document)
                                                <span class="badge bg-success text-white" title="Document DFE fourni">
                                                    <i class="fas fa-file-pdf me-1"></i>DFE
                                                </span>
                                            @endif
                                            @if($store->commerce_register)
                                                <span class="badge bg-success text-white" title="Registre de commerce fourni">
                                                    <i class="fas fa-file-pdf me-1"></i>RC
                                                </span>
                                            @endif
                                            @if(!$store->logo && !$store->banner && !$store->dfe_document && !$store->commerce_register)
                                                <span class="text-muted small">Aucun document</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.stores.show', $store) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(!$store->approved_at && !$store->rejected_at)
                                                <form action="{{ route('admin.stores.approve', $store) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approuver cette boutique ?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $store->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('admin.stores.toggle-status', $store) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-{{ $store->status === 'active' ? 'warning' : 'success' }} btn-sm" 
                                                            onclick="return confirm('{{ $store->status === 'active' ? 'Suspendre' : 'Réactiver' }} cette boutique ?')">
                                                        <i class="fas fa-{{ $store->status === 'active' ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                @if($store->approved_at)
                                                    <form action="{{ route('admin.stores.toggle-official', $store) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-{{ $store->is_official ? 'secondary' : 'warning' }} btn-sm" 
                                                                title="{{ $store->is_official ? 'Désactiver boutique officielle' : 'Activer boutique officielle' }}"
                                                                onclick="return confirm('{{ $store->is_official ? 'Désactiver le statut officiel' : 'Activer le statut officiel' }} pour cette boutique ?')">
                                                            <i class="fas fa-certificate"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Aucune boutique trouvée</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals de rejet -->
@foreach($stores as $store)
@if(!$store->approved_at && !$store->rejected_at)
<div class="modal fade" id="rejectModal{{ $store->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $store->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel{{ $store->id }}">Rejeter la boutique</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.stores.reject', $store) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Vous êtes sur le point de rejeter la boutique <strong>{{ $store->name }}</strong>.</p>
                    <div class="mb-3">
                        <label for="rejection_reason{{ $store->id }}" class="form-label">Raison du rejet <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason{{ $store->id }}" name="rejection_reason" rows="4" 
                                  placeholder="Expliquez pourquoi cette boutique est rejetée..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Rejeter la boutique</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

@endsection
