@extends('layouts.app')

@section('content')
    <main class="container-fluid bg-light py-4">
        <div class="container">
            <!-- BREADCRUMB -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('profil') }}?token={{ request('token') }}">Mon Profil</a></li>
                    <li class="breadcrumb-item active">Commande {{ $order->order_number }}</li>
                </ol>
            </nav>

            <div class="row">
                <!-- Informations de la commande -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header orange-bg text-white">
                            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Commande {{ $order->order_number }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Date de commande:</strong> {{ $order->created_at->format('d/m/Y à H:i') }}</p>
                                    <p><strong>Statut:</strong> <span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span></p>
                                    <p><strong>Paiement:</strong> 
                                        @if($order->payment_method == 'card')
                                            Carte bancaire
                                        @elseif($order->payment_method == 'mobile_money')
                                            Mobile Money
                                        @else
                                            Paiement à la livraison
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total:</strong> <span class="orange-color fs-4">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span></p>
                                </div>
                            </div>

                            <!-- Timeline de suivi -->
                            <h6 class="fw-bold mb-3">Suivi de la commande</h6>
                            <div class="timeline">
                                <div class="timeline-item {{ in_array($order->status, ['pending', 'paid', 'processing', 'shipped', 'delivered']) ? 'completed' : '' }}">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Commande passée</h6>
                                        <p class="text-muted small">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item {{ in_array($order->status, ['paid', 'processing', 'shipped', 'delivered']) ? 'completed' : '' }}">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Paiement confirmé</h6>
                                        <p class="text-muted small">{{ $order->paid_at ? $order->paid_at->format('d/m/Y H:i') : 'En attente' }}</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item {{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'completed' : '' }}">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>En préparation</h6>
                                        <p class="text-muted small">{{ in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'En cours' : 'En attente' }}</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item {{ in_array($order->status, ['shipped', 'delivered']) ? 'completed' : '' }}">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Expédiée</h6>
                                        <p class="text-muted small">{{ $order->shipped_at ? $order->shipped_at->format('d/m/Y H:i') : 'En attente' }}</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item {{ $order->status == 'delivered' ? 'completed' : '' }}">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Livrée</h6>
                                        <p class="text-muted small">{{ $order->delivered_at ? $order->delivered_at->format('d/m/Y H:i') : 'En attente' }}</p>
                                    </div>
                                </div>
                            </div>

                            <style>
                                .timeline {
                                    position: relative;
                                    padding-left: 30px;
                                }
                                .timeline-item {
                                    position: relative;
                                    padding-bottom: 30px;
                                }
                                .timeline-item:before {
                                    content: '';
                                    position: absolute;
                                    left: -21px;
                                    top: 20px;
                                    height: calc(100% - 10px);
                                    width: 2px;
                                    background-color: #ddd;
                                }
                                .timeline-item:last-child:before {
                                    display: none;
                                }
                                .timeline-marker {
                                    position: absolute;
                                    left: -30px;
                                    width: 20px;
                                    height: 20px;
                                    border-radius: 50%;
                                    background-color: #ddd;
                                    border: 3px solid white;
                                }
                                .timeline-item.completed .timeline-marker {
                                    background-color: #f04e27;
                                }
                                .timeline-item.completed:before {
                                    background-color: #f04e27;
                                }
                            </style>
                        </div>
                    </div>

                    <!-- Articles commandés -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Articles ({{ $order->items->count() }})</h5>
                        </div>
                        <div class="card-body">
                            @foreach($order->items as $item)
                            <div class="row align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-2">
                                    <img src="{{ str_starts_with($item->product_image, 'http') ? $item->product_image : asset($item->product_image) }}" 
                                         class="img-fluid rounded" alt="{{ $item->product_name }}">
                                </div>
                                <div class="col-5">
                                    <h6 class="mb-1">{{ $item->product_name }}</h6>
                                    <p class="text-muted small mb-0">Quantité: {{ $item->quantity }}</p>
                                </div>
                                <div class="col-2 text-center">
                                    <p class="mb-0">{{ number_format($item->price, 0, ',', ' ') }} FCFA</p>
                                </div>
                                <div class="col-3 text-end">
                                    <p class="mb-0 fw-bold">{{ number_format($item->total, 0, ',', ' ') }} FCFA</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Résumé -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-receipt me-2"></i>Résumé</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total:</span>
                                <span>{{ number_format($order->subtotal, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Livraison:</span>
                                <span class="text-success">{{ $order->shipping_cost == 0 ? 'Gratuite' : number_format($order->shipping_cost, 0, ',', ' ') . ' FCFA' }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold orange-color">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                    </div>

                    <!-- Livraison -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-truck me-2"></i>Livraison</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>{{ $order->shipping_name }}</strong></p>
                            <p class="mb-1">{{ $order->shipping_phone }}</p>
                            <p class="mb-0 small">
                                {{ $order->shipping_address }}<br>
                                {{ $order->shipping_city }}
                                @if($order->shipping_postal_code), {{ $order->shipping_postal_code }}@endif<br>
                                {{ $order->shipping_country == 'CI' ? 'Côte d\'Ivoire' : $order->shipping_country }}
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-body">
                            <a href="{{ route('order-download', $order->order_number) }}" class="btn orange-bg text-white w-100 mb-2">
                                <i class="bi bi-download me-2"></i>Télécharger la facture
                            </a>
                            <a href="{{ route('profil') }}?token={{ request('token') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left me-2"></i>Retour à mes commandes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

