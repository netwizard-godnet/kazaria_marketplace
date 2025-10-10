@extends('layouts.app')

@section('content')
    <main class="container-fluid">
        <!-- SECTION -->
        <section class="container py-3">
            <div class="row gy-3">
                <div class="col-md-8">
                    <div class="row bg-light rounded-3 p-3">
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0 fw-bold text-uppercase">Panier (<span id="total1" class="mb-0"></span>)</h6>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row" id="container1">
                                @for ($i = 0; $i < 3; $i++)
                                <div class="col-12 rounded-2 p-2">
                                    <div class="row d-flex align-items-center justify-content-between">
                                        <div class="col-8">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('images/produit.jpg') }}" class="h-100px width-100 object-fit-contain me-2" alt="">
                                                <p class="mb-0 fs-7">Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="hstack gap-2 justify-content-end">
                                                <div class="mb-0">
                                                    <span class="fs-5 fw-bold blue-color text-nowrap">50.000 FCFA</span>
                                                    <div class="hstack gap-2">
                                                        <span class="text-decoration-line-through text-danger fs-7">100.000 FCFA</span>
                                                        <span class="bg-light bg-success-subtle p-1 rounded-2 fs-8">-18%</span>
                                                    </div>
                                                    <div class="d-flex align-items-center justify-content-center mt-2">
                                                        <button class="btn btn-sm orange-bg text-white">
                                                            <i class="fa-solid fa-minus"></i>
                                                        </button>
                                                        <p class="mb-0 px-3">1</p>
                                                        <button class="btn btn-sm orange-bg text-white">
                                                            <i class="fa-solid fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="hstack gap-2">
                                                <a href="" class="btn btn-sm orange-bg text-white fs-8"><i class="fa-solid fa-trash"></i> SUPPRIMER</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="bg-light p-3 rounded-3 z-index-9x" >
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0 text-uppercase fw-bold">Résumé du panier</h6>
                        </div>
                        <hr>
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0 fw-bold fs-7">Sous-total :</p>
                            <h4 class="mb-0 text-uppercase fw-bold">50.000 FCFA</h4>
                        </div>
                        <hr>
                        <div class="d-flex align-items-center justify-content-between">
                            <button class="btn btn-sm btn-secondary text-white text-uppercase fs-7 fw-bold"><i class="fa-solid fa-trash-can"></i> Vider le panier</button>
                            <i class="bi bi-dot"></i>
                            <button class="btn btn-sm orange-bg text-white text-uppercase fs-7 fw-bold"><i class="fa-solid fa-square-check"></i> Valider le panier</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- SECTION END -->
    </main>
@endsection