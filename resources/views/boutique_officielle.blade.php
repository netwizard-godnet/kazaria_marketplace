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

        <!-- SECTION BANNER -->
        <div class="row g-2 d-flex align-items-center justify-content-center">
            <div class="col-md-12">
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
        </div>
        <!-- SECTION BANNER END -->

        <!-- SECTION DEALS JOUR -->
        <section class="pb-5 border-top">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Meilleures offres</h5>
            </div>
            <div class="row g-3">
                @for ($i = 0; $i < 6; $i++)
                <div class="col-sm-6 col-md-2">
                    <a class="px-1 text-decoration-none" href="{{ route('product-page') }}">
                        <div class="position-relative">
                            <img src="{{ asset('images/produit.jpg') }}" class="h-200px object-fit-contain" alt="...">
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

        <!-- SECTION NOUVEAUTES -->
        <section class="py-5">
            <div class="bg-light d-flex align-items-center justify-content-start mb-4 border-bottom p-2">
                <h5 class="mb-0 me-4">Nouveautés</h5>
            </div>
            <div class="row g-3">
                @for ($i = 0; $i < 6; $i++)
                <div class="col-sm-6 col-md-2">
                    <div class="px-1">
                        <div class="position-relative">
                            <img src="{{ asset('images/produit.jpg') }}" class="h-200px object-fit-contain" alt="...">
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
        <!-- SECTION NOUVEAUTES END -->

        <!-- SECTION -->
        <section class="py-5">
            <div class="row g-3">
                <div class="col-12 col-sm-3 col-md-2" style="position: sticky; top: 0;">
                    <div class="blue-bg rounded-2 p-2 text-white">
                        <p class="mb-3 fw-bold">Filtre <i class="fa-solid fa-filter"></i></p>
                        @for ($i = 0; $i < 6; $i++)
                        <div class="col-12 ps-2">
                            <p class="mb-2 fs-7">Nom catégorie</p>
                            @for ($j = 0; $j < 3; $j++)
                            <div class="col-12 ps-2">
                                <p class="mb-2 fs-8">Nom sous-catégorie</p>
                            </div>
                            @endfor
                        </div>
                        @endfor
                    </div>
                </div>
                <div class="col-12 col-sm-9 col-md-10">
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="bg-light d-flex align-items-center justify-content-between mb-4 border-bottom p-2">
                                <p class="mb-0 me-4">Produits (15 résultats)</p>
                                <div class="">
                                    <div class="dropdown">
                                        <a class="btn btn-sm dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Trier par :
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#">...</a></li>
                                            <li><a class="dropdown-item" href="#">...</a></li>
                                            <li><a class="dropdown-item" href="#">...</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @for ($i = 0; $i < 15; $i++)
                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                            <div class="px-1">
                                <div class="position-relative">
                                    <img src="{{ asset('images/produit.jpg') }}" class="h-200px object-fit-contain" alt="...">
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
                </div>
            </div>
        </section>
        <!-- SECTION END -->

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
    </main>

@endsection