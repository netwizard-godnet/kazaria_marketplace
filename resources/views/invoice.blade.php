@extends('layouts.app')

@section('content')
    <main class="container-fluid bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Message de succès -->
                    <div class="alert alert-success text-center mb-4">
                        <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Commande validée avec succès !</h4>
                        <p class="mb-0">Numéro de commande: <strong>{{ $order->order_number }}</strong></p>
                        <p class="small text-muted">Un email de confirmation vous a été envoyé à {{ $order->shipping_email }}</p>
                    </div>

                    <!-- Facture -->
                    <div class="card shadow" id="invoice">
                        <div class="card-body p-5">
                            <!-- En-tête -->
                            <div class="row mb-4">
                                <div class="col-6">
                                    <img src="{{ asset('images/logo.png') }}" alt="KAZARIA" height="50">
                                    <p class="mt-2 mb-0 small">
                                        <strong>KAZARIA</strong><br>
                                        E-commerce en Côte d'Ivoire<br>
                                        Email: contact@kazaria.ci<br>
                                        Tél: +225 XX XX XX XX XX
                                    </p>
                                </div>
                                <div class="col-6 text-end">
                                    <h3 class="orange-color">FACTURE</h3>
                                    <p class="mb-0">
                                        <strong>N°:</strong> {{ $order->order_number }}<br>
                                        <strong>Date:</strong> {{ $order->created_at->format('d/m/Y') }}<br>
                                        <strong>Statut:</strong> <span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
                                    </p>
                                </div>
                            </div>

                            <hr>

                            <!-- Informations client -->
                            <div class="row mb-4">
                                <div class="col-6">
                                    <h6 class="text-uppercase fw-bold mb-3">Informations client</h6>
                                    <p class="mb-0">
                                        <strong>{{ $order->shipping_name }}</strong><br>
                                        {{ $order->shipping_email }}<br>
                                        {{ $order->shipping_phone }}
                                    </p>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-uppercase fw-bold mb-3">Adresse de livraison</h6>
                                    <p class="mb-0">
                                        {{ $order->shipping_address }}<br>
                                        {{ $order->shipping_city }}
                                        @if($order->shipping_postal_code), {{ $order->shipping_postal_code }}@endif<br>
                                        {{ $order->shipping_country == 'CI' ? 'Côte d\'Ivoire' : $order->shipping_country }}
                                    </p>
                                </div>
                            </div>

                            <!-- Articles commandés -->
                            <h6 class="text-uppercase fw-bold mb-3">Articles commandés</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Article</th>
                                            <th class="text-center">Quantité</th>
                                            <th class="text-end">Prix unitaire</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ str_starts_with($item->product_image, 'http') ? $item->product_image : asset($item->product_image) }}" 
                                                         alt="{{ $item->product_name }}" 
                                                         style="width: 50px; height: 50px; object-fit: contain;" 
                                                         class="me-2">
                                                    <span>{{ $item->product_name }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">{{ number_format($item->price, 0, ',', ' ') }} FCFA</td>
                                            <td class="text-end fw-bold">{{ number_format($item->total, 0, ',', ' ') }} FCFA</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Sous-total:</th>
                                            <th class="text-end">{{ number_format($order->subtotal, 0, ',', ' ') }} FCFA</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">Livraison:</th>
                                            <th class="text-end text-success">{{ $order->shipping_cost == 0 ? 'Gratuite' : number_format($order->shipping_cost, 0, ',', ' ') . ' FCFA' }}</th>
                                        </tr>
                                        @if($order->discount > 0)
                                        <tr>
                                            <th colspan="3" class="text-end">Réduction:</th>
                                            <th class="text-end text-success">-{{ number_format($order->discount, 0, ',', ' ') }} FCFA</th>
                                        </tr>
                                        @endif
                                        <tr class="table-light">
                                            <th colspan="3" class="text-end fs-5">TOTAL:</th>
                                            <th class="text-end fs-4 orange-color">{{ number_format($order->total, 0, ',', ' ') }} FCFA</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Informations de paiement -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <strong><i class="bi bi-info-circle me-2"></i>Mode de paiement:</strong>
                                        @if($order->payment_method == 'card')
                                            Carte bancaire
                                        @elseif($order->payment_method == 'mobile_money')
                                            Mobile Money
                                        @else
                                            Paiement à la livraison
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($order->customer_notes)
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="fw-bold">Notes:</h6>
                                    <p class="text-muted">{{ $order->customer_notes }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Actions -->
                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <a href="{{ route('order-download', $order->order_number) }}" class="btn orange-bg text-white me-2">
                                        <i class="bi bi-download me-2"></i>Télécharger la facture (PDF)
                                    </a>
                                    <a href="{{ route('profil') }}?token={{ request('token') }}" class="btn btn-outline-primary">
                                        <i class="bi bi-list-ul me-2"></i>Voir mes commandes
                                    </a>
                                    <a href="{{ route('accueil') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-house me-2"></i>Retour à l'accueil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations supplémentaires -->
                    <div class="alert alert-warning mt-3">
                        <h6><i class="bi bi-bell me-2"></i>Que se passe-t-il maintenant ?</h6>
                        <ol class="mb-0 small">
                            <li>Vous recevrez un email de confirmation</li>
                            <li>Votre commande sera préparée sous 24-48h</li>
                            <li>Vous serez informé de l'expédition par email et SMS</li>
                            <li>Livraison sous 2-5 jours ouvrables</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

