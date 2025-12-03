@extends('admin.layouts.app')

@section('title', 'Modifier le Profil - KAZARIA Admin')

@section('content')
<div class="main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Modifier le Profil</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Tableau de bord</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.profile.index') }}">Mon Profil</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Modifier</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 col-sm-12 text-right">
                    <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Edit Form -->
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Informations personnelles</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="prenoms" class="form-label">Prénoms <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('prenoms') is-invalid @enderror" 
                                               id="prenoms" name="prenoms" value="{{ old('prenoms', $user->prenoms) }}" required>
                                        @error('prenoms')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('nom') is-invalid @enderror" 
                                               id="nom" name="nom" value="{{ old('nom', $user->nom) }}" required>
                                        @error('nom')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="telephone" class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control @error('telephone') is-invalid @enderror" 
                                               id="telephone" name="telephone" value="{{ old('telephone', $user->telephone) }}">
                                        @error('telephone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea class="form-control @error('adresse') is-invalid @enderror" 
                                          id="adresse" name="adresse" rows="3">{{ old('adresse', $user->adresse) }}</textarea>
                                @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="profile_pic" class="form-label">Photo de profil</label>
                                <input type="file" class="form-control @error('profile_pic') is-invalid @enderror" 
                                       id="profile_pic" name="profile_pic" accept="image/*">
                                <small class="form-text text-muted">Formats acceptés : JPEG, PNG, JPG, GIF. Taille max : 2MB</small>
                                @error('profile_pic')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Enregistrer les modifications
                                </button>
                                <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary ml-2">
                                    <i class="fa fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-12">
                <!-- Current Profile Picture -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Photo actuelle</h5>
                    </div>
                    <div class="card-body text-center">
                        @if($user->profile_pic_url)
                            <img src="{{ asset($user->profile_pic_url) }}" alt="Photo de profil actuelle" 
                                 class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                            <div>
                                <form action="{{ route('admin.profile.profile-pic.delete') }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette photo ?')">
                                        <i class="fa fa-trash"></i> Supprimer la photo
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-3" 
                                 style="width: 150px; height: 150px; border: 2px dashed #dee2e6;">
                                <i class="fa fa-user text-muted" style="font-size: 48px;"></i>
                            </div>
                            <p class="text-muted">Aucune photo de profil</p>
                        @endif
                    </div>
                </div>
                
                <!-- Help -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">Aide</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            <i class="fa fa-info-circle text-primary me-2"></i>
                            Vous pouvez modifier vos informations personnelles. Les champs marqués d'un astérisque (*) sont obligatoires.
                        </p>
                        <p class="text-muted small">
                            <i class="fa fa-camera text-primary me-2"></i>
                            Pour changer votre photo de profil, sélectionnez une nouvelle image et sauvegardez.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
