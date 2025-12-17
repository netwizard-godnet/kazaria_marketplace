@extends('admin.layouts.app')

@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Gestion des Utilisateurs</h4>
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
                <span>Utilisateurs</span>
            </li>
        </ul>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
                            <p class="mb-0">Total</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['customers'] ?? 0 }}</h4>
                            <p class="mb-0">Clients</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['sellers'] ?? 0 }}</h4>
                            <p class="mb-0">Vendeurs</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-store fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['admins'] ?? 0 }}</h4>
                            <p class="mb-0">Admins</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-shield fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['verified'] ?? 0 }}</h4>
                            <p class="mb-0">Vérifiés</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $stats['unverified'] ?? 0 }}</h4>
                            <p class="mb-0">Non vérifiés</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filtres et Recherche</h3>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.users.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Recherche</label>
                                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nom, email, téléphone...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Rôle</label>
                                    <select name="role" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="seller" {{ request('role') == 'seller' ? 'selected' : '' }}>Vendeur</option>
                                        <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Client</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Statut</label>
                                    <select name="status" class="form-control">
                                        <option value="">Tous</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date début</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Date fin</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Liste des Utilisateurs</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="fas fa-plus"></i> Nouvel Utilisateur
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'id', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}">
                                            ID
                                            @if(request('sort_by') == 'id')
                                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'nom', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}">
                                            Nom
                                            @if(request('sort_by') == 'nom')
                                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_at', 'sort_order' => request('sort_order') == 'asc' ? 'desc' : 'asc']) }}">
                                            Date d'inscription
                                            @if(request('sort_by') == 'created_at')
                                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users ?? [] as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="mr-2">
                                                @if($user->profile_pic_url)
                                                    <img src="{{ str_starts_with($user->profile_pic_url, 'http') ? $user->profile_pic_url : asset($user->profile_pic_url) }}" class="rounded-circle" width="40" height="40" alt="Avatar" style="object-fit: cover; border: 2px solid #ff6b35;">
                                                @else
                                                    <div class="text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%); font-weight: bold; font-size: 14px;">
                                                        {{ strtoupper(substr($user->nom ?? 'U', 0, 1)) }}{{ strtoupper(substr($user->prenoms ?? '', 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <strong>{{ $user->nom }} {{ $user->prenoms }}</strong>
                                                @if($user->store)
                                                    <br><small class="text-muted">{{ $user->store->name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->telephone ?? 'N/A' }}</td>
                                    <td>
                                        @if($user->is_admin)
                                            <span class="badge badge-danger">Admin</span>
                                        @elseif($user->is_seller)
                                            <span class="badge badge-warning">Vendeur</span>
                                        @else
                                            <span class="badge badge-success">Client</span>
                                        @endif
                                        @if($user->role)
                                            <br><small class="text-muted">{{ $user->role->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->is_verified)
                                            <span class="badge badge-success">Actif</span>
                                        @else
                                            <span class="badge badge-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-warning btn-sm" onclick="editUser({{ $user->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-{{ $user->is_verified ? 'secondary' : 'success' }} btn-sm" onclick="toggleUserStatus({{ $user->id }})">
                                                <i class="fas fa-{{ $user->is_verified ? 'ban' : 'check' }}"></i>
                                            </button>
                                            @if(!$user->is_admin)
                                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteUser({{ $user->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Aucun utilisateur trouvé</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            Affichage de {{ $users->firstItem() ?? 0 }} à {{ $users->lastItem() ?? 0 }} sur {{ $users->total() ?? 0 }} résultats
                        </div>
                        <div>
                            {{ $users->links() ?? '' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de création d'utilisateur -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Créer un nouvel utilisateur</h5>
                <button type="button" class="close" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="createUserForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom *</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prénoms *</label>
                                <input type="text" name="prenoms" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Téléphone</label>
                                <input type="text" name="telephone" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Mot de passe *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Confirmation *</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rôle Admin</label>
                                <select name="role_id" class="form-control">
                                    <option value="">Aucun rôle</option>
                                    @foreach(\App\Models\Role::active()->get() as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Sélectionnez un rôle pour l'accès au dashboard admin</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Statut</label>
                                <select name="is_verified" class="form-control">
                                    <option value="1">Actif</option>
                                    <option value="0">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Type d'utilisateur</label>
                                <select name="user_type" class="form-control" onchange="updateUserType(this.value)">
                                    <option value="customer">Client</option>
                                    <option value="seller">Vendeur</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Ville</label>
                                <input type="text" name="ville" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de modification d'utilisateur -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier l'utilisateur</h5>
                <button type="button" class="close" data-bs-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editUserForm">
                <input type="hidden" id="edit_user_id" name="user_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nom *</label>
                                <input type="text" id="edit_nom" name="nom" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Prénoms *</label>
                                <input type="text" id="edit_prenoms" name="prenoms" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" id="edit_email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Téléphone</label>
                                <input type="text" id="edit_telephone" name="telephone" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Ville</label>
                                <input type="text" id="edit_ville" name="ville" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Code postal</label>
                                <input type="text" id="edit_code_postal" name="code_postal" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Rôle Admin</label>
                                <select id="edit_role_id" name="role_id" class="form-control">
                                    <option value="">Aucun rôle</option>
                                    @foreach(\App\Models\Role::active()->get() as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Sélectionnez un rôle pour l'accès au dashboard admin</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Statut</label>
                                <select id="edit_status" name="status" class="form-control">
                                    <option value="1">Actif</option>
                                    <option value="0">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Type d'utilisateur</label>
                                <select id="edit_role" name="role" class="form-control">
                                    <option value="customer">Client</option>
                                    <option value="seller">Vendeur</option>
                                    <option value="admin">Administrateur</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Email vérifié</label>
                                <select id="edit_email_verified" name="email_verified" class="form-control">
                                    <option value="1">Oui</option>
                                    <option value="0">Non</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Adresse</label>
                                <textarea id="edit_adresse" name="adresse" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(userId) {
    // Récupérer les données de l'utilisateur (AJAX JSON)
    fetch(`/admin/users/${userId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.user;
                
                // Remplir le formulaire
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_nom').value = user.nom || '';
                document.getElementById('edit_prenoms').value = user.prenoms || '';
                document.getElementById('edit_email').value = user.email || '';
                document.getElementById('edit_telephone').value = user.telephone || '';
                document.getElementById('edit_ville').value = user.ville || '';
                document.getElementById('edit_code_postal').value = user.code_postal || '';
                document.getElementById('edit_adresse').value = user.adresse || '';
                
                // Définir le rôle
                if (user.is_admin) {
                    document.getElementById('edit_role').value = 'admin';
                } else if (user.is_seller) {
                    document.getElementById('edit_role').value = 'seller';
                } else {
                    document.getElementById('edit_role').value = 'customer';
                }
                
                // Définir le statut
                document.getElementById('edit_status').value = user.is_verified ? '1' : '0';
                
                // Définir l'email vérifié
                document.getElementById('edit_email_verified').value = user.email_verified_at ? '1' : '0';
                
                // Définir le rôle admin
                if (user.role_id) {
                    document.getElementById('edit_role_id').value = user.role_id;
                } else {
                    document.getElementById('edit_role_id').value = '';
                }
                
                // Ouvrir la modal
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
            } else {
                alert('Erreur lors du chargement des données utilisateur');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du chargement des données utilisateur');
        });
}

function toggleUserStatus(userId) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de cet utilisateur ?')) {
        fetch(`/admin/users/${userId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(async response => {
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(payload.message || 'Erreur serveur');
            }
            return payload;
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la mise à jour du statut');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la mise à jour du statut');
        });
    }
}

function deleteUser(userId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(async response => {
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(payload.message || 'Erreur serveur');
            }
            return payload;
        })
        .then(data => {
            if (data.success) {
                location.reload();
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

// Gestion du formulaire de modification
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const userId = formData.get('user_id');
    
    // Convertir les données en JSON
    const roleId = formData.get('role_id');
    
    const data = {
        nom: formData.get('nom'),
        prenoms: formData.get('prenoms'),
        email: formData.get('email'),
        telephone: formData.get('telephone'),
        ville: formData.get('ville'),
        code_postal: formData.get('code_postal'),
        adresse: formData.get('adresse'),
        role_id: roleId && roleId !== '' ? roleId : null,
        role: formData.get('role'),
        is_verified: formData.get('status') === '1',
        email_verified_at: formData.get('email_verified') === '1' ? new Date().toISOString() : null
    };
    
    // Définir les rôles
    data.is_admin = data.role === 'admin';
    data.is_seller = data.role === 'seller';
    
    fetch(`/admin/users/${userId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(payload.message || 'Validation/Serveur');
        }
        return payload;
    })
    .then(data => {
        if (data.success) {
            // Afficher une notification de succès
            showNotification('success', data.message || 'Utilisateur mis à jour avec succès.');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
            if (modal) modal.hide();
            
            // Recharger la page après un court délai pour voir la notification
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification('error', 'Erreur lors de la modification: ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Erreur lors de la modification');
    });
});

// Fonction pour afficher des notifications
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const notification = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas ${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Supprimer les anciennes notifications
    document.querySelectorAll('.alert').forEach(alert => {
        if (alert.style.position === 'fixed') {
            alert.remove();
        }
    });
    
    // Ajouter la nouvelle notification
    document.body.insertAdjacentHTML('beforeend', notification);
    
    // Supprimer automatiquement après 5 secondes
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert[style*="position: fixed"]');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
}

// Fonction pour mettre à jour le type d'utilisateur
function updateUserType(value) {
    const form = document.getElementById('createUserForm');
    if (value === 'admin') {
        form.querySelector('[name="is_admin"]').value = '1';
        form.querySelector('[name="is_seller"]').value = '0';
    } else if (value === 'seller') {
        form.querySelector('[name="is_admin"]').value = '0';
        form.querySelector('[name="is_seller"]').value = '1';
    } else {
        form.querySelector('[name="is_admin"]').value = '0';
        form.querySelector('[name="is_seller"]').value = '0';
    }
}

// Gestion du formulaire de création
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Convertir les données en JSON
    const data = {
        nom: formData.get('nom'),
        prenoms: formData.get('prenoms'),
        email: formData.get('email'),
        telephone: formData.get('telephone'),
        password: formData.get('password'),
        password_confirmation: formData.get('password_confirmation'),
        role_id: formData.get('role_id') || null,
        is_verified: formData.get('is_verified') === '1',
    };
    
    // Définir les rôles
    const userType = formData.get('user_type');
    data.is_admin = userType === 'admin';
    data.is_seller = userType === 'seller';
    
    fetch('/admin/users', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(payload.message || 'Erreur serveur');
        }
        return payload;
    })
    .then(data => {
        if (data.success) {
            // Afficher une notification de succès
            showNotification('success', 'Utilisateur créé avec succès.');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('createUserModal'));
            if (modal) modal.hide();
            
            // Recharger la page après un court délai
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification('error', 'Erreur lors de la création: ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Erreur lors de la création');
    });
});
</script>
@endsection
