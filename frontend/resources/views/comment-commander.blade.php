@extends('layouts.app')

@section('content')
<main class="container-fluid">
    <!-- SECTION BREADCRUMB -->
    <section class="bg-light py-2">
        <div class="container-fluid">
            <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accueil') }}" class="fs-7">Accueil</a></li>
                    <li class="breadcrumb-item active fs-7" aria-current="page">Comment commander ?</li>
                </ol>
            </nav>
        </div>
    </section>
    <!-- SECTION BREADCRUMB END -->

    <!-- SECTION COMMENT COMMANDER -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h3 class="fw-bold mb-3">Comment commander ?</h3>
                    <p class="text-muted">Guide complet pour passer vos commandes sur KAZARIA</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Étapes de commande -->
                    <div class="bg-white p-4 rounded shadow-sm mb-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-list-ol orange-color me-2"></i>
                            Étapes de commande
                        </h5>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-orange text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">1</span>
                                        </div>
                                        <h6 class="fw-bold mb-0">Parcourir & Sélectionner</h6>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>Naviguez dans nos catégories</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Utilisez les filtres de recherche</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Comparez les produits</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Lisez les avis clients</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-blue text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">2</span>
                                        </div>
                                        <h6 class="fw-bold mb-0">Ajouter au panier</h6>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>Choisissez la quantité</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Vérifiez les options</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Cliquez "Ajouter au panier"</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Continuez vos achats</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">3</span>
                                        </div>
                                        <h6 class="fw-bold mb-0">Finaliser la commande</h6>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>Accédez à votre panier</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Vérifiez vos articles</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Cliquez "Procéder au paiement"</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Remplissez vos informations</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">4</span>
                                        </div>
                                        <h6 class="fw-bold mb-0">Paiement & Confirmation</h6>
                                    </div>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>Choisissez le mode de paiement</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Confirmez votre commande</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Recevez l'email de confirmation</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Suivez votre commande</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Options de paiement -->
                    <div class="bg-white p-4 rounded shadow-sm mb-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-credit-card blue-color me-2"></i>
                            Modes de paiement
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <i class="bi bi-cash orange-color fs-3 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Paiement à la livraison</h6>
                                        <p class="text-muted mb-0 small">Payez en espèces ou par carte à la réception</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <i class="bi bi-phone blue-color fs-3 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Mobile Money</h6>
                                        <p class="text-muted mb-0 small">Orange Money, MTN, Moov Money</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <i class="bi bi-credit-card-2-front orange-color fs-3 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Carte bancaire</h6>
                                        <p class="text-muted mb-0 small">Visa, Mastercard sécurisées</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    <i class="bi bi-wallet2 blue-color fs-3 me-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-1">Wave</h6>
                                        <p class="text-muted mb-0 small">Paiement mobile sécurisé</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Conseils -->
                    <div class="bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-lightbulb orange-color me-2"></i>
                            Conseils pour bien commander
                        </h5>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold blue-color">Avant la commande</h6>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Vérifiez la disponibilité</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Lisez les descriptions détaillées</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Consultez les avis clients</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Vérifiez les garanties</li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="fw-bold blue-color">Pendant la commande</h6>
                                <ul class="list-unstyled">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Vérifiez votre adresse</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Choisissez le bon mode de paiement</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Sauvegardez votre numéro de commande</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Gardez l'email de confirmation</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Astuce :</strong> Créez un compte pour sauvegarder vos informations et suivre facilement vos commandes.
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
                            <h6 class="fw-bold blue-color">Conditions de commande</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check text-success me-2"></i>Pas de montant minimum</li>
                                <li><i class="bi bi-check text-success me-2"></i>Livraison gratuite</li>
                                <li><i class="bi bi-check text-success me-2"></i>Paiement sécurisé</li>
                                <li><i class="bi bi-check text-success me-2"></i>Garantie satisfaction</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold blue-color">Délais de traitement</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-clock text-primary me-2"></i>Confirmation : Immédiate</li>
                                <li><i class="bi bi-truck text-primary me-2"></i>Préparation : 24h</li>
                                <li><i class="bi bi-geo-alt text-primary me-2"></i>Livraison : 3-5 jours</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold blue-color">Support client</h6>
                            <p class="small">Notre équipe est disponible pour vous accompagner dans vos commandes.</p>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('product-cart') }}" class="btn orange-bg text-white btn-sm mb-2 w-100">
                                <i class="bi bi-cart me-2"></i>Voir mon panier
                            </a>
                            <a href="https://wa.me/2250701234567" class="btn btn-success btn-sm mb-2 w-100" target="_blank">
                                <i class="bi bi-whatsapp me-2"></i>Aide WhatsApp
                            </a>
                            <a href="{{ route('help-faq') }}" class="btn blue-bg text-white btn-sm w-100">
                                <i class="bi bi-question-circle me-2"></i>FAQ
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Commandes -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="bg-light p-4 rounded">
                        <h5 class="fw-bold mb-4 text-center">
                            <i class="bi bi-question-circle orange-color me-2"></i>
                            Questions fréquentes
                        </h5>
                        
                        <div class="accordion" id="orderFaq">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Puis-je modifier ma commande ?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#orderFaq">
                                    <div class="accordion-body">
                                        Vous pouvez modifier votre commande avant qu'elle soit expédiée. Contactez notre service client pour toute modification.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Comment annuler une commande ?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#orderFaq">
                                    <div class="accordion-body">
                                        Vous pouvez annuler votre commande dans les 24h suivant la confirmation. Contactez-nous rapidement pour l'annulation.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Est-ce que je dois créer un compte ?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#orderFaq">
                                    <div class="accordion-body">
                                        Non, vous pouvez commander en tant qu'invité. Cependant, créer un compte vous permet de suivre vos commandes et sauvegarder vos informations.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- SECTION COMMENT COMMANDER END -->
</main>
@endsection
