<?php $__env->startSection('content'); ?>
    <main class="container-fluid bg-light py-4">
        <div class="container">
            <!-- BREADCRUMB -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('accueil')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo e(route('profil')); ?>?token=<?php echo e(request('token')); ?>">Mon Profil</a></li>
                    <li class="breadcrumb-item active">Commande <?php echo e($order->order_number); ?></li>
                </ol>
            </nav>

            <div class="row">
                <!-- Informations de la commande -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header orange-bg text-white">
                            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Commande <?php echo e($order->order_number); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Date de commande:</strong> <?php echo e($order->created_at->format('d/m/Y à H:i')); ?></p>
                                    <p><strong>Statut:</strong> <span class="badge <?php echo e($order->status_badge_class); ?>"><?php echo e($order->status_label); ?></span></p>
                                    <p><strong>Paiement:</strong> 
                                        <?php if($order->payment_method == 'card'): ?>
                                            Carte bancaire
                                        <?php elseif($order->payment_method == 'mobile_money'): ?>
                                            Mobile Money
                                        <?php else: ?>
                                            Paiement à la livraison
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total:</strong> <span class="orange-color fs-4"><?php echo e(number_format($order->total, 0, ',', ' ')); ?> FCFA</span></p>
                                </div>
                            </div>

                            <!-- Timeline de suivi -->
                            <h6 class="fw-bold mb-3">Suivi de la commande</h6>
                            <div class="timeline">
                                <div class="timeline-item <?php echo e(in_array($order->status, ['pending', 'paid', 'processing', 'shipped', 'delivered']) ? 'completed' : ''); ?>">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Commande passée</h6>
                                        <p class="text-muted small"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item <?php echo e(in_array($order->status, ['paid', 'processing', 'shipped', 'delivered']) ? 'completed' : ''); ?>">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Paiement confirmé</h6>
                                        <p class="text-muted small"><?php echo e($order->paid_at ? $order->paid_at->format('d/m/Y H:i') : 'En attente'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item <?php echo e(in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'completed' : ''); ?>">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>En préparation</h6>
                                        <p class="text-muted small"><?php echo e(in_array($order->status, ['processing', 'shipped', 'delivered']) ? 'En cours' : 'En attente'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item <?php echo e(in_array($order->status, ['shipped', 'delivered']) ? 'completed' : ''); ?>">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Expédiée</h6>
                                        <p class="text-muted small"><?php echo e($order->shipped_at ? $order->shipped_at->format('d/m/Y H:i') : 'En attente'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item <?php echo e($order->status == 'delivered' ? 'completed' : ''); ?>">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6>Livrée</h6>
                                        <p class="text-muted small"><?php echo e($order->delivered_at ? $order->delivered_at->format('d/m/Y H:i') : 'En attente'); ?></p>
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
                            <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Articles (<?php echo e($order->items->count()); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="row align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-2">
                                    <img src="<?php echo e(str_starts_with($item->product_image, 'http') ? $item->product_image : asset($item->product_image)); ?>" 
                                         class="img-fluid rounded" alt="<?php echo e($item->product_name); ?>">
                                </div>
                                <div class="col-5">
                                    <h6 class="mb-1"><?php echo e($item->product_name); ?></h6>
                                    <p class="text-muted small mb-0">Quantité: <?php echo e($item->quantity); ?></p>
                                    <?php if($item->variation && $item->variation->attributeValues && $item->variation->attributeValues->count() > 0): ?>
                                        
                                        <?php
                                            $groupedAttributes = $item->variation->attributeValues->groupBy('attribute.name');
                                        ?>
                                        <div class="mt-2">
                                            <?php $__currentLoopData = $groupedAttributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attrName => $values): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="mb-1">
                                                    <small class="text-muted fw-bold"><?php echo e($attrName); ?>:</small>
                                                    <small class="text-primary">
                                                        <?php echo e($values->pluck('value')->implode(', ')); ?>

                                                    </small>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php elseif($item->attributes && count($item->attributes) > 0): ?>
                                        
                                        <div class="mt-2">
                                            <?php $__currentLoopData = $item->attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attrName => $attrValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="mb-1">
                                                    <small class="text-muted fw-bold"><?php echo e(ucfirst($attrName)); ?>:</small>
                                                    <small class="text-primary">
                                                        <?php echo e(is_array($attrValue) ? implode(', ', $attrValue) : $attrValue); ?>

                                                    </small>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-2 text-center">
                                    <p class="mb-0"><?php echo e(number_format($item->price, 0, ',', ' ')); ?> FCFA</p>
                                </div>
                                <div class="col-3 text-end">
                                    <p class="mb-0 fw-bold"><?php echo e(number_format($item->total, 0, ',', ' ')); ?> FCFA</p>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                                <span><?php echo e(number_format($order->subtotal, 0, ',', ' ')); ?> FCFA</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Livraison:</span>
                                <span class="text-success"><?php echo e($order->shipping_cost == 0 ? 'Gratuite' : number_format($order->shipping_cost, 0, ',', ' ') . ' FCFA'); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold orange-color"><?php echo e(number_format($order->total, 0, ',', ' ')); ?> FCFA</span>
                            </div>
                        </div>
                    </div>

                    <!-- Livraison -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-truck me-2"></i>Livraison</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong><?php echo e($order->shipping_name); ?></strong></p>
                            <p class="mb-1"><?php echo e($order->shipping_phone); ?></p>
                            <p class="mb-0 small">
                                <?php echo e($order->shipping_address); ?><br>
                                <?php echo e($order->shipping_city); ?>

                                <?php if($order->shipping_postal_code): ?>, <?php echo e($order->shipping_postal_code); ?><?php endif; ?><br>
                                <?php echo e($order->shipping_country == 'CI' ? 'Côte d\'Ivoire' : $order->shipping_country); ?>

                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card">
                        <div class="card-body">
                            <a href="<?php echo e(route('order-download', $order->order_number)); ?>" class="btn orange-bg text-white w-100 mb-2">
                                <i class="bi bi-download me-2"></i>Télécharger la facture
                            </a>
                            <a href="<?php echo e(route('profil')); ?>?token=<?php echo e(request('token')); ?>" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-arrow-left me-2"></i>Retour à mes commandes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/israa/Desktop/kazaria_marketplace/resources/views/order-details.blade.php ENDPATH**/ ?>