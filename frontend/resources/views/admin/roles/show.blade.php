@extends('admin.layouts.app')

@section('title', 'Détails du Rôle')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Détails du Rôle: {{ $role->name }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item">
                <a href="{{ route('admin.roles.index') }}">Rôles</a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>{{ $role->name }}</span></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informations du Rôle</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Nom:</strong> {{ $role->name }}
                        </li>
                        <li class="list-group-item">
                            <strong>Slug:</strong> <code>{{ $role->slug }}</code>
                        </li>
                        <li class="list-group-item">
                            <strong>Description:</strong>
                            <p class="mb-0">{{ $role->description ?? 'Aucune description' }}</p>
                        </li>
                        <li class="list-group-item">
                            <strong>Statut:</strong>
                            @if($role->is_active)
                                <span class="badge badge-success">Actif</span>
                            @else
                                <span class="badge badge-danger">Inactif</span>
                            @endif
                        </li>
                        <li class="list-group-item">
                            <strong>Créé le:</strong> {{ $role->created_at->format('d/m/Y H:i') }}
                        </li>
                        <li class="list-group-item">
                            <strong>Mis à jour le:</strong> {{ $role->updated_at->format('d/m/Y H:i') }}
                        </li>
                    </ul>
                </div>
                <div class="card-footer">
                    <div class="btn-group w-100">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        @if($role->users->count() == 0)
                            <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Permissions ({{ $role->permissions->count() }})</h3>
                </div>
                <div class="card-body">
                    @if($role->permissions->count() > 0)
                        @foreach($role->permissions->groupBy('module') as $module => $permissions)
                            <div class="mb-3">
                                <h5 class="text-primary">
                                    <i class="fas fa-folder"></i> {{ ucfirst($module) }}
                                    <span class="badge badge-info">{{ $permissions->count() }}</span>
                                </h5>
                                <div class="row">
                                    @foreach($permissions as $permission)
                                        <div class="col-md-6 mb-2">
                                            <div class="alert alert-info mb-2 py-2">
                                                <i class="fas fa-check-circle text-success"></i>
                                                <strong>{{ $permission->name }}</strong>
                                                @if($permission->description)
                                                    <br>
                                                    <small class="text-muted">{{ $permission->description }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">Aucune permission associée à ce rôle.</p>
                    @endif
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Utilisateurs avec ce rôle ({{ $role->users->count() }})</h3>
                </div>
                <div class="card-body">
                    @if($role->users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom</th>
                                        <th>Email</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($role->users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->nom }} {{ $user->prenoms }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @if($user->is_verified)
                                                    <span class="badge badge-success">Actif</span>
                                                @else
                                                    <span class="badge badge-danger">Inactif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">Aucun utilisateur n'utilise ce rôle actuellement.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

