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
                                    @if($item->attributes && (is_array($item->attributes) || is_object($item->attributes)) && count((array)$item->attributes) > 0)
                                        <div class="mt-2">
                                            @foreach($item->attributes as $attrName => $attrValue)
                                                <div class="mb-1">
                                                    <small class="text-muted fw-bold">{{ ucfirst($attrName) }}:</small>
                                                    <small class="text-primary">
                                                        {{ is_array($attrValue) ? implode(', ', $attrValue) : $attrValue }}
                                                    </small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="col-4 text-end">
                                    <p class="mb-0 fw-bold">{{ number_format($item->price * $item->quantity, 0, ',', ' ') }} FCFA</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    @if($user)
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
                    @else
                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="bi bi-person me-2"></i>Informations de livraison</h5>
                            </div>
                            <div class="card-body">
                                <form id="checkoutForm">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="checkoutName" class="form-label">Nom complet <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="checkoutName" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="checkoutEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="checkoutEmail" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="checkoutPhone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" id="checkoutPhone" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="checkoutCity" class="form-label">Ville <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="checkoutCity" value="Abidjan" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="checkoutAddress" class="form-label">Adresse complète <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="checkoutAddress" rows="3" required></textarea>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="checkoutPostalCode" class="form-label">Code postal</label>
                                            <input type="text" class="form-control" id="checkoutPostalCode">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="checkoutCountry" class="form-label">Pays <span class="text-danger">*</span></label>
                                            <select class="form-control" id="checkoutCountry" required>
                                                <option value="CI" selected>Côte d'Ivoire</option>
                                                <option value="SN">Sénégal</option>
                                                <option value="ML">Mali</option>
                                                <option value="BF">Burkina Faso</option>
                                                <option value="GH">Ghana</option>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
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
                                <span class="fw-bold">{{ number_format($subtotal ?? $total, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @if(($discount ?? 0) > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Réduction @if(($promo['code'] ?? null))<small class="text-muted">({{ $promo['code'] }})</small>@endif:</span>
                                <span class="text-success">- {{ number_format($discount, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endif
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Livraison:</span>
                                @if(($shippingCost ?? 0) > 0)
                                    <span class="fw-bold">{{ number_format($shippingCost, 0, ',', ' ') }} FCFA</span>
                                @else
                                    <span class="text-success fw-bold">Gratuite</span>
                                @endif
                            </div>
                            
                            @if(isset($freeThreshold) && $freeThreshold > 0 && $subtotal < $freeThreshold)
                            <div class="small text-muted mb-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Livraison gratuite à partir de {{ number_format($freeThreshold, 0, ',', ' ') }} FCFA
                                ({{ number_format($freeThreshold - $subtotal, 0, ',', ' ') }} FCFA restants)
                            </div>
                            @endif
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold fs-5">Total:</span>
                                <span class="fw-bold fs-4 orange-color">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                            </div>

                            @if($user)
                                <button class="btn orange-bg text-white w-100 mb-2" onclick="proceedToShipping()">
                                    <i class="bi bi-arrow-right me-2"></i>Continuer vers la livraison
                                </button>
                            @else
                                <button class="btn orange-bg text-white w-100 mb-2" onclick="proceedToCheckout()">
                                    <i class="bi bi-arrow-right me-2"></i>Continuer vers la livraison
                                </button>
                            @endif
                            
                            <a href="{{ route('product-cart') }}" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-arrow-left me-2"></i>Retour au panier
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal pour authentification (compte existant) -->
    <div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="authModalLabel">
                        <i class="bi bi-shield-lock me-2"></i>Confirmer votre identité
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Un compte existe déjà avec cet email. Veuillez entrer votre mot de passe pour continuer.</p>
                    <form id="authForm">
                        <div class="mb-3">
                            <label for="authPassword" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="authPassword" required>
                            <div class="invalid-feedback" id="authPasswordError"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn orange-bg text-white" onclick="authenticateForOrder()">
                        <i class="bi bi-check-circle me-2"></i>Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour définir mot de passe (nouveau compte) -->
    <div class="modal fade" id="passwordSetupModal" tabindex="-1" aria-labelledby="passwordSetupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="passwordSetupModalLabel">
                        <i class="bi bi-key me-2"></i>Sécuriser votre compte
                    </h5>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Votre commande a été créée avec succès ! Veuillez définir un mot de passe pour sécuriser votre compte.</p>
                    <form id="passwordSetupForm">
                        <div class="mb-3">
                            <label for="setupPassword" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="setupPassword" minlength="8" required>
                            <small class="form-text text-muted">Minimum 8 caractères</small>
                            <div class="invalid-feedback" id="setupPasswordError"></div>
                        </div>
                        <div class="mb-3">
                            <label for="setupPasswordConfirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="setupPasswordConfirmation" minlength="8" required>
                            <div class="invalid-feedback" id="setupPasswordConfirmationError"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn orange-bg text-white" onclick="setupPassword()">
                        <i class="bi bi-check-circle me-2"></i>Définir le mot de passe
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let checkoutData = {};
        let pendingUserId = null;

        function proceedToShipping() {
            const token = new URLSearchParams(window.location.search).get('token');
            if (token) {
                window.location.href = '{{ route("shipping") }}?token=' + token;
            } else {
                window.location.href = '{{ route("shipping") }}';
            }
        }

        async function proceedToCheckout() {
            const form = document.getElementById('checkoutForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            checkoutData = {
                email: document.getElementById('checkoutEmail').value,
                shipping_name: document.getElementById('checkoutName').value,
                shipping_phone: document.getElementById('checkoutPhone').value,
                shipping_address: document.getElementById('checkoutAddress').value,
                shipping_city: document.getElementById('checkoutCity').value,
                shipping_postal_code: document.getElementById('checkoutPostalCode').value,
                shipping_country: document.getElementById('checkoutCountry').value,
            };

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const response = await fetch('/orders/verify-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(checkoutData)
                });

                const data = await response.json();
                
                if (data.success) {
                    if (data.account_exists) {
                        // Compte existe - afficher modal pour mot de passe
                        pendingUserId = data.user_id;
                        const authModal = new bootstrap.Modal(document.getElementById('authModal'));
                        authModal.show();
                    } else {
                        // Compte n'existe pas - continuer vers shipping
                        proceedToShippingWithData();
                    }
                } else {
                    showNotification('error', data.message || 'Erreur lors de la vérification');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('error', 'Erreur de connexion');
            }
        }

        async function authenticateForOrder() {
            const password = document.getElementById('authPassword').value;
            if (!password) {
                document.getElementById('authPassword').classList.add('is-invalid');
                document.getElementById('authPasswordError').textContent = 'Le mot de passe est requis';
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const response = await fetch('/orders/authenticate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        email: checkoutData.email,
                        password: password
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Fermer le modal et continuer
                    const authModal = bootstrap.Modal.getInstance(document.getElementById('authModal'));
                    authModal.hide();
                    
                    // Recharger la page pour mettre à jour les données utilisateur
                    window.location.reload();
                } else {
                    document.getElementById('authPassword').classList.add('is-invalid');
                    document.getElementById('authPasswordError').textContent = data.message || 'Mot de passe incorrect';
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('error', 'Erreur de connexion');
            }
        }

        function proceedToShippingWithData() {
            // Stocker les données dans la session et rediriger
            window.location.href = '{{ route("shipping") }}';
        }

        async function setupPassword() {
            const password = document.getElementById('setupPassword').value;
            const passwordConfirmation = document.getElementById('setupPasswordConfirmation').value;

            if (!password || password.length < 8) {
                document.getElementById('setupPassword').classList.add('is-invalid');
                document.getElementById('setupPasswordError').textContent = 'Le mot de passe doit contenir au moins 8 caractères';
                return;
            }

            if (password !== passwordConfirmation) {
                document.getElementById('setupPasswordConfirmation').classList.add('is-invalid');
                document.getElementById('setupPasswordConfirmationError').textContent = 'Les mots de passe ne correspondent pas';
                return;
            }

            const pendingPasswordSetup = @json(session('pending_password_setup'));
            if (!pendingPasswordSetup || !pendingPasswordSetup.user_id) {
                showNotification('error', 'Session expirée');
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                const response = await fetch('/orders/setup-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        user_id: pendingPasswordSetup.user_id,
                        password: password,
                        password_confirmation: passwordConfirmation
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    showNotification('success', data.message);
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route("accueil") }}';
                    }, 1500);
                } else {
                    showNotification('error', data.message || 'Erreur lors de la définition du mot de passe');
                    if (data.errors) {
                        if (data.errors.password) {
                            document.getElementById('setupPassword').classList.add('is-invalid');
                            document.getElementById('setupPasswordError').textContent = data.errors.password[0];
                        }
                        if (data.errors.password_confirmation) {
                            document.getElementById('setupPasswordConfirmation').classList.add('is-invalid');
                            document.getElementById('setupPasswordConfirmationError').textContent = data.errors.password_confirmation[0];
                        }
                    }
                }
            } catch (error) {
                console.error('Erreur:', error);
                showNotification('error', 'Erreur de connexion');
            }
        }

        // Afficher le modal de définition de mot de passe si nécessaire
        @if(request('setup_password') == '1' && session('pending_password_setup'))
            document.addEventListener('DOMContentLoaded', function() {
                const passwordSetupModal = new bootstrap.Modal(document.getElementById('passwordSetupModal'));
                passwordSetupModal.show();
            });
        @endif
    </script>
@endsection

