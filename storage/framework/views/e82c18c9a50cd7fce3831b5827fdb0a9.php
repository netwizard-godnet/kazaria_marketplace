<?php $__env->startSection('content'); ?>
    <main class="container-fluid bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Message de succès -->
                    <div class="alert alert-success text-center mb-4">
                        <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                        <h4 class="mt-3">Commande validée avec succès !</h4>
                        <p class="mb-0">Numéro de commande: <strong><?php echo e($order->order_number); ?></strong></p>
                        <p class="small text-muted">Un email de confirmation vous a été envoyé à <?php echo e($order->shipping_email); ?></p>
                    </div>

                    <!-- Facture -->
                    <div class="card shadow" id="invoice">
                        <div class="card-body p-5">
                            <!-- En-tête -->
                            <div class="row mb-4">
                                <div class="col-6">
                                    <img src="<?php echo e(asset('images/logo.png')); ?>" alt="KAZARIA" height="50">
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
                                        <strong>N°:</strong> <?php echo e($order->order_number); ?><br>
                                        <strong>Date:</strong> <?php echo e($order->created_at->format('d/m/Y')); ?><br>
                                        <strong>Statut:</strong> <span class="badge <?php echo e($order->status_badge_class); ?>"><?php echo e($order->status_label); ?></span>
                                    </p>
                                </div>
                            </div>

                            <hr>

                            <!-- Informations client -->
                            <div class="row mb-4">
                                <div class="col-6">
                                    <h6 class="text-uppercase fw-bold mb-3">Informations client</h6>
                                    <p class="mb-0">
                                        <strong><?php echo e($order->shipping_name); ?></strong><br>
                                        <?php echo e($order->shipping_email); ?><br>
                                        <?php echo e($order->shipping_phone); ?>

                                    </p>
                                </div>
                                <div class="col-6">
                                    <h6 class="text-uppercase fw-bold mb-3">Adresse de livraison</h6>
                                    <p class="mb-0">
                                        <?php echo e($order->shipping_address); ?><br>
                                        <?php echo e($order->shipping_city); ?>

                                        <?php if($order->shipping_postal_code): ?>, <?php echo e($order->shipping_postal_code); ?><?php endif; ?><br>
                                        <?php echo e($order->shipping_country == 'CI' ? 'Côte d\'Ivoire' : $order->shipping_country); ?>

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
                                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo e(str_starts_with($item->product_image, 'http') ? $item->product_image : asset($item->product_image)); ?>" 
                                                         alt="<?php echo e($item->product_name); ?>" 
                                                         style="width: 50px; height: 50px; object-fit: contain;" 
                                                         class="me-2">
                                                    <div>
                                                    <span><?php echo e($item->product_name); ?></span>
                                                        <?php if($item->attributes && (is_array($item->attributes) || is_object($item->attributes)) && count((array)$item->attributes) > 0): ?>
                                                            <div class="mt-1">
                                                                <?php $__currentLoopData = $item->attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attrName => $attrValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                                            <td class="text-center"><?php echo e($item->quantity); ?></td>
                                            <td class="text-end"><?php echo e(number_format($item->price, 0, ',', ' ')); ?> FCFA</td>
                                            <td class="text-end fw-bold"><?php echo e(number_format($item->total, 0, ',', ' ')); ?> FCFA</td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-end">Sous-total:</th>
                                            <th class="text-end"><?php echo e(number_format($order->subtotal, 0, ',', ' ')); ?> FCFA</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-end">Livraison:</th>
                                            <th class="text-end text-success"><?php echo e($order->shipping_cost == 0 ? 'Gratuite' : number_format($order->shipping_cost, 0, ',', ' ') . ' FCFA'); ?></th>
                                        </tr>
                                        <?php if($order->discount > 0): ?>
                                        <tr>
                                            <th colspan="3" class="text-end">Réduction:</th>
                                            <th class="text-end text-success">-<?php echo e(number_format($order->discount, 0, ',', ' ')); ?> FCFA</th>
                                        </tr>
                                        <?php endif; ?>
                                        <tr class="table-light">
                                            <th colspan="3" class="text-end fs-5">TOTAL:</th>
                                            <th class="text-end fs-4 orange-color"><?php echo e(number_format($order->total, 0, ',', ' ')); ?> FCFA</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Informations de paiement -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <strong><i class="bi bi-info-circle me-2"></i>Mode de paiement:</strong>
                                        <?php if($order->payment_method == 'card'): ?>
                                            Carte bancaire
                                        <?php elseif($order->payment_method == 'mobile_money'): ?>
                                            Mobile Money
                                        <?php else: ?>
                                            Paiement à la livraison
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if($order->customer_notes): ?>
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="fw-bold">Notes:</h6>
                                    <p class="text-muted"><?php echo e($order->customer_notes); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Actions -->
                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <a href="<?php echo e(route('order-download', $order->order_number)); ?>" class="btn orange-bg text-white me-2">
                                        <i class="bi bi-download me-2"></i>Télécharger la facture (PDF)
                                    </a>
                                    <a href="<?php echo e(route('profil')); ?>?token=<?php echo e(request('token')); ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-list-ul me-2"></i>Voir mes commandes
                                    </a>
                                    <a href="<?php echo e(route('accueil')); ?>" class="btn btn-outline-secondary btn-sm">
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
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\invoice.blade.php ENDPATH**/ ?>