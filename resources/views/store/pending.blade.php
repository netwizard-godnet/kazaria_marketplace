@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/store.css') }}">
<div class="container my-5 store-pending">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5 text-center">
                    <!-- Icône d'attente -->
                    <div class="mb-4">
                        <i class="bi bi-clock-history orange-color" style="font-size: 5rem;"></i>
                    </div>

                    <!-- Titre -->
                    <h2 class="fw-bold blue-color mb-3">Demande en cours de traitement</h2>
                    
                    <!-- Message -->
                    <p class="text-muted fs-5 mb-4">
                        Votre demande de création de boutique a bien été reçue !
                    </p>

                    <!-- Détails de la boutique -->
                    <div class="alert alert-info text-start mb-4">
                        <h5 class="fw-bold mb-3">
                            <i class="bi bi-shop me-2"></i>{{ $store->name }}
                        </h5>
                        <p class="mb-2">
                            <strong>Email:</strong> {{ $store->email }}
                        </p>
                        <p class="mb-2">
                            <strong>Téléphone:</strong> {{ $store->phone }}
                        </p>
                        <p class="mb-2">
                            <strong>Catégorie:</strong> {{ $store->category->name }}
                        </p>
                        <p class="mb-0">
                            <strong>Statut:</strong> 
                            @if($store->status === 'pending')
                                <span class="badge bg-warning">En attente de validation</span>
                            @elseif($store->status === 'rejected')
                                <span class="badge bg-danger">Rejetée</span>
                            @endif
                        </p>
                    </div>

                    @if($store->status === 'pending')
                        <!-- Timeline -->
                        <div class="my-5">
                            <h5 class="fw-bold mb-4">Prochaines étapes</h5>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success">
                                        <i class="bi bi-check text-white"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold">Demande soumise</h6>
                                        <p class="text-muted small">{{ $store->created_at->format('d/m/Y à H:i') }}</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning">
                                        <i class="bi bi-hourglass-split text-white"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold">Vérification des documents</h6>
                                        <p class="text-muted small">En cours... (24-48h)</p>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-secondary">
                                        <i class="bi bi-star text-white"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="fw-bold">Activation de votre boutique</h6>
                                        <p class="text-muted small">Bientôt...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informations -->
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            Notre équipe examine actuellement vos documents. Vous recevrez un email dès que votre boutique sera activée.
                        </div>
                    @endif

                    @if($store->status === 'rejected')
                        <div class="alert alert-danger">
                            <h5 class="fw-bold mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>Demande rejetée
                            </h5>
                            <p>Malheureusement, votre demande n'a pas pu être approuvée. Veuillez nous contacter pour plus d'informations.</p>
                        </div>
                    @endif

                    <!-- Boutons -->
                    <div class="mt-4">
                        <a href="{{ route('accueil') }}" class="btn btn-outline-primary me-2">
                            <i class="bi bi-house me-2"></i>Retour à l'accueil
                        </a>
                        <a href="{{ route('profil') }}?token={{ request()->token }}" class="btn orange-bg text-white">
                            <i class="bi bi-person me-2"></i>Mon profil
                        </a>
                    </div>

                    <!-- Contact -->
                    <div class="mt-5 pt-4 border-top">
                        <p class="text-muted mb-2">
                            <strong>Besoin d'aide ?</strong>
                        </p>
                        <p class="text-muted">
                            <i class="bi bi-envelope me-2"></i>
                            <a href="mailto:vendeurs@kazaria.com" class="orange-color">vendeurs@kazaria.com</a>
                        </p>
                        <p class="text-muted">
                            <i class="bi bi-telephone me-2"></i>
                            <a href="tel:+225XXXXXXXXXX" class="orange-color">+225 XX XX XX XX XX</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

