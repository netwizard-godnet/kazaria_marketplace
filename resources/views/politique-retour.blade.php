@extends('layouts.app')

@section('content')
<main class="container-fluid">
    <!-- SECTION BREADCRUMB -->
    <section class="bg-light py-2">
        <div class="container-fluid">
            <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accueil') }}" class="fs-7">Accueil</a></li>
                    <li class="breadcrumb-item active fs-7" aria-current="page">Politique de retour</li>
                </ol>
            </nav>
        </div>
    </section>
    <!-- SECTION BREADCRUMB END -->

    <!-- SECTION POLITIQUE DE RETOUR -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h3 class="fw-bold mb-3">Politique de retour</h3>
                    <p class="text-muted">Notre engagement pour votre satisfaction</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Conditions de retour -->
                    <div class="bg-white p-4 rounded shadow-sm mb-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-arrow-return-left orange-color me-2"></i>
                            Conditions de retour
                        </h5>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-bold blue-color mb-3">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Produits éligibles
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-check text-success me-2"></i>Produit non utilisé</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Emballage d'origine intact</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Accessoires inclus</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Facture présente</li>
                                        <li><i class="bi bi-check text-success me-2"></i>Délai respecté (14 jours)</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <h6 class="fw-bold orange-color mb-3">
                                        <i class="bi bi-x-circle text-danger me-2"></i>
                                        Produits non éligibles
                                    </h6>
                                    <ul class="list-unstyled">
                                        <li><i class="bi bi-x text-danger me-2"></i>Produits personnalisés</li>
                                        <li><i class="bi bi-x text-danger me-2"></i>Produits alimentaires</li>
                                        <li><i class="bi bi-x text-danger me-2"></i>Produits d'hygiène</li>
                                        <li><i class="bi bi-x text-danger me-2"></i>Produits endommagés par l'usage</li>
                                        <li><i class="bi bi-x text-danger me-2"></i>Délai dépassé</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Processus de retour -->
                    <div class="bg-white p-4 rounded shadow-sm mb-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-list-ol blue-color me-2"></i>
                            Comment retourner un produit ?
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="text-center p-3">
                                    <div class="bg-orange text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px;">
                                        <span class="fw-bold">1</span>
                                    </div>
                                    <h6 class="fw-bold">Contactez-nous</h6>
                                    <p class="small text-muted">Via WhatsApp ou formulaire de contact</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="text-center p-3">
                                    <div class="bg-blue text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px;">
                                        <span class="fw-bold">2</span>
                                    </div>
                                    <h6 class="fw-bold">Obtenez le RMA</h6>
                                    <p class="small text-muted">Numéro d'autorisation de retour</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="text-center p-3">
                                    <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px;">
                                        <span class="fw-bold">3</span>
                                    </div>
                                    <h6 class="fw-bold">Emballage</h6>
                                    <p class="small text-muted">Remettez dans l'emballage d'origine</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="text-center p-3">
                                    <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 50px; height: 50px;">
                                        <span class="fw-bold">4</span>
                                    </div>
                                    <h6 class="fw-bold">Expédition</h6>
                                    <p class="small text-muted">Renvoyez ou déposez en magasin</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Délais et remboursements -->
                    <div class="bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-clock orange-color me-2"></i>
                            Délais et remboursements
                        </h5>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <h6 class="fw-bold blue-color">Délai de retour</h6>
                                    <p class="mb-2"><strong>14 jours</strong> à partir de la réception</p>
                                    <p class="small text-muted">Dimanches et jours fériés non comptés</p>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-3 border rounded">
                                    <h6 class="fw-bold blue-color">Délai de remboursement</h6>
                                    <p class="mb-2"><strong>5-7 jours</strong> après réception</p>
                                    <p class="small text-muted">Selon votre mode de paiement</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Important :</strong> Les frais de retour sont à la charge du client, sauf en cas de défaut du produit ou d'erreur de notre part.
                        </div>
                    </div>
                </div>

                <!-- Informations pratiques -->
                <div class="col-lg-4">
                    <div class="bg-light p-4 rounded h-100">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-tools orange-color me-2"></i>
                            Informations pratiques
                        </h5>
                        
                        <div class="mb-4">
                            <h6 class="fw-bold blue-color">Modes de retour</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-truck text-success me-2"></i>Expédition à vos frais</li>
                                <li><i class="bi bi-shop text-success me-2"></i>Dépôt en magasin partenaire</li>
                                <li><i class="bi bi-person text-success me-2"></i>Ramassage à domicile (+5 000 FCFA)</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold blue-color">Documents requis</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-file-text text-primary me-2"></i>Numéro de commande</li>
                                <li><i class="bi bi-receipt text-primary me-2"></i>Facture d'achat</li>
                                <li><i class="bi bi-card-text text-primary me-2"></i>Numéro RMA</li>
                                <li><i class="bi bi-envelope text-primary me-2"></i>Motif du retour</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold blue-color">Modes de remboursement</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-credit-card text-success me-2"></i>Carte bancaire</li>
                                <li><i class="bi bi-phone text-success me-2"></i>Mobile Money</li>
                                <li><i class="bi bi-cash text-success me-2"></i>Espèces (en magasin)</li>
                            </ul>
                        </div>

                        <div class="text-center">
                            <a href="https://wa.me/2250701234567" class="btn btn-success btn-sm mb-2 w-100" target="_blank">
                                <i class="bi bi-whatsapp me-2"></i>Demander un retour
                            </a>
                            <a href="{{ route('contact') }}" class="btn orange-bg text-white btn-sm w-100">
                                <i class="bi bi-envelope me-2"></i>Formulaire contact
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Retours -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="bg-light p-4 rounded">
                        <h5 class="fw-bold mb-4 text-center">
                            <i class="bi bi-question-circle orange-color me-2"></i>
                            Questions fréquentes
                        </h5>
                        
                        <div class="accordion" id="returnFaq">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        Puis-je échanger un produit ?
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#returnFaq">
                                    <div class="accordion-body">
                                        Oui, vous pouvez échanger un produit contre un autre de valeur équivalente ou supérieure. La différence de prix sera facturée.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        Que faire si le produit est défectueux ?
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#returnFaq">
                                    <div class="accordion-body">
                                        En cas de défaut, nous couvrons tous les frais de retour et vous proposons un remplacement gratuit ou un remboursement intégral.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        Puis-je retourner un produit en promotion ?
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#returnFaq">
                                    <div class="accordion-body">
                                        Oui, les produits en promotion suivent la même politique de retour que les autres produits, sous réserve des conditions générales.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- SECTION POLITIQUE DE RETOUR END -->
</main>
@endsection
