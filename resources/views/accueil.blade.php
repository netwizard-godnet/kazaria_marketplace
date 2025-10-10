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
                @for ($i = 0; $i < 16; $i++)
                <div class="multi-carousel-item px-2">
                    <a class="px-1 text-decoration-none" href="{{ route('product-page') }}">
                        <div class="position-relative">
                            <img src="{{ asset('images/produit.jpg') }}" class="h-200px w-100 object-fit-contain" alt="...">
                            <span class="position-absolute bottom-0 end-0 bg-light text-success fs-8 p-1 rounded-2">-18%</span>
                        </div>
                        <div class="py-1">
                            <div class="d-flex align-items-center justify-content-start fs-7">
                                <span class="fs-7 text-danger fw-bold text-nowrap me-2">50.000 FCFA</span>
                                <span class="fs-8 text-decoration-line-through text-secondary text-nowrap">100.000 FCFA</span>
                            </div>
                            <p class="fs-7 my-2 orange-color">Nom produit</p>
                            <div class="hstack gap-1">
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-secondary fs-8"></i>
                            </div>
                        </div>
                    </a>
                </div>
                @endfor
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
                @for ($i = 0; $i < 6; $i++)
                <div class="col-6 col-sm-4 col-md-2">
                    <a class="px-1 card text-decoration-none" href="{{ route('product-page') }}">
                        <div class="position-relative text-center">
                            <img src="{{ asset('images/produit.jpg') }}" class="w-100 h-200px object-fit-contain" alt="...">
                        </div>
                        <div class="py-1">
                            <p class="fs-7 my-2 orange-color text-center">Nom catégorie</p>
                        </div>
                    </a>
                </div>
                @endfor
            </div>
        </section>
        <!-- SECTION TOP CATEGORIES END -->

        <!-- SECTION TELEPHONES & TABLETTES -->
        <section class="py-5">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Téléphones et tablettes</h5>
            </div>
            <div class="row g-3">
                @for ($i = 0; $i < 6; $i++)
                <div class="col-6 col-sm-4 col-md-2">
                    <a class="px-1 text-decoration-none" href="{{ route('product-page') }}">
                        <div class="position-relative">
                            <img src="{{ asset('images/produit.jpg') }}" class="w-100 h-200px object-fit-contain" alt="...">
                            <span class="position-absolute bottom-0 end-0 bg-light text-success fs-8 p-1 rounded-2">-18%</span>
                        </div>
                        <div class="py-1">
                            <div class="d-flex align-items-center justify-content-start fs-7">
                                <span class="fs-7 text-danger fw-bold text-nowrap me-2">50.000 FCFA</span>
                                <span class="fs-8 text-decoration-line-through text-secondary text-nowrap">100.000 FCFA</span>
                            </div>
                            <p class="fs-7 my-2 orange-color">Nom produit</p>
                            <div class="hstack gap-1">
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-secondary fs-8"></i>
                            </div>
                        </div>
                    </a>
                </div>
                @endfor
            </div>
        </section>
        <!-- SECTION TELEPHONES & TABLETTES END -->

        <!-- SECTION TV et Electronique -->
        <section class="py-5">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">TV et Electronique</h5>
            </div>
            <div class="row g-3">
                @for ($i = 0; $i < 6; $i++)
                <div class="col-6 col-sm-4 col-md-2">
                    <a class="px-1 text-decoration-none" href="{{ route('product-page') }}">
                        <div class="position-relative">
                            <img src="{{ asset('images/produit.jpg') }}" class="w-100 h-200px object-fit-contain" alt="...">
                            <span class="position-absolute bottom-0 end-0 bg-light text-success fs-8 p-1 rounded-2">-18%</span>
                        </div>
                        <div class="py-1">
                            <div class="d-flex align-items-center justify-content-start fs-7">
                                <span class="fs-7 text-danger fw-bold text-nowrap me-2">50.000 FCFA</span>
                                <span class="fs-8 text-decoration-line-through text-secondary text-nowrap">100.000 FCFA</span>
                            </div>
                            <p class="fs-7 my-2 orange-color">Nom produit</p>
                            <div class="hstack gap-1">
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-secondary fs-8"></i>
                            </div>
                        </div>
                    </a>
                </div>
                @endfor
            </div>
        </section>
        <!-- SECTION TV et Electronique END -->

        <!-- SECTION Electroménager -->
        <section class="py-5">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Electroménager</h5>
            </div>
            <div class="row g-3">
                @for ($i = 0; $i < 6; $i++)
                <div class="col-6 col-sm-4 col-md-2">
                    <a class="px-1 text-decoration-none" href="{{ route('product-page') }}">
                        <div class="position-relative">
                            <img src="{{ asset('images/produit.jpg') }}" class="w-100 h-200px object-fit-contain" alt="...">
                            <span class="position-absolute bottom-0 end-0 bg-light text-success fs-8 p-1 rounded-2">-18%</span>
                        </div>
                        <div class="py-1">
                            <div class="d-flex align-items-center justify-content-start fs-7">
                                <span class="fs-7 text-danger fw-bold text-nowrap me-2">50.000 FCFA</span>
                                <span class="fs-8 text-decoration-line-through text-secondary text-nowrap">100.000 FCFA</span>
                            </div>
                            <p class="fs-7 my-2 orange-color">Nom produit</p>
                            <div class="hstack gap-1">
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-secondary fs-8"></i>
                            </div>
                        </div>
                    </a>
                </div>
                @endfor
            </div>
        </section>
        <!-- SECTION Electroménager END -->

        <!-- SECTION Ordinateurs et accessoires -->
        <section class="py-5">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Ordinateurs et accessoires</h5>
            </div>
            <div class="row g-3">
                @for ($i = 0; $i < 6; $i++)
                <div class="col-6 col-sm-4 col-md-2">
                    <a class="px-1 text-decoration-none" href="{{ route('product-page') }}">
                        <div class="position-relative">
                            <img src="{{ asset('images/produit.jpg') }}" class="w-100 h-200px object-fit-contain" alt="...">
                            <span class="position-absolute bottom-0 end-0 bg-light text-success fs-8 p-1 rounded-2">-18%</span>
                        </div>
                        <div class="py-1">
                            <div class="d-flex align-items-center justify-content-start fs-7">
                                <span class="fs-7 text-danger fw-bold text-nowrap me-2">50.000 FCFA</span>
                                <span class="fs-8 text-decoration-line-through text-secondary text-nowrap">100.000 FCFA</span>
                            </div>
                            <p class="fs-7 my-2 orange-color">Nom produit</p>
                            <div class="hstack gap-1">
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-secondary fs-8"></i>
                            </div>
                        </div>
                    </a>
                </div>
                @endfor
            </div>
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
        <section class="py-5">
            <div class="bg-light d-flex align-items-center justify-content-between mb-4 border-bottom p-2">
                <h5 class="mb-0">Tendance</h5>
                <div class="">
                    <div class="hstack gap-2">
                        @for ($i = 0; $i < 2; $i++)
                        <div class="">Smartphone</div>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="row g-3">
                @for ($i = 0; $i < 6; $i++)
                <div class="col-6 col-sm-4 col-md-2">
                    <div class="px-1">
                        <div class="position-relative">
                            <img src="{{ asset('images/produit.jpg') }}" class="w-100 h-200px object-fit-contain" alt="...">
                            <span class="position-absolute bottom-0 end-0 bg-light text-success fs-8 p-1 rounded-2">-18%</span>
                        </div>
                        <div class="py-1">
                            <div class="d-flex align-items-center justify-content-start fs-7">
                                <span class="fs-7 text-danger fw-bold text-nowrap me-2">50.000 FCFA</span>
                                <span class="fs-8 text-decoration-line-through text-secondary text-nowrap">100.000 FCFA</span>
                            </div>
                            <p class="fs-7 my-2 orange-color">Nom produit</p>
                            <div class="hstack gap-1">
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-warning fs-8"></i>
                                <i class="fa-solid fa-star text-secondary fs-8"></i>
                            </div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </section>
        <!-- SECTION Ordinateurs et accessoires END -->
    </main>

@endsection