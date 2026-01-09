

<?php $__env->startSection('content'); ?>
    <main class="container-fluid">
        <!-- BREADCRUMB -->
        <div class="container py-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('accueil')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo e(route('product-cart')); ?>">Panier</a></li>
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
                            <h5 class="mb-0"><i class="bi bi-cart3 me-2"></i>Articles (<?php echo e($cartItems->count()); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="row align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-2">
                                    <img src="<?php echo e(str_starts_with($item->product->image, 'http') ? $item->product->image : asset($item->product->image)); ?>" 
                                         class="img-fluid rounded" alt="<?php echo e($item->product->name); ?>">
                                </div>
                                <div class="col-6">
                                    <h6 class="mb-1"><?php echo e($item->product->name); ?></h6>
                                    <p class="text-muted small mb-0">Quantité: <?php echo e($item->quantity); ?></p>
                                    <?php if($item->attributes && (is_array($item->attributes) || is_object($item->attributes)) && count((array)$item->attributes) > 0): ?>
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
                                <div class="col-4 text-end">
                                    <p class="mb-0 fw-bold"><?php echo e(number_format($item->price * $item->quantity, 0, ',', ' ')); ?> FCFA</p>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="bi bi-person me-2"></i>Informations de livraison</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Nom:</strong> <?php echo e($user->prenoms); ?> <?php echo e($user->nom); ?></p>
                            <p><strong>Email:</strong> <?php echo e($user->email); ?></p>
                            <p><strong>Téléphone:</strong> <?php echo e($user->telephone); ?></p>
                            <p><strong>Adresse:</strong> <?php echo e($user->adresse ?? 'Non renseignée'); ?></p>
                            
                            <a href="<?php echo e(route('shipping')); ?>?token=<?php echo e(request('token')); ?>" class="btn btn-outline-danger btn-sm mt-2">
                                <i class="bi bi-pencil me-1"></i>Modifier les informations de livraison
                            </a>
                        </div>
                    </div>
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
                                <span class="fw-bold"><?php echo e(number_format($subtotal ?? $total, 0, ',', ' ')); ?> FCFA</span>
                            </div>
                            <?php if(($discount ?? 0) > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Réduction <?php if(($promo['code'] ?? null)): ?><small class="text-muted">(<?php echo e($promo['code']); ?>)</small><?php endif; ?>:</span>
                                <span class="text-success">- <?php echo e(number_format($discount, 0, ',', ' ')); ?> FCFA</span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Livraison:</span>
                                <?php if(($shippingCost ?? 0) > 0): ?>
                                    <span class="fw-bold"><?php echo e(number_format($shippingCost, 0, ',', ' ')); ?> FCFA</span>
                                <?php else: ?>
                                    <span class="text-success fw-bold">Gratuite</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if(isset($freeThreshold) && $freeThreshold > 0 && $subtotal < $freeThreshold): ?>
                            <div class="small text-muted mb-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Livraison gratuite à partir de <?php echo e(number_format($freeThreshold, 0, ',', ' ')); ?> FCFA
                                (<?php echo e(number_format($freeThreshold - $subtotal, 0, ',', ' ')); ?> FCFA restants)
                            </div>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fw-bold fs-5">Total:</span>
                                <span class="fw-bold fs-4 orange-color"><?php echo e(number_format($total, 0, ',', ' ')); ?> FCFA</span>
                            </div>

                            <button class="btn orange-bg text-white w-100 mb-2" onclick="proceedToShipping()">
                                <i class="bi bi-arrow-right me-2"></i>Continuer vers la livraison
                            </button>
                            
                            <a href="<?php echo e(route('product-cart')); ?>" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-arrow-left me-2"></i>Retour au panier
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        function proceedToShipping() {
            const token = new URLSearchParams(window.location.search).get('token');
            if (token) {
                window.location.href = '<?php echo e(route("shipping")); ?>?token=' + token;
            } else {
                window.location.href = '<?php echo e(route("shipping")); ?>';
            }
        }
    </script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\checkout.blade.php ENDPATH**/ ?>