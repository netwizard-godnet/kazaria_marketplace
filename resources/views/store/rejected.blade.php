@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <!-- Icône d'erreur -->
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    
                    <!-- Titre -->
                    <h2 class="text-danger mb-3">Boutique Rejetée</h2>
                    
                    <!-- Message principal -->
                    <p class="lead text-muted mb-4">
                        Votre demande de création de boutique <strong>"{{ $store->name }}"</strong> a été rejetée.
                    </p>
                    
                    <!-- Informations de rejet -->
                    <div class="alert alert-danger text-start mb-4">
                        <h5 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>Détails du rejet
                        </h5>
                        <hr>
                        <p class="mb-2">
                            <strong>Date de rejet :</strong> {{ $store->rejected_at->format('d/m/Y à H:i') }}
                        </p>
                        @if($store->rejection_reason)
                            <p class="mb-0">
                                <strong>Raison :</strong> {{ $store->rejection_reason }}
                            </p>
                        @endif
                    </div>
                    
                    <!-- Actions possibles -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('store.create') }}" class="btn btn-primary btn-lg me-md-2">
                            <i class="fas fa-plus me-2"></i>Créer une nouvelle boutique
                        </a>
                        <a href="{{ route('contact') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-envelope me-2"></i>Nous contacter
                        </a>
                    </div>
                    
                    <!-- Conseils -->
                    <div class="mt-5">
                        <h5 class="text-muted mb-3">Conseils pour votre prochaine demande :</h5>
                        <div class="row text-start">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Vérifiez que tous les documents sont lisibles
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Assurez-vous que les informations sont correctes
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Rédigez une description détaillée
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Choisissez une catégorie appropriée
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
