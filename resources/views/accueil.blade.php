@extends('layouts.app')

@section('content')
    <main class="container-fluid">
        <div class="row g-2 py-2 d-flex align-items-center justify-content-center">
            <div class="col-md-8">
                <div class="">
                    <div id="carouselExampleAutoplaying" class="carousel slide h-400px" data-bs-ride="carousel">
                        <div class="carousel-inner h-400px">
                            <div class="carousel-item active" data-bs-interval="2000">
                                <img src="{{ asset('images/bg-1.jpg') }}" class="d-block w-100 h-400px" alt="...">
                            </div>
                            <div class="carousel-item" data-bs-interval="2000">
                                <img src="{{ asset('images/bg-1.jpg') }}" class="d-block w-100 h-400px" alt="...">
                            </div>
                            <div class="carousel-item" data-bs-interval="2000">
                                <img src="{{ asset('images/bg-1.jpg') }}" class="d-block w-100 h-400px" alt="...">
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="row gy-2">
                    <div class="col-md-12">
                        <div style="background: url('{{ asset('images/bg-2.jpg') }}'); background-size: cover; background-repeat: no-repeat;height: 200px;"></div>
                    </div>
                    <div class="col-md-12">
                        <div style="background: url('{{ asset('images/bg-2.jpg') }}'); background-size: cover; background-repeat: no-repeat;height: 200px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION -->
        <section class="border border-light py-3">
            <div class="container">
                <div class="row g-3">
                    <div class="col-md-3 border-end">
                        <div class="d-flex align-items-center justify-content-start">
                            <i class="fa-solid fa-rocket fa-3x me-3 orange-color"></i>
                            <div class="vstack gap-2">
                                <p class="fs-7 fw-bold mb-0">Livraison Gratuite</p>
                                <span class="fs-8 text-secondary">Livraison gratuite pour tout achat de 100.000F ou plus</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 border-end">
                        <div class="d-flex align-items-center justify-content-start">
                            <i class="fa-solid fa-shield-halved fa-3x me-3 orange-color"></i>
                            <div class="vstack gap-2">
                                <p class="fs-7 fw-bold mb-0">90 Jours Garantie</p>
                                <span class="fs-8 text-secondary">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 border-end">
                        <div class="d-flex align-items-center justify-content-start">
                            <i class="fa-solid fa-credit-card fa-3x me-3 orange-color"></i>
                            <div class="vstack gap-2">
                                <p class="fs-7 fw-bold mb-0">Paiement sécurisé</p>
                                <span class="fs-8 text-secondary">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center justify-content-start">
                            <i class="fa-solid fa-comment-dots fa-3x me-3 orange-color"></i>
                            <div class="vstack gap-2">
                                <p class="fs-7 fw-bold mb-0">Service Client 24/7</p>
                                <span class="fs-8 text-secondary">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- SECTION END -->

        <!-- SECTION DEALS JOUR -->
        <section class="multi-carousel py-5" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Deals du jour</h5>
                <span class="orange-bg px-3 fs-7 text-white">Fin dans 00:00:00</span>
            </div>
            <div class="multi-carousel-track d-flex">
                @foreach ($dealsProducts as $product)
                <div class="multi-carousel-item px-2">
                    @include('components.product-card', ['product' => $product])
                </div>
                @endforeach
            </div>
            <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="multi-carousel-dots text-center mt-2"></div>
        </section>
        <!-- SECTION DEALS JOUR END -->

        <!-- SECTION AFFICHES -->
        <section class="py-5">
            <div class="row g-3">
                <div class="col-md-4">
                    <img src="{{ asset('images/bg-2.jpg') }}" class="w-100 h-200px object-fit-cover" alt="">
                </div>
                <div class="col-md-4">
                    <img src="{{ asset('images/bg-2.jpg') }}" class="w-100 h-200px object-fit-cover" alt="">
                </div>
                <div class="col-md-4">
                    <img src="{{ asset('images/bg-2.jpg') }}" class="w-100 h-200px object-fit-cover" alt="">
                </div>
            </div>
        </section>
        <!-- SECTION AFFICHES END -->

        <!-- SECTION TOP CATEGORIES -->
        <section class="py-5">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Top Catégories du Mois</h5>
            </div>
            <div class="row g-3">
                @foreach ($categories as $category)
                <div class="col-6 col-sm-4 col-md-3">
                    <a class="px-1 card text-decoration-none hover-shadow" href="{{ route('categorie', $category->slug) }}">
                        <div class="position-relative text-center py-5">
                            @if($category->icon)
                            <i class="{{ $category->icon }} fa-5x orange-color mb-3"></i>
                            @else
                            <img src="{{ str_starts_with($category->image, 'http') ? $category->image : asset($category->image) }}" class="w-100 h-200px object-fit-contain" alt="{{ $category->name }}">
                            @endif
                        </div>
                        <div class="py-3 border-top">
                            <p class="fs-6 my-0 orange-color text-center fw-bold">{{ $category->name }}</p>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </section>
        <!-- SECTION TOP CATEGORIES END -->

        <!-- SECTION TELEPHONES & TABLETTES -->
        <section class="multi-carousel py-5" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Téléphones et tablettes</h5>
            </div>
            <div class="multi-carousel-track d-flex">
                @foreach ($phoneProducts as $product)
                <div class="multi-carousel-item px-2">
                    @include('components.product-card', ['product' => $product])
                </div>
                @endforeach
            </div>
            <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="multi-carousel-dots text-center mt-2"></div>
        </section>
        <!-- SECTION TELEPHONES & TABLETTES END -->

        <!-- SECTION TV et Electronique -->
        <section class="multi-carousel py-5" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">TV et Electronique</h5>
            </div>
            <div class="multi-carousel-track d-flex">
                @foreach ($tvProducts as $product)
                <div class="multi-carousel-item px-2">
                    @include('components.product-card', ['product' => $product])
                </div>
                @endforeach
            </div>
            <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="multi-carousel-dots text-center mt-2"></div>
        </section>
        <!-- SECTION TV et Electronique END -->

        <!-- SECTION Electroménager -->
        <section class="multi-carousel py-5" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Electroménager</h5>
            </div>
            <div class="multi-carousel-track d-flex">
                @foreach ($electroProducts as $product)
                <div class="multi-carousel-item px-2">
                    @include('components.product-card', ['product' => $product])
                </div>
                @endforeach
            </div>
            <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="multi-carousel-dots text-center mt-2"></div>
        </section>
        <!-- SECTION Electroménager END -->

        <!-- SECTION Ordinateurs et accessoires -->
        <section class="multi-carousel py-5" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Ordinateurs et accessoires</h5>
            </div>
            <div class="multi-carousel-track d-flex">
                @foreach ($computerProducts as $product)
                <div class="multi-carousel-item px-2">
                    @include('components.product-card', ['product' => $product])
                </div>
                @endforeach
            </div>
            <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="multi-carousel-dots text-center mt-2"></div>
        </section>
        <!-- SECTION Ordinateurs et accessoires END -->

        <!-- SECTION AFFICHES -->
        <section class="py-5">
            <div class="row g-3">
                <div class="col-md-8">
                    <img src="{{ asset('images/bg-1.jpg') }}" class="w-100 h-300px object-fit-cover" alt="">
                </div>
                <div class="col-md-4">
                    <img src="{{ asset('images/bg-2.jpg') }}" class="w-100 h-300px object-fit-cover" alt="">
                </div>
            </div>
        </section>
        <!-- SECTION AFFICHES END -->

        <!-- SECTION Download our app -->
        <section class="container pt-5">
            <div class="row g-3">
                <div class="col-md-6 d-flex align-items-end justify-content-center">
                    <img src="{{ asset('images/mockup.png') }}" class="w-100 h-400px object-fit-contain object-position-" alt="">
                </div>
                <div class="col-md-6 d-flex align-items-center justify-content-center">
                    <div class="">
                        <h3 class="fw-light mb-3">Télécharger l'application maintenant !</h3>
                        <p class="mb-3">Lorem ipsum dolor sit amet consectetur adipisicing elit. Neque, doloribus in nemo illo officia laudantium incidunt soluta iusto!</p>
                        <div class="hstack gap-2">
                            <img src="{{ asset('images/google-play.png') }}" alt="">
                            <img src="{{ asset('images/app-store.png') }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- SECTION Download our app end -->

        <!-- SECTION Tendance -->
        <section class="multi-carousel py-5" data-multi-carousel data-slides-to-show="6" data-slides-lg="4" data-slides-md="3" data-slides-sm="2" data-slides-xs="2" data-gap="0" data-autoplay="true" data-autoplay-speed="2000" data-pause-on-hover="true">
            <div class="bg-light d-flex align-items-center justify-content-between mb-4 border-bottom p-2">
                <h5 class="mb-0">Tendance</h5>
            </div>
            <div class="multi-carousel-track d-flex">
                @foreach ($trendingProducts as $product)
                <div class="multi-carousel-item px-2">
                    @include('components.product-card', ['product' => $product])
                </div>
                @endforeach
            </div>
            <button class="multi-carousel-prev btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="multi-carousel-next btn btn-sm btn-light orange-color"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="multi-carousel-dots text-center mt-2"></div>
        </section>
        <!-- SECTION Tendance END -->

        <!-- SECTION Politique de confidentialité -->
        <section class="container-fluid py-5 bg-light">
            <div class="container">
                <!-- En-tête -->
                <div class="row mb-5">
                    <div class="col-12 text-center">
                        <h4 class="fw-bold mb-3">
                            <i class="bi bi-shield-check orange-color me-2"></i>
                            Vos Données en Sécurité
                        </h4>
                        <p class="lead text-muted">
                            Chez <strong class="orange-color">KAZARIA</strong>, la protection de vos données personnelles est notre priorité absolue
                        </p>
                    </div>
                </div>

                <!-- Contenu principal -->
                <div class="row mb-5">
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title orange-color mb-3">
                                    <i class="bi bi-info-circle me-2"></i>Notre Engagement
                                </h5>
                                <p class="mb-3">
                                    Nous nous engageons à respecter votre vie privée et à sécuriser toutes vos informations. 
                                    Notre marketplace utilise les dernières technologies de cryptage pour protéger vos données 
                                    personnelles et financières.
                                </p>
                                <p class="mb-0">
                                    Chaque transaction est sécurisée, chaque donnée est protégée, et vous gardez toujours 
                                    le contrôle total sur vos informations personnelles.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title orange-color mb-3">
                                    <i class="bi bi-shield-lock me-2"></i>Vos Droits
                                </h5>
                                <p class="mb-3">
                                    Conformément au RGPD et aux lois en vigueur, vous disposez d'un droit d'accès, de 
                                    rectification, de suppression et de portabilité de vos données personnelles.
                                </p>
                                <p class="mb-0">
                                    Vous pouvez à tout moment consulter, modifier ou supprimer vos informations depuis 
                                    votre espace personnel ou en nous contactant directement.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Points clés -->
                <div class="row mb-5">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center p-3 bg-white rounded shadow-sm h-100">
                            <i class="bi bi-lock-fill orange-color" style="font-size: 2.5rem;"></i>
                            <h6 class="mt-3 mb-2">Cryptage SSL/TLS</h6>
                            <small class="text-muted">Toutes vos données sont cryptées de bout en bout</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center p-3 bg-white rounded shadow-sm h-100">
                            <i class="bi bi-shield-fill-check orange-color" style="font-size: 2.5rem;"></i>
                            <h6 class="mt-3 mb-2">Paiement Sécurisé</h6>
                            <small class="text-muted">Transactions protégées par nos partenaires certifiés</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center p-3 bg-white rounded shadow-sm h-100">
                            <i class="bi bi-eye-slash-fill orange-color" style="font-size: 2.5rem;"></i>
                            <h6 class="mt-3 mb-2">Confidentialité</h6>
                            <small class="text-muted">Vos informations ne sont jamais vendues à des tiers</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="text-center p-3 bg-white rounded shadow-sm h-100">
                            <i class="bi bi-check-circle-fill orange-color" style="font-size: 2.5rem;"></i>
                            <h6 class="mt-3 mb-2">Conformité RGPD</h6>
                            <small class="text-muted">Respect total des réglementations internationales</small>
                        </div>
                    </div>
                </div>

                <!-- Que collectons-nous -->
                <div class="row mb-5">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title orange-color mb-4">
                                    <i class="bi bi-database me-2"></i>Que collectons-nous ?
                                </h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <h6 class="mb-2"><i class="bi bi-person-badge me-2 text-muted"></i>Identité</h6>
                                        <ul class="list-unstyled small text-muted mb-0">
                                            <li>• Nom et prénom</li>
                                            <li>• Adresse email</li>
                                            <li>• Numéro de téléphone</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <h6 class="mb-2"><i class="bi bi-credit-card me-2 text-muted"></i>Paiement</h6>
                                        <ul class="list-unstyled small text-muted mb-0">
                                            <li>• Informations bancaires (cryptées)</li>
                                            <li>• Historique des transactions</li>
                                            <li>• Méthodes de paiement</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <h6 class="mb-2"><i class="bi bi-graph-up me-2 text-muted"></i>Navigation</h6>
                                        <ul class="list-unstyled small text-muted mb-0">
                                            <li>• Produits consultés</li>
                                            <li>• Préférences d'achat</li>
                                            <li>• Cookies essentiels</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="row">
                    <div class="col-12 text-center">
                        <div class="p-4 bg-white rounded shadow-sm">
                            <h5 class="mb-3">Vous voulez en savoir plus sur la protection de vos données ?</h5>
                            <p class="text-muted mb-4">
                                Consultez notre politique de confidentialité complète pour découvrir tous les détails 
                                sur la collecte, l'utilisation et la protection de vos informations personnelles.
                            </p>
                            <a href="{{ route('privacy-policy') }}" class="btn orange-bg text-white btn-sm">
                                <i class="bi bi-file-text me-2"></i>Lire la politique complète
                            </a>
                            <a href="mailto:privacy@kazaria.ci" class="btn btn-outline-secondary btn-sm ms-2">
                                <i class="bi bi-envelope me-2"></i>Nous contacter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- SECTION Politique de confidentialité END -->
    </main>

@endsection