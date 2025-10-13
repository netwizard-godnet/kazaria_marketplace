@extends('layouts.app')

@section('content')
<main class="container-fluid">
    <!-- SECTION BREADCRUMB -->
    <section class="bg-light py-2">
        <div class="container-fluid">
            <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('accueil') }}" class="fs-7">Accueil</a></li>
                    <li class="breadcrumb-item active fs-7" aria-current="page">Suivre sa commande</li>
                </ol>
            </nav>
        </div>
    </section>
    <!-- SECTION BREADCRUMB END -->

    <!-- SECTION SUIVRE COMMANDE -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h3 class="fw-bold mb-3">Suivre sa commande</h3>
                    <p class="text-muted">Suivez l'état de votre commande en temps réel</p>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Formulaire de suivi -->
                    <div class="bg-white p-4 rounded shadow-sm mb-5">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-search me-2 orange-color"></i>
                            Rechercher votre commande
                        </h5>
                        
                        <form id="trackOrderForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="order_number" class="form-label">Numéro de commande *</label>
                                    <input type="text" class="form-control" id="order_number" name="order_number" 
                                           placeholder="Ex: KZ2025001" required>
                                    <small class="text-muted">Le numéro de commande commence par "KZ"</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email de commande *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="votre@email.com" required>
                                    <small class="text-muted">L'email utilisé lors de la commande</small>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-sm orange-bg text-white px-4">
                                        <i class="bi bi-search me-2"></i>Suivre ma commande
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Messages de statut -->
                        <div id="trackStatus" class="mt-3" style="display: none;"></div>
                    </div>

                    <!-- Résultat du suivi -->
                    <div id="orderDetails" class="bg-light p-4 rounded" style="display: none;">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-box-seam me-2 orange-color"></i>
                            Détails de la commande
                        </h5>
                        <div id="orderContent"></div>
                    </div>

                    <!-- Informations utiles -->
                    <div class="row mt-5">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="bi bi-clock blue-color fs-1 mb-3"></i>
                                <h6 class="fw-bold">Délais de livraison</h6>
                                <p class="text-muted small">3-5 jours ouvrés en standard</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="bi bi-geo-alt orange-color fs-1 mb-3"></i>
                                <h6 class="fw-bold">Suivi en temps réel</h6>
                                <p class="text-muted small">Mise à jour automatique</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <i class="bi bi-headset blue-color fs-1 mb-3"></i>
                                <h6 class="fw-bold">Besoin d'aide ?</h6>
                                <p class="text-muted small">Contactez notre support</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- SECTION SUIVRE COMMANDE END -->
</main>

<!-- JavaScript pour le suivi -->
<script>
document.getElementById('trackOrderForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const statusDiv = document.getElementById('trackStatus');
    const orderDetails = document.getElementById('orderDetails');
    const orderContent = document.getElementById('orderContent');
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    // Afficher un message de chargement
    statusDiv.innerHTML = `
        <div class="alert alert-info">
            <i class="bi bi-hourglass-split me-2"></i>
            Recherche en cours...
        </div>
    `;
    statusDiv.style.display = 'block';
    orderDetails.style.display = 'none';
    
    // Désactiver le bouton
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Recherche...';
    
    try {
        // Simuler une recherche (à remplacer par une vraie API)
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        const orderNumber = formData.get('order_number');
        const email = formData.get('email');
        
        // Simuler un résultat (à remplacer par une vraie API)
        const mockOrder = {
            number: orderNumber,
            email: email,
            status: 'En cours de livraison',
            date: '2025-01-15',
            total: '125000',
            items: [
                { name: 'iPhone 15 Pro', quantity: 1, price: '125000' }
            ],
            tracking: [
                { date: '2025-01-15', status: 'Commande confirmée', location: 'Abidjan' },
                { date: '2025-01-16', status: 'Préparation en cours', location: 'Entrepôt KAZARIA' },
                { date: '2025-01-17', status: 'En cours de livraison', location: 'En route' }
            ]
        };
        
        // Afficher le résultat
        orderContent.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold">Informations de commande</h6>
                    <p><strong>Numéro :</strong> ${mockOrder.number}</p>
                    <p><strong>Date :</strong> ${mockOrder.date}</p>
                    <p><strong>Total :</strong> <span class="orange-color fw-bold">${new Intl.NumberFormat('fr-FR').format(mockOrder.total)} FCFA</span></p>
                    <p><strong>Statut :</strong> <span class="badge bg-warning">${mockOrder.status}</span></p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Articles commandés</h6>
                    ${mockOrder.items.map(item => `
                        <div class="d-flex justify-content-between">
                            <span>${item.name} x${item.quantity}</span>
                            <span class="fw-bold">${new Intl.NumberFormat('fr-FR').format(item.price)} FCFA</span>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <hr>
            
            <h6 class="fw-bold mb-3">Historique de suivi</h6>
            <div class="timeline">
                ${mockOrder.tracking.map((step, index) => `
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <div class="bg-${index === mockOrder.tracking.length - 1 ? 'warning' : 'success'} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                <i class="bi bi-check"></i>
                            </div>
                        </div>
                        <div>
                            <strong>${step.status}</strong><br>
                            <small class="text-muted">${step.date} - ${step.location}</small>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
        
        statusDiv.innerHTML = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Commande trouvée !</strong> Voici les détails de votre commande.
            </div>
        `;
        
        orderDetails.style.display = 'block';
        
        // Masquer le message après 3 secondes
        setTimeout(() => {
            statusDiv.style.display = 'none';
        }, 3000);
        
    } catch (error) {
        console.error('Erreur:', error);
        statusDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Commande non trouvée</strong><br>
                Vérifiez votre numéro de commande et email, ou contactez notre support.
            </div>
        `;
    } finally {
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
});
</script>
@endsection
