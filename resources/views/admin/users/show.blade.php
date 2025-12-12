@extends('admin.layouts.app')

@section('title', 'Détails de l\'Utilisateur')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Détails de l'Utilisateur</h4>
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
                <a href="{{ route('admin.users.index') }}">Utilisateurs</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span>Détails</span>
            </li>
        </ul>
    </div>

    <div class="row">
        <!-- Informations générales -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informations Générales</h4>
                </div>
                <div class="card-body">
                    <!-- Photo de profil -->
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <div class="profile-photo-container">
                                @if($user->profile_pic_url)
                                    <img src="{{ str_starts_with($user->profile_pic_url, 'http') ? $user->profile_pic_url : asset($user->profile_pic_url) }}" alt="Photo de profil" class="profile-photo rounded-circle" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #ff6b35;">
                                @else
                                    <div class="profile-photo-placeholder rounded-circle d-flex align-items-center justify-content-center" style="width: 120px; height: 120px; background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%); color: white; font-size: 48px; font-weight: bold; margin: 0 auto; border: 4px solid #ff6b35;">
                                        {{ strtoupper(substr($user->nom ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->prenoms ?? '', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <h5 class="mt-3 mb-1">{{ $user->nom ?? 'N/A' }} {{ $user->prenoms ?? '' }}</h5>
                            <p class="text-muted mb-0">
                                @if($user->is_admin)
                                    <span class="badge badge-danger">Administrateur</span>
                                @elseif($user->is_seller)
                                    <span class="badge badge-warning">Vendeur</span>
                                @else
                                    <span class="badge badge-info">Client</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Nom :</strong></label>
                                <p>{{ $user->nom ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Prénoms :</strong></label>
                                <p>{{ $user->prenoms ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Email :</strong></label>
                                <p>{{ $user->email ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Téléphone :</strong></label>
                                <p>{{ $user->telephone ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Ville :</strong></label>
                                <p>{{ $user->ville ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Date d'inscription :</strong></label>
                                <p>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutique (si vendeur) -->
            @if($user->is_seller && $user->store)
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="card-title">Boutique</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Nom de la boutique :</strong></label>
                                <p>{{ $user->store->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Statut :</strong></label>
                                <span class="badge badge-{{ $user->store->status === 'active' ? 'success' : ($user->store->status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($user->store->status ?? 'N/A') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    @if($user->store->description)
                    <div class="form-group">
                        <label><strong>Description :</strong></label>
                        <p>{{ $user->store->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Commandes récentes -->
            @if($user->orders && $user->orders->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="card-title">Commandes Récentes</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>N° Commande</th>
                                    <th>Date</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->orders->take(5) as $order)
                                <tr>
                                    <td>#{{ $order->order_number ?? $order->id }}</td>
                                    <td>{{ $order->created_at ? $order->created_at->format('d/m/Y') : 'N/A' }}</td>
                                    <td>{{ number_format($order->total ?? 0, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        <span class="badge badge-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'info') }}">
                                            {{ ucfirst($order->status ?? 'N/A') }}
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

        <!-- Actions et statut -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Actions</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label><strong>Rôle :</strong></label>
                        <p>
                            @if($user->is_admin)
                                <span class="badge badge-danger">Administrateur</span>
                            @elseif($user->is_seller)
                                <span class="badge badge-warning">Vendeur</span>
                            @else
                                <span class="badge badge-info">Client</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Statut :</strong></label>
                        <p>
                            @if($user->is_verified)
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-danger">Inactif</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="form-group">
                        <label><strong>Email vérifié :</strong></label>
                        <p>
                            @if($user->email_verified_at)
                                <span class="badge badge-success">Oui</span>
                            @else
                                <span class="badge badge-warning">Non</span>
                            @endif
                        </p>
                    </div>
                    
                    <hr>
                    
                    <div class="btn-group-vertical w-100">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mb-2">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                        
                        <button type="button" class="btn btn-warning mb-2" onclick="editUser({{ $user->id }})">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        
                        <button type="button" class="btn btn-{{ $user->is_verified ? 'secondary' : 'success' }} mb-2" onclick="toggleUserStatus({{ $user->id }})">
                            <i class="fas fa-{{ $user->is_verified ? 'ban' : 'check' }}"></i> 
                            {{ $user->is_verified ? 'Désactiver' : 'Activer' }}
                        </button>
                        
                        @if(!$user->is_admin)
                        <button type="button" class="btn btn-danger" onclick="deleteUser({{ $user->id }})">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editUser(userId) {
    // Fonction d'édition à implémenter
    alert('Fonction d\'édition à implémenter pour l\'utilisateur ' + userId);
}

function toggleUserStatus(userId) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de cet utilisateur ?')) {
        fetch(`/admin/users/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la mise à jour');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la mise à jour');
        });
    }
}

function deleteUser(userId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/admin/users';
            } else {
                alert('Erreur lors de la suppression');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la suppression');
        });
    }
}
</script>
@endpush
@endsection
