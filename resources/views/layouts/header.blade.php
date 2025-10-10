<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <!-- Fontawesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-(your integrity hash)" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
        <!-- SLICK -->
        <link rel="stylesheet" href="{{ asset('slick/slick.css') }}">
        <link rel="stylesheet" href="{{ asset('slick/slick-theme.css') }}">
        <!-- CUSTOM CSS -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('css/profil.css') }}">
        <link rel="stylesheet" href="{{ asset('css/carousel.css') }}">
        <!-- FONTS -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    </head>

    <body>
        <!-- Header -->
        <div class="container-fluid orange-bg py-2">
            <div class="container flex-column d-flex align-items-center justify-content-between flex-md-row">
                <div class="mb-3 mb-md-0">
                    <p class="mb-0 fs-8 text-uppercase">Mega sale Season sale with discount up to 50%, Hurry up! Don’t miss out. 
                        <a href="" class="text-white">Know More</a>
                    </p>
                </div>
                <div class="d-flex align-items-center justify-content-evenly">
                    <a href="" class="btn btn-sm border-end rounded-0 text-white fs-8">STORE LOCATION</a>
                    <a href="" class="btn btn-sm border-end rounded-0 text-white fs-8">TRACK YOUR ORDER</a>
                    <a href="" class="btn btn-sm rounded-0 text-white fs-8">Francs CFA</a>
                </div>
            </div>
        </div>
        <header class="z-index-9x shadow d-none d-sm-block" style="position: sticky; top: 0;">
            <div class="container-fluid blue-bg py-0 position-relative">
                <nav class="navbar navbar-expand-lg py-0">
                    <div class="container-fluid">
                        <a class="navbar-brand fw-bolder text-white fs-2" href="{{ route('accueil') }}">
                            <img src="{{ asset('images/logo.png') }}" class="logo-size-header" alt="Bienvenue sur KAZARIA">
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <form class="d-flex ms-auto" action="{{ route('search_product') }}" role="search">
                                <div class="bg-light d-flex align-items-center justify-content-between rounded-2 me-2">
                                    <div class="dropdown">
                                        <button class="btn dropdown-toggle fs-7" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Toutes les catégories
                                        </button>
                                        <ul class="bg-light dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('categorie') }}">Action</a></li>
                                            <li><a class="dropdown-item" href="#">Another action</a></li>
                                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                                        </ul>
                                    </div>
                                    <input class="form-control px-4 me-2 border-0 rounded-1 width-400" type="search" placeholder="Je veux acheter..." aria-label="Search"/>
                                </div>
                                <button class="btn orange-bg rounded-1 text-white text-uppercase fw-bolder" type="submit">
                                Rechercher
                                </button>
                            </form>
                            <ul class="navbar-nav">
                                <li class="nav-item px-1 d-flex align-items-center justify-content-center">
                                    <a class="nav-link d-flex align-items-center" aria-current="page" href="#">
                                        <i class="fa-solid fas fa-heart fa-2x text-white"></i>
                                    </a>
                                </li>
                                <li class="nav-item px-1 d-flex align-items-center justify-content-center">
                                    <a class="nav-link position-relative" aria-current="page" href="{{ route('product-cart') }}">
                                        <i class="fa-solid fas fa-shopping-cart text-white fa-2x"></i>
                                        <span class="position-absolute bottom-0 end-0 bg-danger px-2 rounded-2 fw-lighter fs-8 text-white">0</span>
                                    </a>
                                </li>
                                <li class="nav-item px-1 d-flex align-items-center justify-content-center">
                                <a class="nav-link d-flex align-items-center" aria-current="page" href="{{ route('profil') }}">
                                    <i class="fa-solid fa-user text-white fa-2x"></i>
                                    <div class="vstack text-white">
                                    <span class="fs-8 fw-lighter">Connexion</span>
                                    <span class="fs-8 fw-lighter">Inscription</span>
                                    </div>
                                </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
                <hr class="text-white my-1">
                <div class="row gx-2 py-2">
                    <!--  -->
                    <div class="col-md-8 hstack gap-1">
                        <div class="d-flex align-items-center justify-content-start">
                            <a class="btn btn-sm orange-bg text-white fs-7" href="{{ route('boutique_officielle') }}">
                            Boutiques Officielles <i class="fa-solid fa-certificate"></i>
                            </a>
                        </div>
                        <div class="header-menu d-flex align-items-center justify-content-start">
                            <a class="btn btn-sm text-white fs-7" type="button">
                            Téléphones et tablettes <i class="fa-solid fas fa-chevron-down fs-8"></i>
                            </a>
                            <div class="w-100 bg-light pb-5 position-absolute top-100 start-0 z-index-9x d-none container-fluid">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg text-white rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg text-white rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg text-white rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg text-white rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="header-menu d-flex align-items-center justify-content-start">
                            <a class="btn btn-sm text-white fs-7" type="button">
                            TV et Eléctronique <i class="fa-solid fas fa-chevron-down fs-8"></i>
                            </a>
                            <div class="w-100 bg-light pb-5 position-absolute top-100 start-0 z-index-9x d-none container-fluid">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg text-white rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg text-white rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg text-white rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="header-menu d-flex align-items-center justify-content-start">
                            <a class="btn btn-sm text-white fs-7" type="button">
                            Electroménager <i class="fa-solid fas fa-chevron-down fs-8"></i>
                            </a>
                            <div class="w-100 bg-light pb-5 position-absolute top-100 start-0 z-index-9x d-none container-fluid">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="header-menu d-flex align-items-center justify-content-start">
                            <a class="btn btn-sm text-white fs-7" type="button">
                            Ordinateurs et accessoires <i class="fa-solid fas fa-chevron-down fs-8"></i>
                            </a>
                            <div class="w-100 bg-light pb-5 position-absolute top-100 start-0 z-index-9x d-none container-fluid">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="list-group">
                                            <a href="#" class="list-group-item list-group-item-action orange-bg rounded-0" aria-current="true">
                                                Catégorie
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">A second link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A third link item</a>
                                            <a href="#" class="list-group-item list-group-item-action">A fourth link item</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-1">
                        
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center justify-content-start">
                            <a href="" class="btn btn-sm fs-7 text-white rounded-0 border-end pe-3" style="border-right-color:var(--main-color)!important;">Vendez sur KAZARIA</a>
                            <a href="" class="btn btn-sm fs-7 text-white rounded-0 ps-3">Suivre ma commande</a>
                        </div>
                    </div>
                    <!--  -->
                </div>
            </div>
        </header>

        <!-- Mobile Header -->
        <header class="z-index-9x shadow d-sm-none" style="position: sticky; top: 0;">
            <div class="container-fluid blue-bg py-3 position-relative">
                <nav class="py-0">
                    <div class="vstack gap-2">
                        <div class="w-100 d-flex align-items-center justify-content-between">
                            <a class="" href="#">
                                <img src="{{ asset('images/logo.png') }}" class="logo-size-header" alt="Bienvenue sur KAZARIA">
                            </a>
                            <ul class="d-flex align-items-center justify-content-evenly m-0">
                                <li class="nav-item px-1 d-flex align-items-center justify-content-center">
                                    <a class="nav-link position-relative" aria-current="page" href="#">
                                        <i class="fa-solid fas fa-shopping-cart text-white fa-2x"></i>
                                        <span class="position-absolute bottom-0 end-0 bg-danger px-2 rounded-2 fw-lighter fs-8 text-white">0</span>
                                    </a>
                                </li>
                                <li class="nav-item px-1 d-flex align-items-center justify-content-center">
                                    <a class="nav-link d-flex align-items-center" aria-current="page" href="#">
                                        <i class="fa-solid fa-user text-white fa-2x"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="">
                            <form class="d-flex ms-auto" role="search">
                                <div class="bg-light d-flex align-items-center justify-content-between rounded-2 me-2">
                                    <input class="form-control px-4 me-2 border-0 width-400" type="search" placeholder="Search" aria-label="Search"/>
                                </div>
                                <button class="btn orange-bg text-white text-uppercase fw-bolder" type="submit">
                                Rechercher
                                </button>
                            </form>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
        <!-- Header end -->