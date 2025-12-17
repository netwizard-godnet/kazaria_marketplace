@extends('admin.layouts.app')

@section('title', 'Détails de la commande')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Détails de la commande #{{ $order->order_number ?? 'N/A' }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.orders.index') }}">Commandes</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span>{{ $order->order_number }}</span>
            </li>
        </ul>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <!-- Informations de la commande -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informations de la commande</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Informations client</h6>
                            <p><strong>Nom:</strong> {{ $order->user->prenoms ?? 'N/A' }} {{ $order->user->nom ?? '' }}</p>
                            <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
                            <p><strong>Téléphone:</strong> {{ $order->user->telephone ?? $order->shipping_phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Statuts</h6>
                            <p><strong>Statut commande:</strong> 
                                <span class="badge bg-{{ $order->status_class }}">{{ $order->status_label }}</span>
                            </p>
                            <p><strong>Statut paiement:</strong> 
                                <span class="badge bg-{{ $order->payment_status_class }}">{{ $order->payment_status_label }}</span>
                            </p>
                            <p><strong>Méthode de paiement:</strong> 
                                @if($order->payment_method == 'card')
                                    Carte bancaire
                                @elseif($order->payment_method == 'mobile_money')
                                    Mobile Money
                                @elseif($order->payment_method == 'cash_on_delivery')
                                    Paiement à la livraison
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Articles de la commande -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Articles commandés</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Boutique</th>
                                    <th>Prix unitaire</th>
                                    <th>Quantité</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->product_image)
                                                <img src="{{ str_starts_with($item->product_image, 'http') ? $item->product_image : (str_starts_with($item->product_image, 'storage/') ? asset($item->product_image) : asset('storage/' . $item->product_image)) }}" 
                                                     alt="{{ $item->product_name }}" 
                                                     class="me-3" 
                                                     style="width: 50px; height: 50px; object-fit: cover;"
                                                     onerror="this.src='{{ asset('images/produit.jpg') }}'">
                                            @endif
                                            <div>
                                                <strong>{{ $item->product_name }}</strong>
                                                @php
                                                    // Récupérer les attributs bruts depuis la base de données
                                                    $rawAttributes = $item->getAttributes()['attributes'] ?? null;
                                                    $attrs = $item->attributes; // Via l'accesseur
                                                    
                                                    // Debug (à retirer après)
                                                    // \Log::info('Admin Order Item Debug', [
                                                    //     'item_id' => $item->id,
                                                    //     'raw_attributes' => $rawAttributes,
                                                    //     'attributes_via_accessor' => $attrs,
                                                    //     'attributes_type' => gettype($attrs),
                                                    // ]);
                                                    
                                                    // Convertir en tableau pour vérifier
                                                    $attrsArray = [];
                                                    if (is_object($attrs)) {
                                                        $attrsArray = (array)$attrs;
                                                    } elseif (is_array($attrs)) {
                                                        $attrsArray = $attrs;
                                                    }
                                                    
                                                    $hasAttributes = !empty($attrsArray) && count($attrsArray) > 0;
                                                @endphp
                                                @if($item->variation && $item->variation->attributeValues && $item->variation->attributeValues->count() > 0)
                                                    {{-- Afficher les attributs de la variation --}}
                                                    @php
                                                        $groupedAttributes = $item->variation->attributeValues->groupBy('attribute.name');
                                                    @endphp
                                                    <div class="mt-1">
                                                        @foreach($groupedAttributes as $attrName => $values)
                                                            <small class="text-muted d-block">
                                                                <strong>{{ $attrName }}:</strong>
                                                                <span class="text-primary">
                                                                    {{ $values->pluck('value')->implode(', ') }}
                                                                </span>
                                                            </small>
                                                        @endforeach
                                                    </div>
                                                @elseif($hasAttributes)
                                                    {{-- Fallback: afficher les attributs stockés dans le champ attributes --}}
                                                    <div class="mt-1">
                                                        @foreach($attrsArray as $attrName => $attrValue)
                                                            <small class="text-muted d-block">
                                                                <strong>{{ ucfirst($attrName) }}:</strong>
                                                                <span class="text-primary">
                                                                    {{ is_array($attrValue) ? implode(', ', $attrValue) : $attrValue }}
                                                                </span>
                                                            </small>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->product && $item->product->store)
                                            <span class="badge bg-info">{{ $item->product->store->name }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->price, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td><strong>{{ number_format($item->total, 0, ',', ' ') }} FCFA</strong></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Adresse de livraison -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Adresse de livraison</h4>
                </div>
                <div class="card-body">
                    <p><strong>Nom:</strong> {{ $order->shipping_name }}</p>
                    <p><strong>Email:</strong> {{ $order->shipping_email }}</p>
                    <p><strong>Téléphone:</strong> {{ $order->shipping_phone }}</p>
                    <p><strong>Adresse:</strong> {{ $order->shipping_address }}</p>
                    <p><strong>Ville:</strong> {{ $order->shipping_city }}</p>
                    @if($order->shipping_postal_code)
                        <p><strong>Code postal:</strong> {{ $order->shipping_postal_code }}</p>
                    @endif
                    <p><strong>Pays:</strong> {{ $order->shipping_country }}</p>
                </div>
            </div>

            @if($order->customer_notes)
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Notes du client</h4>
                </div>
                <div class="card-body">
                    <p>{{ $order->customer_notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <!-- Résumé financier -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Résumé financier</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total:</span>
                        <span>{{ number_format($order->subtotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Frais de livraison:</span>
                        <span>{{ number_format($order->shipping_cost, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @if($order->tax > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Taxes:</span>
                        <span>{{ number_format($order->tax, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @endif
                    @if($order->discount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Remise:</span>
                        <span class="text-success">-{{ number_format($order->discount, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="text-primary">{{ number_format($order->total, 0, ',', ' ') }} FCFA</strong>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- Actions de statut -->
                        @if($order->status === 'pending')
                            <button class="btn btn-warning" onclick="updateOrderStatus('processing')">
                                <i class="fas fa-play me-2"></i>Marquer en cours de livraison
                            </button>
                            <button class="btn btn-danger" onclick="updateOrderStatus('cancelled')">
                                <i class="fas fa-times me-2"></i>Annuler la commande
                            </button>
                        @elseif($order->status === 'processing')
                            <button class="btn btn-success" onclick="updateOrderStatus('delivered')">
                                <i class="fas fa-check me-2"></i>Marquer comme livrée
                            </button>
                            <button class="btn btn-danger" onclick="updateOrderStatus('cancelled')">
                                <i class="fas fa-times me-2"></i>Annuler la commande
                            </button>
                        @elseif($order->status === 'delivered')
                            <button class="btn btn-info" onclick="updateOrderStatus('processing')">
                                <i class="fas fa-undo me-2"></i>Retour en cours de livraison
                            </button>
                            <button class="btn btn-danger" onclick="updateOrderStatus('cancelled')">
                                <i class="fas fa-times me-2"></i>Annuler la commande
                            </button>
                        @elseif($order->status === 'cancelled')
                            <button class="btn btn-primary" onclick="updateOrderStatus('pending')">
                                <i class="fas fa-redo me-2"></i>Réactiver la commande
                            </button>
                            <button class="btn btn-warning" onclick="updateOrderStatus('processing')">
                                <i class="fas fa-play me-2"></i>Marquer en cours de livraison
                            </button>
                        @endif

                        <!-- Actions de paiement -->
                        @if($order->payment_status === 'pending')
                            <button class="btn btn-success" onclick="updatePaymentStatus('paid')">
                                <i class="fas fa-check-circle me-2"></i>Marquer comme payé
                            </button>
                        @else
                            <button class="btn btn-warning" onclick="updatePaymentStatus('pending')">
                                <i class="fas fa-clock me-2"></i>Paiement en attente
                            </button>
                        @endif

                        <!-- Actions spéciales -->
                        <button class="btn btn-info" onclick="printOrder()">
                            <i class="fas fa-print me-2"></i>Imprimer la commande
                        </button>
                        
                        @if($order->status === 'cancelled')
                            <button class="btn btn-danger" onclick="deleteOrder()">
                                <i class="fas fa-trash me-2"></i>Supprimer la commande
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations système -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informations système</h4>
                </div>
                <div class="card-body">
                    <p><strong>Date de création:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Dernière modification:</strong> {{ $order->updated_at->format('d/m/Y H:i') }}</p>
                    @if($order->paid_at)
                        <p><strong>Date de paiement:</strong> {{ $order->paid_at->format('d/m/Y H:i') }}</p>
                    @endif
                    @if($order->shipped_at)
                        <p><strong>Date d'expédition:</strong> {{ $order->shipped_at->format('d/m/Y H:i') }}</p>
                    @endif
                    @if($order->delivered_at)
                        <p><strong>Date de livraison:</strong> {{ $order->delivered_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateOrderStatus(status) {
    if (!confirm('Êtes-vous sûr de vouloir changer le statut de cette commande ?')) {
        return;
    }
    
    fetch(`/admin/orders/{{ $order->id }}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            status: status, 
            reason: 'Changement de statut par l\'admin' 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la mise à jour');
    });
}

function updatePaymentStatus(paymentStatus) {
    if (!confirm('Êtes-vous sûr de vouloir changer le statut de paiement de cette commande ?')) {
        return;
    }
    
    fetch(`/admin/orders/{{ $order->id }}/payment-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            payment_status: paymentStatus, 
            reason: 'Changement de statut de paiement par l\'admin' 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la mise à jour');
    });
}

function printOrder() {
    // Ouvrir la facture dans une nouvelle fenêtre
    const invoiceUrl = '{{ route("admin.orders.invoice", $order->id) }}';
    const printWindow = window.open(invoiceUrl, '_blank');
    
    // Attendre que la page soit chargée puis déclencher l'impression
    if (printWindow) {
        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
            }, 500);
        };
    }
}

function deleteOrder() {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette commande ? Cette action est irréversible.')) {
        return;
    }
    
    fetch(`/admin/orders/{{ $order->id }}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => window.location.href = '/admin/orders', 1000);
        } else {
            showNotification('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('danger', 'Erreur lors de la suppression');
    });
}

function showNotification(type, message) {
    // Créer une notification toast
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Supprimer l'élément après 5 secondes
    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
@endpush