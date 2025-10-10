@extends('layouts.app')

@section('content')
    <main class="container-fluid">
        <!-- SECTION BREADCRUMB -->
        <section class="bg-light py-2">
            <div class="container-fluid">
                <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb" class="">
                    <ol class="breadcrumb" class="">
                        <li class="breadcrumb-item mb-0"><a href="{{ route('accueil') }}" class="fs-7">Accueil</a></li>
                        <li class="breadcrumb-item mb-0 active fs-7" aria-current="page">{{ Route::currentRouteName() }}</li>
                    </ol>
                </nav>
            </div>
        </section>
        <!-- SECTION BREADCRUMB END -->

        <!-- SECTION -->
        <section class="pb-5 border-top">
            <div class="container-fluid bg-light">
                <div class="container py-2">
                    <div class="row g-4">
                        <div class="col-md-5 bg-light-subtle p-4">
                            <div class="row g-3 d-flex align-items-center justify-content-center">
                                <div class="col-12">
                                    <img src="{{ asset('images/produit.jpg') }}" class="w-100 h-400px object-fit-contain" alt="">
                                </div>
                                @for ($i = 0; $i < 6; $i++)
                                <div class="col-2">
                                    <img src="{{ asset('images/produit.jpg') }}" class="w-100 h-100 object-fit-contain" alt="">
                                </div>
                                @endfor
                            </div>
                        </div>
                        <div class="col-md-4 p-4">
                            <div>
                                <a class="btn btn-sm fs-8 blue-bg text-white px-1 py-0">Boutique Officielle</a>
                                <!-- NOM PRODUIT -->
                                <p class="mb-0 mt-3 fs-5">Lorem ipsum dolor sit amet consectetur adipisicing elit. Accusamus, odit!</p>
                                <p class="mb-0 mt-3 fs-8">Marque : <span>Lorem</span> | <span>Lorem, ipsum dolor sit</span></p>
                                <!-- NOM PRODUIT END -->
                                <hr>
                                <div class="d-flex align-items-center justify-content-start">
                                    <span class="fs-3 orange-color fw-bold text-nowrap me-2">50.000 FCFA</span>
                                    <span class="fs-6 text-decoration-line-through text-secondary text-nowrap">100.000 FCFA</span>
                                </div>
                                <div class="mb-3">
                                    <span class="fs-8 fw-bold orange-color mb-3">Vous avez épargné 50.000 FCFA</span>
                                </div>
                                <div class="hstack gap-1">
                                    <i class="fa-solid fa-star text-secondary fs-7"></i>
                                    <i class="fa-solid fa-star text-secondary fs-7"></i>
                                    <i class="fa-solid fa-star text-secondary fs-7"></i>
                                    <i class="fa-solid fa-star text-secondary fs-7"></i>
                                    <i class="fa-solid fa-star text-secondary fs-7"></i>
                                    <p class="mb-0 fs-8">(Pas d'avis pour le moment)</p>
                                </div>
                                <hr>
                                <div class="hstack gap-2">
                                    <a href="" class="btn orange-bg text-white"><i class="bi bi-cart"></i> Ajouter au panier</a>
                                    <a href="" class="btn btn-secondary"><i class="bi bi-heart"></i></a>
                                </div>
                                <hr>
                                <div>
                                    <p class="mb-2 text-uppercase fs-7 fw-bold">Partager ce produit</p>
                                    <div class="hstack gap-2">
                                        <a href="" class="btn btn-outline-primary"><i class="bi bi-facebook"></i></a>
                                        <a href="" class="btn btn-outline-success"><i class="bi bi-whatsapp"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light-subtle p-2 rounded-2 mb-3">
                                <p class="mb-3 fw-bold text-uppercase">Livraison en 24H partout à :</p>
                                <hr>
                                <span class="orange-bg text-white rounded-5 px-2 py-1 fs-8">Abidjan</span><br>
                                <a href="" class="btn btn-sm orange-color fs-8 mt-2">Termes & Condition</a>
                            </div>
                            <div class="bg-light-subtle p-2 rounded-2">
                                <p class="mb-3 fw-bold text-uppercase">Livraison & Retours</p>
                                <hr>
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-truck fa-2x orange-color me-3"></i>
                                    <p class="mb-0 fs-8">
                                        <span class="orange-color fs-6 fw-bold">Livraison</span><br>
                                        Livraison express disponible uniquement pour les produits KAZARIA <br>
                                        <span class="orange-color fs-7 fw-bold">Livraison le jour même :</span> veuillez passer votre commande avant 15h (sauf le dimanche). <br>
                                        <span class="orange-color fs-7 fw-bold">Livraison le lendemain :</span> les commandes passées après 15h seront livrées le lendemain.<br>
                                        <span class="orange-color fs-7 fw-bold">Remarque :</span> la disponibilité peut varier selon la région. <br>
                                    </p>
                                </div>
                                <hr>
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-arrow-clockwise fa-2x orange-color me-3"></i>
                                    <p class="mb-0 fs-8">
                                        <span class="orange-color fs-6 fw-bold">Politique de retour</span><br>
                                        <span class="fs-7 fw-bold">Retour garanti sous 7 jours</span><br>
                                        Pour plus d'informations sur les options de retour, veuillez consulter la politique de retour de Konga.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-light-subtle p-2 rounded-2">
                                <nav>
                                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                        <button class="nav-link blue-color active" id="nav-descripProduit-tab" data-bs-toggle="tab" data-bs-target="#nav-descripProduit" type="button" role="tab" aria-controls="nav-descripProduit" aria-selected="true">Description</button>
                                        <button class="nav-link blue-color" id="nav-ficheTech-tab" data-bs-toggle="tab" data-bs-target="#nav-ficheTech" type="button" role="tab" aria-controls="nav-ficheTech" aria-selected="false">Fiche Technique</button>
                                        <button class="nav-link blue-color" id="nav-avisProduit-tab" data-bs-toggle="tab" data-bs-target="#nav-avisProduit" type="button" role="tab" aria-controls="nav-avisProduit" aria-selected="false">Avis (0)</button>
                                    </div>
                                </nav>
                                <div class="tab-content" id="nav-tabContent">
                                    <div class="tab-pane fade show active" id="nav-descripProduit" role="tabpanel" aria-labelledby="nav-descripProduit-tab" tabindex="0">...</div>
                                    <div class="tab-pane fade" id="nav-ficheTech" role="tabpanel" aria-labelledby="nav-ficheTech-tab" tabindex="0">...</div>
                                    <div class="tab-pane fade" id="nav-avisProduit" role="tabpanel" aria-labelledby="nav-avisProduit-tab" tabindex="0">...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- SECTION END -->
    </main>

@endsection