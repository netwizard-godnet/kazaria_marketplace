

<?php $__env->startSection('content'); ?>
<main class="container-fluid">
    <!-- SECTION BREADCRUMB -->
    <section class="bg-light py-2">
        <div class="container-fluid">
            <nav style="--bs-breadcrumb-divider: '|';" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('accueil')); ?>" class="fs-7">Accueil</a></li>
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
        // Appeler l'API de recherche
        const response = await fetch('<?php echo e(route("api.track-order")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                order_number: formData.get('order_number'),
                email: formData.get('email')
            })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Commande non trouvée');
        }
        
        const order = data.order;
        
        // Obtenir la classe de badge selon le statut
        const getStatusBadgeClass = (statusCode) => {
            const statusClasses = {
                'pending': 'bg-secondary',
                'processing': 'bg-info',
                'shipped': 'bg-warning',
                'delivered': 'bg-success',
                'cancelled': 'bg-danger',
                'refunded': 'bg-dark'
            };
            return statusClasses[statusCode] || 'bg-secondary';
        };
        
        // Afficher le résultat
        orderContent.innerHTML = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Informations de commande</h6>
                    <p class="mb-2"><strong>Numéro :</strong> ${order.number}</p>
                    <p class="mb-2"><strong>Date :</strong> ${order.date}</p>
                    <p class="mb-2"><strong>Sous-total :</strong> <span class="fw-bold">${order.subtotal} FCFA</span></p>
                    ${order.shipping_cost > 0 ? `<p class="mb-2"><strong>Livraison :</strong> ${order.shipping_cost} FCFA</p>` : ''}
                    ${order.discount > 0 ? `<p class="mb-2"><strong>Remise :</strong> <span class="text-success">-${order.discount} FCFA</span></p>` : ''}
                    <p class="mb-2"><strong>Total :</strong> <span class="orange-color fw-bold fs-5">${order.total} FCFA</span></p>
                    <p class="mb-2"><strong>Statut :</strong> <span class="badge ${getStatusBadgeClass(order.status_code)}">${order.status}</span></p>
                    <p class="mb-2"><strong>Paiement :</strong> <span class="badge ${order.payment_status === 'paid' ? 'bg-success' : 'bg-warning'}">${order.payment_status === 'paid' ? 'Payé' : 'En attente'}</span></p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold mb-3">Adresse de livraison</h6>
                    <p class="mb-1"><strong>${order.shipping_name}</strong></p>
                    <p class="mb-1">${order.shipping_address}</p>
                    <p class="mb-1">${order.shipping_city}</p>
                    <p class="mb-1"><i class="bi bi-telephone me-1"></i>${order.shipping_phone}</p>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold mb-3">Articles commandés</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th class="text-end">Prix unitaire</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${order.items.map(item => `
                                    <tr>
                                        <td>
                                            ${item.image ? `<img src="${item.image}" alt="${item.name}" class="me-2" style="width: 50px; height: 50px; object-fit: cover;">` : ''}
                                            <div>
                                                <div class="fw-bold">${item.name}</div>
                                                ${item.attributes && Object.keys(item.attributes).length > 0 ? `
                                                    <div class="mt-1">
                                                        ${Object.entries(item.attributes).map(([attrName, attrValue]) => `
                                                            <small class="text-muted d-block">
                                                                <strong>${attrName.charAt(0).toUpperCase() + attrName.slice(1)}:</strong>
                                                                <span class="text-primary">
                                                                    ${Array.isArray(attrValue) ? attrValue.join(', ') : attrValue}
                                                                </span>
                                                            </small>
                                                        `).join('')}
                                                    </div>
                                                ` : ''}
                                            </div>
                                        </td>
                                        <td>${item.quantity}</td>
                                        <td class="text-end">${item.price} FCFA</td>
                                        <td class="text-end fw-bold">${item.total} FCFA</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <h6 class="fw-bold mb-3">Historique de suivi</h6>
            <div class="timeline">
                ${order.tracking.map((step, index) => `
                    <div class="d-flex align-items-start mb-3">
                        <div class="me-3">
                            <div class="bg-${step.active ? 'warning' : 'success'} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; flex-shrink: 0;">
                                <i class="bi bi-${step.active ? 'clock' : 'check'}-circle"></i>
                            </div>
                            ${index < order.tracking.length - 1 ? '<div class="bg-light mx-auto" style="width: 2px; height: 40px; margin-top: 5px;"></div>' : ''}
                        </div>
                        <div class="flex-grow-1">
                            <strong class="d-block">${step.status}</strong>
                            <small class="text-muted">${step.date} à ${step.time} - ${step.location}</small>
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
                <strong>${error.message || 'Erreur lors de la recherche'}</strong><br>
                Vérifiez votre numéro de commande et email, ou contactez notre support.
            </div>
        `;
        orderDetails.style.display = 'none';
    } finally {
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/suivre-commande.blade.php ENDPATH**/ ?>