<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle commande reçue sur KAZARIA !</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #f04e26 0%, #ff6b47 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .content {
            padding: 30px;
        }
        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .order-info h3 {
            color: #f04e26;
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #f04e26 0%, #ff6b47 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background: linear-gradient(135deg, #e03d1a 0%, #f04e26 100%);
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
        }
        .highlight {
            color: #f04e26;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Nouvelle commande reçue !</h1>
        </div>
        
        <div class="content">
            <p>Bonjour <strong><?php echo e($store->user->name ?? $store->name); ?></strong>,</p>
            
            <p>Vous avez reçu une nouvelle commande pour votre boutique <strong><?php echo e($store->name); ?></strong> sur KAZARIA !</p>
            
            <div class="order-info">
                <h3>📋 Informations de la commande</h3>
                <p><strong>Numéro de commande :</strong> <span class="highlight"><?php echo e($order->order_number); ?></span></p>
                <p><strong>Date de commande :</strong> <?php echo e($order->created_at->format('d/m/Y H:i')); ?></p>
                <p><strong>Client :</strong> <?php echo e($order->shipping_name); ?></p>
                <p><strong>Email :</strong> <?php echo e($order->shipping_email); ?></p>
                <p><strong>Téléphone :</strong> <?php echo e($order->shipping_phone); ?></p>
            </div>
            
            <h3>🛍️ Détails de la commande pour votre boutique :</h3>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($item->product_name); ?></td>
                            <td><?php echo e($item->quantity); ?></td>
                            <td><?php echo e(number_format($item->price, 0, ',', ' ')); ?> FCFA</td>
                            <td><?php echo e(number_format($item->total, 0, ',', ' ')); ?> FCFA</td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right;"><strong>Total pour votre boutique :</strong></td>
                        <td><strong><?php echo e(number_format($storeTotal, 0, ',', ' ')); ?> FCFA</strong></td>
                    </tr>
                </tfoot>
            </table>

            <div class="order-info">
                <h3>💳 Informations de paiement</h3>
                <p><strong>Méthode de paiement :</strong> <?php echo e(ucfirst(str_replace('_', ' ', $order->payment_method))); ?></p>
                <p><strong>Statut du paiement :</strong> 
                    <?php if($order->payment_status === 'paid'): ?>
                        <span style="color: #28a745; font-weight: bold;">✅ Payé</span>
                    <?php else: ?>
                        <span style="color: #ffc107; font-weight: bold;">⏳ En attente</span>
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="order-info">
                <h3>📍 Adresse de livraison</h3>
                <p><?php echo e($order->shipping_address); ?></p>
                <p><?php echo e($order->shipping_city); ?>, <?php echo e($order->shipping_country); ?></p>
                <?php if($order->shipping_postal_code): ?>
                    <p>Code postal : <?php echo e($order->shipping_postal_code); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if($order->customer_notes): ?>
                <div class="order-info">
                    <h3>📝 Notes du client</h3>
                    <p><?php echo e($order->customer_notes); ?></p>
                </div>
            <?php endif; ?>
            
            <p>Vous pouvez consulter les détails complets de cette commande et gérer son statut directement depuis votre tableau de bord vendeur :</p>
            
            <p style="text-align: center;">
                <a href="<?php echo e(route('store.order-details', $order->order_number)); ?>" class="button">
                    🔗 Voir la commande dans mon dashboard
                </a>
            </p>
            
            <p><strong>⚠️ Action requise :</strong> Veuillez préparer la commande et mettre à jour son statut dès que possible.</p>
        </div>
        
        <div class="footer">
            <p>Merci de votre confiance sur <strong>KAZARIA</strong> !</p>
            <p>L'équipe KAZARIA</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\emails\seller\new-order.blade.php ENDPATH**/ ?>