@extends('layouts.app')

@section('content')
<main class="container-fluid">
    <!-- SECTION BREADCRUMB -->
    <section class="bg-light py-2">
        <div class="container-fluid">
            <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accueil') }}" class="fs-7">Accueil</a></li>
                    <li class="breadcrumb-item active fs-7" aria-current="page">Agences & Points de relais KAZARIA</li>
                </ol>
            </nav>
        </div>
    </section>
    <!-- SECTION BREADCRUMB END -->

    <!-- SECTION AGENCES & POINTS DE RELAIS -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-4">
                    <h4 class="fw-bold mb-3">Agences & Points de relais KAZARIA</h4>
                    <p class="text-muted">Retrouvez nos agences et points de relais près de chez vous</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- Agences principales -->
                <div class="col-lg-8">
                    <div class="bg-white p-4 rounded shadow-sm mb-4">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-building orange-color me-2"></i>
                            Agences principales
                        </h5>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-geo-alt blue-color fs-4 me-3"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1">Agence Plateau</h6>
                                            <p class="text-muted mb-0 small">Centre-ville</p>
                                        </div>
                                    </div>
                                    <p class="small mb-2">
                                        <strong>Adresse :</strong><br>
                                        Avenue Franchet d'Esperey<br>
                                        Immeuble KAZARIA, 1er étage<br>
                                        Plateau, Abidjan
                                    </p>
                                    <p class="small mb-2">
                                        <strong>Téléphone :</strong><br>
                                        +225 20 30 40 50
                                    </p>
                                    <p class="small mb-2">
                                        <strong>Horaires :</strong><br>
                                        Lun-Ven : 8h00-18h00<br>
                                        Sam : 9h00-16h00
                                    </p>
                                    <div class="mt-3">
                                        <span class="badge bg-success">Ouvert</span>
                                        <span class="badge bg-primary ms-2">Retrait</span>
                                        <span class="badge bg-warning ms-2">Retour</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-geo-alt blue-color fs-4 me-3"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1">Agence Cocody</h6>
                                            <p class="text-muted mb-0 small">Angré 8ème Tranche</p>
                                        </div>
                                    </div>
                                    <p class="small mb-2">
                                        <strong>Adresse :</strong><br>
                                        Boulevard de la Paix<br>
                                        Centre Commercial Riviera 2<br>
                                        Cocody, Abidjan
                                    </p>
                                    <p class="small mb-2">
                                        <strong>Téléphone :</strong><br>
                                        +225 20 30 40 51
                                    </p>
                                    <p class="small mb-2">
                                        <strong>Horaires :</strong><br>
                                        Lun-Ven : 8h00-18h00<br>
                                        Sam : 9h00-16h00
                                    </p>
                                    <div class="mt-3">
                                        <span class="badge bg-success">Ouvert</span>
                                        <span class="badge bg-primary ms-2">Retrait</span>
                                        <span class="badge bg-warning ms-2">Retour</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-geo-alt blue-color fs-4 me-3"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1">Agence Yopougon</h6>
                                            <p class="text-muted mb-0 small">Sicogi</p>
                                        </div>
                                    </div>
                                    <p class="small mb-2">
                                        <strong>Adresse :</strong><br>
                                        Boulevard de la Paix<br>
                                        Face au marché Sicogi<br>
                                        Yopougon, Abidjan
                                    </p>
                                    <p class="small mb-2">
                                        <strong>Téléphone :</strong><br>
                                        +225 20 30 40 52
                                    </p>
                                    <p class="small mb-2">
                                        <strong>Horaires :</strong><br>
                                        Lun-Ven : 8h00-18h00<br>
                                        Sam : 9h00-16h00
                                    </p>
                                    <div class="mt-3">
                                        <span class="badge bg-success">Ouvert</span>
                                        <span class="badge bg-primary ms-2">Retrait</span>
                                        <span class="badge bg-warning ms-2">Retour</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex align-items-center mb-3">
                                        <i class="bi bi-geo-alt blue-color fs-4 me-3"></i>
                                        <div>
                                            <h6 class="fw-bold mb-1">Agence Marcory</h6>
                                            <p class="text-muted mb-0 small">Zone 4</p>
                                        </div>
                                    </div>
                                    <p class="small mb-2">
                                        <strong>Adresse :</strong><br>
                                        Route du Port<br>
                                        Centre Commercial Marcory<br>
                                        Marcory, Abidjan
                                    </p>
                                    <p class="small mb-2">
                                        <strong>Téléphone :</strong><br>
                                        +225 20 30 40 53
                                    </p>
                                    <p class="small mb-2">
                                        <strong>Horaires :</strong><br>
                                        Lun-Ven : 8h00-18h00<br>
                                        Sam : 9h00-16h00
                                    </p>
                                    <div class="mt-3">
                                        <span class="badge bg-success">Ouvert</span>
                                        <span class="badge bg-primary ms-2">Retrait</span>
                                        <span class="badge bg-warning ms-2">Retour</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Points de relais -->
                    <div class="bg-white p-4 rounded shadow-sm">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-shop blue-color me-2"></i>
                            Points de relais partenaires
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="fw-bold blue-color mb-2">Bingerville</h6>
                                    <p class="small mb-1">
                                        <strong>Pharmacie du Centre</strong><br>
                                        Avenue principale, Bingerville<br>
                                        <strong>Tel :</strong> +225 07 01 23 45
                                    </p>
                                    <span class="badge bg-info">Retrait uniquement</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="fw-bold blue-color mb-2">Anyama</h6>
                                    <p class="small mb-1">
                                        <strong>Librairie Moderne</strong><br>
                                        Carrefour Anyama<br>
                                        <strong>Tel :</strong> +225 07 01 23 46
                                    </p>
                                    <span class="badge bg-info">Retrait uniquement</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="fw-bold blue-color mb-2">Songon</h6>
                                    <p class="small mb-1">
                                        <strong>Boutique KAZARIA Express</strong><br>
                                        Marché de Songon<br>
                                        <strong>Tel :</strong> +225 07 01 23 47
                                    </p>
                                    <span class="badge bg-success">Retrait & Retour</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="fw-bold blue-color mb-2">Port-Bouët</h6>
                                    <p class="small mb-1">
                                        <strong>Station Total</strong><br>
                                        Boulevard de l'Aéroport<br>
                                        <strong>Tel :</strong> +225 07 01 23 48
                                    </p>
                                    <span class="badge bg-info">Retrait uniquement</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note :</strong> Les points de relais partenaires ont des horaires variables. Contactez-les avant de vous déplacer.
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
                            <h6 class="fw-bold blue-color">Services disponibles</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-check text-success me-2"></i>Retrait de commandes</li>
                                <li><i class="bi bi-check text-success me-2"></i>Retour de produits</li>
                                <li><i class="bi bi-check text-success me-2"></i>Conseil client</li>
                                <li><i class="bi bi-check text-success me-2"></i>Paiement en espèces</li>
                                <li><i class="bi bi-check text-success me-2"></i>Réparation (agences principales)</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold blue-color">Documents requis</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-file-text text-primary me-2"></i>Numéro de commande</li>
                                <li><i class="bi bi-person text-primary me-2"></i>Pièce d'identité</li>
                                <li><i class="bi bi-phone text-primary me-2"></i>Numéro de téléphone</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold blue-color">Délais de conservation</h6>
                            <ul class="list-unstyled">
                                <li><i class="bi bi-clock text-warning me-2"></i>Agences : 30 jours</li>
                                <li><i class="bi bi-clock text-warning me-2"></i>Points relais : 15 jours</li>
                            </ul>
                        </div>

                        <div class="text-center">
                            <a href="https://wa.me/2250701234567" class="btn btn-success btn-sm mb-2 w-100" target="_blank">
                                <i class="bi bi-whatsapp me-2"></i>Nous contacter
                            </a>
                            <a href="{{ route('contact') }}" class="btn orange-bg text-white btn-sm w-100">
                                <i class="bi bi-envelope me-2"></i>Formulaire contact
                            </a>
                        </div>
                        
                        <!-- Carte Google Maps -->
                        <div class="mt-4">
                            <h6 class="fw-bold orange-color mb-3 text-center">
                                <i class="bi bi-map me-2"></i>
                                Localisation de nos agences
                            </h6>
                            <div class="map-container rounded overflow-hidden" style="height: 500px; border: 2px solid #f04e27;">
                                <iframe 
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3973.724643734375!2d-4.008675685237123!3d5.316374996094937!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xfc1948c8b5c1d6b%3A0x8b9b7c8b9b7c8b9b!2sPlateau%2C%20Abidjan%2C%20C%C3%B4te%20d%27Ivoire!5e0!3m2!1sfr!2sfr!4v1640000000000!5m2!1sfr!2sfr"
                                    width="100%" 
                                    height="100%" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy" 
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                            <div class="mt-3 text-center">
                                <a href="https://maps.google.com/?q=Abidjan,Côte+d'Ivoire" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-up-right me-1"></i>Ouvrir dans Google Maps
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <!-- SECTION AGENCES & POINTS DE RELAIS END -->
</main>
@endsection
