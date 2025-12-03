@extends('layouts.app')

@section('content')
<main class="container-fluid">
    <!-- SECTION BREADCRUMB -->
    <section class="bg-light py-2">
        <div class="container-fluid">
            <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accueil') }}" class="fs-7">Accueil</a></li>
                    <li class="breadcrumb-item active fs-7" aria-current="page">Expédition & Livraison</li>
                </ol>
            </nav>
        </div>
    </section>
    <!-- SECTION BREADCRUMB END -->

    <!-- SECTION EXPÉDITION & LIVRAISON -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h3 class="fw-bold mb-3">Expédition & Livraison</h3>
                    <p class="text-muted">Tout ce que vous devez savoir sur nos services de livraison</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Options de livraison -->
                <div class="col-lg-8">
                    <div class="bg-white p-4 rounded shadow-sm mb-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-truck blue-color me-2"></i>
                            Options de livraison
                        </h5>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-clock blue-color fs-2 me-3"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1">Livraison Standard</h6>
                                            <p class="text-muted mb-0">3-5 jours ouvrés</p>
                                        </div>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Livraison gratuite</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Suivi en temps réel</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Signature requise</li>
                                    </ul>
                                    <p class="fw-bold orange-color mb-0">GRATUIT</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-lightning orange-color fs-2 me-3"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1">Livraison Express</h6>
                                            <p class="text-muted mb-0">1-2 jours ouvrés</p>
                                        </div>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Livraison prioritaire</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Suivi en temps réel</li>
                                        <li><i class="bi bi-check-circle text-success me-2"></i>Livraison matinale</li>
                                    </ul>
                                    <p class="fw-bold orange-color mb-0">+5 000 FCFA</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Zones de livraison -->
                    <div class="bg-white p-4 rounded shadow-sm mb-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-geo-alt orange-color me-2"></i>
                            Zones de livraison
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="fw-bold blue-color">Abidjan</h6>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-dot"></i>Cocody</li>
                                    <li><i class="bi bi-dot"></i>Plateau</li>
                                    <li><i class="bi bi-dot"></i>Marcory</li>
                                    <li><i class="bi bi-dot"></i>Yopougon</li>
                                    <li><i class="bi bi-dot"></i>Adjamé</li>
                                    <li><i class="bi bi-dot"></i>Koumassi</li>
                                    <li><i class="bi bi-dot"></i>Port-Bouët</li>
                                    <li><i class="bi bi-dot"></i>Bingerville</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold blue-color">Autres villes</h6>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-dot"></i>Bouaké</li>
                                    <li><i class="bi bi-dot"></i>San-Pédro</li>
                                    <li><i class="bi bi-dot"></i>Korhogo</li>
                                    <li><i class="bi bi-dot"></i>Gagnoa</li>
                                    <li><i class="bi bi-dot"></i>Man</li>
                                    <li><i class="bi bi-dot"></i>Divo</li>
                                    <li><i class="bi bi-dot"></i>Anyama</li>
                                    <li><i class="bi bi-dot"></i>Songon</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Livraison en province :</strong> Délais supplémentaires de 2-3 jours ouvrés. Frais de livraison variables selon la destination.
                        </div>
                    </div>
                </div>

                <!-- Informations pratiques -->
                <div class="col-lg-4">
                    <div class="bg-light p-4 rounded h-100">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-info-circle orange-color me-2"></i>
                            Informations pratiques
                        </h5>
                        
                        <div class="mb-4">
                            <h6 class="fw-bold blue-color">Horaires de livraison</h6>
                            <p class="mb-2"><strong>Lundi - Vendredi :</strong><br>8h00 - 18h00</p>
                            <p class="mb-2"><strong>Samedi :</strong><br>9h00 - 16h00</p>
                            <p class="mb-0"><strong>Dimanche :</strong><br>Fermé</p>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold blue-color">Conditions de livraison</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check text-success me-2"></i>Présence requise</li>
                                <li><i class="bi bi-check text-success me-2"></i>Pièce d'identité</li>
                                <li><i class="bi bi-check text-success me-2"></i>Signature obligatoire</li>
                                <li><i class="bi bi-check text-success me-2"></i>Emballage d'origine</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold blue-color">En cas d'absence</h6>
                            <p class="small">Le livreur laissera un avis de passage. Vous pourrez reprogrammer la livraison ou récupérer votre colis au point relais le plus proche.</p>
                        </div>

                        <div class="text-center">
                            <a href="https://wa.me/2250701234567" class="btn btn-success btn-sm mb-2 w-100" target="_blank">
                                <i class="bi bi-whatsapp me-2"></i>Support WhatsApp
                            </a>
                            <a href="{{ route('contact') }}" class="btn orange-bg text-white btn-sm w-100">
                                <i class="bi bi-envelope me-2"></i>Nous contacter
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Livraison -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="bg-light p-4 rounded">
                        <h5 class="fw-bold mb-4 text-center">
                            <i class="bi bi-question-circle orange-color me-2"></i>
                            Questions fréquentes
                        </h5>
                        
                        <div class="accordion" id="deliveryFaq">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Comment calculer les frais de livraison ?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#deliveryFaq">
                                    <div class="accordion-body">
                                        Les frais de livraison sont calculés automatiquement selon votre adresse et la méthode choisie. La livraison standard est gratuite pour toutes les commandes.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Puis-je modifier mon adresse de livraison ?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#deliveryFaq">
                                    <div class="accordion-body">
                                        Vous pouvez modifier votre adresse de livraison avant l'expédition. Une fois expédiée, contactez notre support pour toute modification.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Que faire si mon colis est endommagé ?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#deliveryFaq">
                                    <div class="accordion-body">
                                        En cas de colis endommagé, refusez la livraison et contactez immédiatement notre service client. Nous organiserons un remplacement gratuit.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- SECTION EXPÉDITION & LIVRAISON END -->
</main>
@endsection
