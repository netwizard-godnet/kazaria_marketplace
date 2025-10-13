@extends('layouts.app')

@section('content')
    <main class="container-fluid">
        <!-- BREADCRUMB -->
        <div class="container py-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accueil') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('product-cart') }}">Panier</a></li>
                    <li class="breadcrumb-item active">Livraison</li>
                </ol>
            </nav>
        </div>

        <!-- SECTION LIVRAISON -->
        <section class="container py-4">
            <h3 class="mb-4"><i class="bi bi-truck me-2"></i>Informations de livraison</h3>
            
            <div class="row">
                <!-- Formulaire de livraison -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Détails de livraison</h5>
                        </div>
                        <div class="card-body">
                            <form id="shippingForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="shippingName" class="form-label">Nom complet <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="shippingName" value="{{ $user->prenoms }} {{ $user->nom }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="shippingEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="shippingEmail" value="{{ $user->email }}" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="shippingPhone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="shippingPhone" value="{{ $user->telephone }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="shippingCity" class="form-label">Ville <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="shippingCity" value="{{ $user->ville ?? 'Abidjan' }}" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="shippingAddress" class="form-label">Adresse complète <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="shippingAddress" rows="3" required>{{ $user->adresse ?? '' }}</textarea>
                                    <div class="form-text">Indiquez un point de repère pour faciliter la livraison</div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="shippingPostalCode" class="form-label">Code postal</label>
                                        <input type="text" class="form-control" id="shippingPostalCode" value="{{ $user->code_postal ?? '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="shippingCountry" class="form-label">Pays <span class="text-danger">*</span></label>
                                        <select class="form-control" id="shippingCountry" required>
                                            <option value="CI" {{ ($user->pays ?? 'CI') == 'CI' ? 'selected' : '' }}>Côte d'Ivoire</option>
                                            <option value="SN" {{ ($user->pays ?? '') == 'SN' ? 'selected' : '' }}>Sénégal</option>
                                            <option value="ML" {{ ($user->pays ?? '') == 'ML' ? 'selected' : '' }}>Mali</option>
                                            <option value="BF" {{ ($user->pays ?? '') == 'BF' ? 'selected' : '' }}>Burkina Faso</option>
                                            <option value="GH" {{ ($user->pays ?? '') == 'GH' ? 'selected' : '' }}>Ghana</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="paymentMethod" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check border rounded p-3">
                                                <input class="form-check-input" type="radio" name="paymentMethod" id="paymentCard" value="card" checked>
                                                <label class="form-check-label w-100" for="paymentCard">
                                                    <i class="bi bi-credit-card me-2"></i>Carte bancaire
                                                    <br><small class="text-muted">Visa, Mastercard</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check border rounded p-3">
                                                <input class="form-check-input" type="radio" name="paymentMethod" id="paymentMobile" value="mobile_money">
                                                <label class="form-check-label w-100" for="paymentMobile">
                                                    <i class="bi bi-phone me-2"></i>Mobile Money
                                                    <br><small class="text-muted">Orange, MTN, Moov</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check border rounded p-3">
                                                <input class="form-check-input" type="radio" name="paymentMethod" id="paymentCash" value="cash_on_delivery">
                                                <label class="form-check-label w-100" for="paymentCash">
                                                    <i class="bi bi-cash me-2"></i>Paiement à la livraison
                                                    <br><small class="text-muted">En espèces</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customerNotes" class="form-label">Notes pour le livreur (optionnel)</label>
                                    <textarea class="form-control" id="customerNotes" rows="2" placeholder="Instructions spéciales pour la livraison..."></textarea>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="termsAccepted" required>
                                    <label class="form-check-label" for="termsAccepted">
                                        J'accepte les <a href="#" target="_blank">conditions générales de vente</a>
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Résumé -->
                <div class="col-md-4">
                    <div class="card position-sticky" style="top: 100px;">
                        <div class="card-header orange-bg text-white">
                            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Récapitulatif</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total:</span>
                                <span>{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Livraison:</span>
                                <span class="text-success">Gratuite</span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold fs-5">Total à payer:</span>
                                <span class="fw-bold fs-4 orange-color">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                            </div>

                            <button class="btn orange-bg text-white w-100 mb-2" id="submitOrderBtn" onclick="submitOrder()">
                                <i class="bi bi-check-circle me-2"></i>Valider la commande
                            </button>
                            
                            <a href="{{ route('product-cart') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left me-2"></i>Retour au panier
                            </a>

                            <div class="alert alert-info mt-3 small">
                                <i class="bi bi-shield-check me-2"></i>
                                <strong>Paiement sécurisé</strong><br>
                                Vos données sont protégées
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        async function submitOrder() {
            // Vérification des champs
            const form = document.getElementById('shippingForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const termsAccepted = document.getElementById('termsAccepted').checked;
            if (!termsAccepted) {
                showNotification('error', 'Vous devez accepter les conditions générales');
                return;
            }
            
            const submitBtn = document.getElementById('submitOrderBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Création de la commande...';
            
            try {
                const token = localStorage.getItem('auth_token');
                const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
                
                const response = await fetch('/api/orders/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        shipping_name: document.getElementById('shippingName').value,
                        shipping_email: document.getElementById('shippingEmail').value,
                        shipping_phone: document.getElementById('shippingPhone').value,
                        shipping_address: document.getElementById('shippingAddress').value,
                        shipping_city: document.getElementById('shippingCity').value,
                        shipping_postal_code: document.getElementById('shippingPostalCode').value,
                        shipping_country: document.getElementById('shippingCountry').value,
                        payment_method: paymentMethod,
                        customer_notes: document.getElementById('customerNotes').value
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification('success', 'Commande créée avec succès !');
                    
                    // Redirection vers la facture
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                } else {
                    showNotification('error', data.message || 'Erreur lors de la création de la commande');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('error', 'Erreur de connexion');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    </script>
@endsection

