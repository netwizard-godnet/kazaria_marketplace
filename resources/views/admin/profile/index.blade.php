@extends('admin.layouts.app')

@section('title', 'Mon Profil - KAZARIA Admin')

@section('content')
<div class="main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Mon Profil</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Tableau de bord</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Mon Profil</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 col-sm-12 text-right">
                    <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary">
                        <i class="fa fa-edit"></i> Modifier le profil
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="row">
            <div class="col-lg-4 col-md-12">
                <!-- Profile Card -->
                <div class="card">
                    <div class="card-body text-center">
                        <div class="profile-photo mb-3">
                            @if($user->profile_pic_url)
                                <img src="{{ asset($user->profile_pic_url) }}" alt="Photo de profil" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                    <span class="text-white" style="font-size: 48px; font-weight: bold;">
                                        {{ strtoupper(substr($user->prenoms, 0, 1) . substr($user->nom, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <h4 class="mb-1">{{ $user->prenoms }} {{ $user->nom }}</h4>
                        <p class="text-muted mb-3">{{ $user->email }}</p>
                        
                        @if($user->telephone)
                            <p class="mb-1">
                                <i class="fa fa-phone text-primary me-2"></i>
                                {{ $user->telephone }}
                            </p>
                        @endif
                        
                        @if($user->is_admin)
                            <span class="badge bg-success mb-3">Administrateur</span>
                        @endif
                        
                        <div class="mt-3">
                            <a href="{{ route('admin.profile.edit') }}" class="btn btn-primary btn-sm me-2">
                                <i class="fa fa-edit"></i> Modifier
                            </a>
                            <a href="{{ route('admin.profile.password') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-key"></i> Mot de passe
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">Statistiques rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <h4 class="text-primary">{{ \App\Models\Order::count() }}</h4>
                                <p class="text-muted mb-0">Commandes</p>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success">{{ \App\Models\Product::count() }}</h4>
                                <p class="text-muted mb-0">Produits</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-md-12">
                <!-- Profile Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Informations du profil</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Prénoms</label>
                                    <p class="form-control-plaintext">{{ $user->prenoms }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Nom</label>
                                    <p class="form-control-plaintext">{{ $user->nom }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <p class="form-control-plaintext">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Téléphone</label>
                                    <p class="form-control-plaintext">{{ $user->telephone ?? 'Non renseigné' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Adresse</label>
                            <p class="form-control-plaintext">{{ $user->adresse ?? 'Non renseignée' }}</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Membre depuis</label>
                                    <p class="form-control-plaintext">{{ $user->created_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Dernière connexion</label>
                                    <p class="form-control-plaintext">{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : 'Jamais' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Security -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">Sécurité du compte</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-shield-alt text-success me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Mot de passe</h6>
                                        <p class="text-muted mb-0">Dernière modification : {{ $user->updated_at->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <a href="{{ route('admin.profile.password') }}" class="btn btn-outline-primary">
                                    <i class="fa fa-key"></i> Changer le mot de passe
                                </a>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-envelope text-info me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Email vérifié</h6>
                                        <p class="text-muted mb-0">
                                            @if($user->email_verified_at)
                                                <span class="text-success">Oui ({{ $user->email_verified_at->format('d/m/Y') }})</span>
                                            @else
                                                <span class="text-warning">Non</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-user-shield text-primary me-3"></i>
                                    <div>
                                        <h6 class="mb-1">Rôle</h6>
                                        <p class="text-muted mb-0">
                                            @if($user->is_admin)
                                                <span class="badge bg-success">Administrateur</span>
                                            @else
                                                <span class="badge bg-secondary">Utilisateur</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
