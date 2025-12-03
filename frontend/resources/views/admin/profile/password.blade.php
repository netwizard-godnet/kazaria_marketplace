@extends('admin.layouts.app')

@section('title', 'Changer le Mot de Passe - KAZARIA Admin')

@section('content')
<div class="main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <div class="title">
                        <h4>Changer le Mot de Passe</h4>
                    </div>
                    <nav aria-label="breadcrumb" role="navigation">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Tableau de bord</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.profile.index') }}">Mon Profil</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Mot de passe</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-6 col-sm-12 text-right">
                    <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Retour au profil
                    </a>
                </div>
            </div>
        </div>

        <!-- Password Change Form -->
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Changer le mot de passe</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.profile.password.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="form-group">
                                <label for="current_password" class="form-label">Mot de passe actuel <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" name="current_password" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Le mot de passe doit contenir au moins 8 caractères.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" required>
                            </div>
                            
                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-key"></i> Changer le mot de passe
                                </button>
                                <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary ml-2">
                                    <i class="fa fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Security Tips -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">Conseils de sécurité</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fa fa-check text-success me-2"></i>
                                Utilisez au moins 8 caractères
                            </li>
                            <li class="mb-2">
                                <i class="fa fa-check text-success me-2"></i>
                                Mélangez lettres majuscules et minuscules
                            </li>
                            <li class="mb-2">
                                <i class="fa fa-check text-success me-2"></i>
                                Incluez des chiffres et des symboles
                            </li>
                            <li class="mb-2">
                                <i class="fa fa-check text-success me-2"></i>
                                Évitez les mots de passe courants
                            </li>
                            <li class="mb-0">
                                <i class="fa fa-check text-success me-2"></i>
                                Ne partagez jamais votre mot de passe
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Afficher/masquer les mots de passe
document.addEventListener('DOMContentLoaded', function() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        const toggleButton = document.createElement('button');
        toggleButton.type = 'button';
        toggleButton.className = 'btn btn-outline-secondary btn-sm position-absolute';
        toggleButton.style.right = '10px';
        toggleButton.style.top = '50%';
        toggleButton.style.transform = 'translateY(-50%)';
        toggleButton.innerHTML = '<i class="fa fa-eye"></i>';
        
        const wrapper = document.createElement('div');
        wrapper.className = 'position-relative';
        wrapper.style.width = '100%';
        
        field.parentNode.insertBefore(wrapper, field);
        wrapper.appendChild(field);
        wrapper.appendChild(toggleButton);
        
        toggleButton.addEventListener('click', function() {
            if (field.type === 'password') {
                field.type = 'text';
                this.innerHTML = '<i class="fa fa-eye-slash"></i>';
            } else {
                field.type = 'password';
                this.innerHTML = '<i class="fa fa-eye"></i>';
            }
        });
    });
});
</script>
@endsection
