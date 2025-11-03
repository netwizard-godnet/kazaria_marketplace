@extends('layouts.app')

@section('content')
    <main class="container py-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Qui nous sommes ?</li>
            </ol>
        </nav>

        <section class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h3 mb-3">Qui nous sommes ?</h1>
                        <p class="text-muted">KAZARIA est une marketplace ivoirienne dédiée à offrir la meilleure expérience d’achat en ligne: vaste catalogue, prix transparents et service client réactif.</p>

                        <h2 class="h5 mt-4">Notre mission</h2>
                        <p>Mettre en relation acheteurs et vendeurs de manière fiable, rapide et sécurisée, partout en Côte d’Ivoire.</p>

                        <h2 class="h5 mt-4">Nos engagements</h2>
                        <ul class="mb-3">
                            <li>Produits vérifiés et vendeurs accompagnés</li>
                            <li>Paiement sécurisé et protection des achats</li>
                            <li>Livraison rapide avec suivi</li>
                            <li>Support client 7j/7</li>
                        </ul>

                        <h2 class="h5 mt-4">Chiffres clés</h2>
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <div class="h4 mb-0">5000+</div>
                                    <small class="text-muted">Produits</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <div class="h4 mb-0">100+</div>
                                    <small class="text-muted">Boutiques</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <div class="h4 mb-0">24/7</div>
                                    <small class="text-muted">Assistance</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded text-center">
                                    <div class="h4 mb-0">48h</div>
                                    <small class="text-muted">Livraison type</small>
                                </div>
                            </div>
                        </div>

                        <h2 class="h5 mt-4">Sécurité & Paiements</h2>
                        <p>Nous utilisons des partenaires de paiement reconnus (carte bancaire, mobile money) et la navigation chiffrée (HTTPS). Les données sensibles ne sont jamais stockées en clair.</p>

                        <h2 class="h5 mt-4">Livraison</h2>
                        <ul>
                            <li>Réseau de partenaires logistiques sur Abidjan et l’intérieur du pays</li>
                            <li>Suivi de colis et notification par email/SMS</li>
                            <li>Retours facilités selon la <a href="{{ route('politique-retour') }}">politique de retour</a></li>
                        </ul>

                        <h2 class="h5 mt-4">Nous contacter</h2>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-1"><i class="bi bi-envelope me-2"></i>{{ $settings['contact_email'] ?? 'contact@kazaria.ci' }}</li>
                            <li class="mb-1"><i class="bi bi-telephone me-2"></i>{{ $settings['contact_phone'] ?? '+225 07 00 00 00 00' }}</li>
                            <li class="mb-1"><i class="bi bi-geo-alt me-2"></i>{{ $settings['contact_address'] ?? "Abidjan, Côte d'Ivoire" }}</li>
                        </ul>

                        <div class="mt-4">
                            <a href="{{ route('contact') }}" class="btn orange-bg text-white me-2">
                                <i class="bi bi-chat-dots me-1"></i>Contactez‑nous
                            </a>
                            <a href="{{ route('help-faq') }}" class="btn btn-outline-secondary">
                                Aide & FAQ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h6 mb-3">Pourquoi KAZARIA ?</h2>
                        <ul class="mb-0">
                            <li>Catalogue sélectionné</li>
                            <li>Offres et Deals du jour</li>
                            <li>Retours facilités</li>
                        </ul>
                        <hr>
                        <h2 class="h6 mb-3">Rejoignez nos vendeurs</h2>
                        <p class="mb-2">Développez votre activité avec notre marketplace.</p>
                        <a href="#" onclick="goToSell(event)" class="btn btn-sm btn-outline-primary">Devenir vendeur</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection


