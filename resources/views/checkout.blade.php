@extends('layouts.app')

@section('content')
    <main class="container-fluid">
        <!-- BREADCRUMB -->
        <div class="container py-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('product-cart') }}">Panier</a></li>
                    <li class="breadcrumb-item active">Validation</li>
                </ol>
            </nav>
        </div>

        <!-- SECTION CHECKOUT -->
        <section class="container py-4">
            <h3 class="mb-4"><i class="bi bi-bag-check me-2"></i>Validation de la commande</h3>
            
            <div class="row">
                <!-- Résumé de la commande -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Articles ({{ $cartItems->count() }})</h5>
                        </div>
                        <div class="card-body">
                            @foreach($cartItems as $item)
                            <div class="row align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-2">
                                    <img src="{{ str_starts_with($item->product->image, 'http') ? $item->product->image : asset($item->product->image) }}" 
                                         class="img-fluid rounded" alt="{{ $item->product->name }}">
                                </div>
                                <div class="col-6">
                                    <h6 class="mb-1">{{ $item->product->name }}</h6>
                                    <p class="text-muted small mb-0">Quantité: {{ $item->quantity }}</p>
                                </div>
                                <div class="col-4 text-end">
                                    <p class="mb-0 fw-bold">{{ number_format($item->price * $item->quantity, 0, ',', ' ') }} FCFA</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Informations de livraison</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Nom:</strong> {{ $user->prenoms }} {{ $user->nom }}</p>
                            <p><strong>Email:</strong> {{ $user->email }}</p>
                            <p><strong>Téléphone:</strong> {{ $user->telephone }}</p>
                            <p><strong>Adresse:</strong> {{ $user->adresse ?? 'Non renseignée' }}</p>
                            
                            <a href="{{ route('shipping') }}?token={{ request('token') }}" class="btn btn-outline-danger btn-sm mt-2">
                                <i class="bi bi-pencil me-1"></i>Modifier les informations de livraison
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Total et validation -->
                <div class="col-md-4">
                    <div class="card position-sticky" style="top: 100px;">
                        <div class="card-header orange-bg text-white">
                            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Récapitulatif</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total:</span>
                                <span class="fw-bold">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Livraison:</span>
                                <span class="text-success fw-bold">Gratuite</span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold fs-5">Total:</span>
                                <span class="fw-bold fs-4 orange-color">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                            </div>

                            <button class="btn orange-bg text-white w-100 mb-2" onclick="proceedToShipping()">
                                <i class="bi bi-arrow-right me-2"></i>Continuer vers la livraison
                            </button>
                            
                            <a href="{{ route('product-cart') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left me-2"></i>Retour au panier
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function proceedToShipping() {
            const token = new URLSearchParams(window.location.search).get('token');
            if (token) {
                window.location.href = '{{ route("shipping") }}?token=' + token;
            } else {
                window.location.href = '{{ route("shipping") }}';
            }
        }
    </script>
@endsection

