<?php $__env->startSection('title', 'Détails de la commande'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Détails de la commande #<?php echo e($order->order_number ?? 'N/A'); ?></h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?php echo e(route('admin.dashboard')); ?>">
                    <i class="flaticon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.orders.index')); ?>">Commandes</a>
            </li>
            <li class="separator">
                <i class="flaticon-right-arrow"></i>
            </li>
            <li class="nav-item">
                <span><?php echo e($order->order_number); ?></span>
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
                            <p><strong>Nom:</strong> <?php echo e($order->user->prenoms ?? 'N/A'); ?> <?php echo e($order->user->nom ?? ''); ?></p>
                            <p><strong>Email:</strong> <?php echo e($order->user->email ?? 'N/A'); ?></p>
                            <p><strong>Téléphone:</strong> <?php echo e($order->user->telephone ?? $order->shipping_phone ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Statuts</h6>
                            <p><strong>Statut commande:</strong> 
                                <span class="badge bg-<?php echo e($order->status_class); ?>"><?php echo e($order->status_label); ?></span>
                            </p>
                            <p><strong>Statut paiement:</strong> 
                                <span class="badge bg-<?php echo e($order->payment_status_class); ?>"><?php echo e($order->payment_status_label); ?></span>
                            </p>
                            <p><strong>Méthode de paiement:</strong> 
                                <?php if($order->payment_method == 'card'): ?>
                                    Carte bancaire
                                <?php elseif($order->payment_method == 'mobile_money'): ?>
                                    Mobile Money
                                <?php elseif($order->payment_method == 'cash_on_delivery'): ?>
                                    Paiement à la livraison
                                <?php else: ?>
                                    <?php echo e(ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A'))); ?>

                                <?php endif; ?>
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
                                <?php $__currentLoopData = $order->orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($item->product_image): ?>
                                                <img src="<?php echo e(str_starts_with($item->product_image, 'http') ? $item->product_image : (str_starts_with($item->product_image, 'storage/') ? asset($item->product_image) : asset('storage/' . $item->product_image))); ?>" 
                                                     alt="<?php echo e($item->product_name); ?>" 
                                                     class="me-3" 
                                                     style="width: 50px; height: 50px; object-fit: cover;"
                                                     onerror="this.src='<?php echo e(asset('images/produit.jpg')); ?>'">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo e($item->product_name); ?></strong>
                                                <?php
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
                                                ?>
                                                <?php if($item->variation && $item->variation->attributeValues && $item->variation->attributeValues->count() > 0): ?>
                                                    
                                                    <?php
                                                        $groupedAttributes = $item->variation->attributeValues->groupBy('attribute.name');
                                                    ?>
                                                    <div class="mt-1">
                                                        <?php $__currentLoopData = $groupedAttributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attrName => $values): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <small class="text-muted d-block">
                                                                <strong><?php echo e($attrName); ?>:</strong>
                                                                <span class="text-primary">
                                                                    <?php echo e($values->pluck('value')->implode(', ')); ?>

                                                                </span>
                                                            </small>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                <?php elseif($hasAttributes): ?>
                                                    
                                                    <div class="mt-1">
                                                        <?php $__currentLoopData = $attrsArray; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attrName => $attrValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <small class="text-muted d-block">
                                                                <strong><?php echo e(ucfirst($attrName)); ?>:</strong>
                                                                <span class="text-primary">
                                                                    <?php echo e(is_array($attrValue) ? implode(', ', $attrValue) : $attrValue); ?>

                                                                </span>
                                                            </small>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if($item->product && $item->product->store): ?>
                                            <span class="badge bg-info"><?php echo e($item->product->store->name); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e(number_format($item->price, 0, ',', ' ')); ?> FCFA</td>
                                    <td><?php echo e($item->quantity); ?></td>
                                    <td><strong><?php echo e(number_format($item->total, 0, ',', ' ')); ?> FCFA</strong></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                    <p><strong>Nom:</strong> <?php echo e($order->shipping_name); ?></p>
                    <p><strong>Email:</strong> <?php echo e($order->shipping_email); ?></p>
                    <p><strong>Téléphone:</strong> <?php echo e($order->shipping_phone); ?></p>
                    <p><strong>Adresse:</strong> <?php echo e($order->shipping_address); ?></p>
                    <p><strong>Ville:</strong> <?php echo e($order->shipping_city); ?></p>
                    <?php if($order->shipping_postal_code): ?>
                        <p><strong>Code postal:</strong> <?php echo e($order->shipping_postal_code); ?></p>
                    <?php endif; ?>
                    <p><strong>Pays:</strong> <?php echo e($order->shipping_country); ?></p>
                </div>
            </div>

            <?php if($order->customer_notes): ?>
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Notes du client</h4>
                </div>
                <div class="card-body">
                    <p><?php echo e($order->customer_notes); ?></p>
                </div>
            </div>
            <?php endif; ?>
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
                        <span><?php echo e(number_format($order->subtotal, 0, ',', ' ')); ?> FCFA</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Frais de livraison:</span>
                        <span><?php echo e(number_format($order->shipping_cost, 0, ',', ' ')); ?> FCFA</span>
                    </div>
                    <?php if($order->tax > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Taxes:</span>
                        <span><?php echo e(number_format($order->tax, 0, ',', ' ')); ?> FCFA</span>
                    </div>
                    <?php endif; ?>
                    <?php if($order->discount > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Remise:</span>
                        <span class="text-success">-<?php echo e(number_format($order->discount, 0, ',', ' ')); ?> FCFA</span>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="text-primary"><?php echo e(number_format($order->total, 0, ',', ' ')); ?> FCFA</strong>
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
                        <?php if($order->status === 'pending'): ?>
                            <button class="btn btn-warning" onclick="updateOrderStatus('processing')">
                                <i class="fas fa-play me-2"></i>Marquer en cours de livraison
                            </button>
                            <button class="btn btn-danger" onclick="updateOrderStatus('cancelled')">
                                <i class="fas fa-times me-2"></i>Annuler la commande
                            </button>
                        <?php elseif($order->status === 'processing'): ?>
                            <button class="btn btn-success" onclick="updateOrderStatus('delivered')">
                                <i class="fas fa-check me-2"></i>Marquer comme livrée
                            </button>
                            <button class="btn btn-danger" onclick="updateOrderStatus('cancelled')">
                                <i class="fas fa-times me-2"></i>Annuler la commande
                            </button>
                        <?php elseif($order->status === 'delivered'): ?>
                            <button class="btn btn-info" onclick="updateOrderStatus('processing')">
                                <i class="fas fa-undo me-2"></i>Retour en cours de livraison
                            </button>
                            <button class="btn btn-danger" onclick="updateOrderStatus('cancelled')">
                                <i class="fas fa-times me-2"></i>Annuler la commande
                            </button>
                        <?php elseif($order->status === 'cancelled'): ?>
                            <button class="btn btn-primary" onclick="updateOrderStatus('pending')">
                                <i class="fas fa-redo me-2"></i>Réactiver la commande
                            </button>
                            <button class="btn btn-warning" onclick="updateOrderStatus('processing')">
                                <i class="fas fa-play me-2"></i>Marquer en cours de livraison
                            </button>
                        <?php endif; ?>

                        <!-- Actions de paiement -->
                        <?php if($order->payment_status === 'pending'): ?>
                            <button class="btn btn-success" onclick="updatePaymentStatus('paid')">
                                <i class="fas fa-check-circle me-2"></i>Marquer comme payé
                            </button>
                        <?php else: ?>
                            <button class="btn btn-warning" onclick="updatePaymentStatus('pending')">
                                <i class="fas fa-clock me-2"></i>Paiement en attente
                            </button>
                        <?php endif; ?>

                        <!-- Actions spéciales -->
                        <button class="btn btn-info" onclick="printOrder()">
                            <i class="fas fa-print me-2"></i>Imprimer la commande
                        </button>
                        
                        <?php if($order->status === 'cancelled'): ?>
                            <button class="btn btn-danger" onclick="deleteOrder()">
                                <i class="fas fa-trash me-2"></i>Supprimer la commande
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Informations système -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Informations système</h4>
                </div>
                <div class="card-body">
                    <p><strong>Date de création:</strong> <?php echo e($order->created_at->format('d/m/Y H:i')); ?></p>
                    <p><strong>Dernière modification:</strong> <?php echo e($order->updated_at->format('d/m/Y H:i')); ?></p>
                    <?php if($order->paid_at): ?>
                        <p><strong>Date de paiement:</strong> <?php echo e($order->paid_at->format('d/m/Y H:i')); ?></p>
                    <?php endif; ?>
                    <?php if($order->shipped_at): ?>
                        <p><strong>Date d'expédition:</strong> <?php echo e($order->shipped_at->format('d/m/Y H:i')); ?></p>
                    <?php endif; ?>
                    <?php if($order->delivered_at): ?>
                        <p><strong>Date de livraison:</strong> <?php echo e($order->delivered_at->format('d/m/Y H:i')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function updateOrderStatus(status) {
    if (!confirm('Êtes-vous sûr de vouloir changer le statut de cette commande ?')) {
        return;
    }
    
    fetch(`/admin/orders/<?php echo e($order->id); ?>/status`, {
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
    
    fetch(`/admin/orders/<?php echo e($order->id); ?>/payment-status`, {
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
    const invoiceUrl = '<?php echo e(route("admin.orders.invoice", $order->id)); ?>';
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
    
    fetch(`/admin/orders/<?php echo e($order->id); ?>`, {
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
<?php $__env->stopPush(); ?>
<?php echo $__env->make('admin.layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/admin/orders/show.blade.php ENDPATH**/ ?>