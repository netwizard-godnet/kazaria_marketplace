<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture <?php echo e($order->order_number); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #f04e27;
            padding-bottom: 20px;
        }
        .logo {
            font-size: 28px;
            color: #f04e27;
            font-weight: bold;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        .invoice-details table {
            width: 100%;
        }
        .invoice-details td {
            padding: 5px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table.items th {
            background-color: #f04e27;
            color: white;
            padding: 10px;
            text-align: left;
        }
        table.items td {
            border-bottom: 1px solid #ddd;
            padding: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            font-size: 16px;
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="logo">KAZARIA</div>
        <p style="margin: 5px 0;">E-commerce en Côte d'Ivoire</p>
        <p style="margin: 5px 0;">Email: contact@kazaria.ci | Tél: +225 XX XX XX XX XX</p>
    </div>

    <!-- Détails de la facture -->
    <div class="invoice-details">
        <table>
            <tr>
                <td style="width: 50%;">
                    <h3 style="color: #f04e27; margin-bottom: 10px;">FACTURE</h3>
                    <p>
                        <strong>N°:</strong> <?php echo e($order->order_number); ?><br>
                        <strong>Date:</strong> <?php echo e($order->created_at->format('d/m/Y H:i')); ?><br>
                        <strong>Statut:</strong> <?php echo e($order->status_label); ?>

                    </p>
                </td>
                <td style="width: 50%; vertical-align: top;">
                    <h4 style="margin-bottom: 10px;">Client</h4>
                    <p>
                        <strong><?php echo e($order->shipping_name); ?></strong><br>
                        <?php echo e($order->shipping_email); ?><br>
                        <?php echo e($order->shipping_phone); ?><br>
                        <br>
                        <strong>Adresse de livraison:</strong><br>
                        <?php echo e($order->shipping_address); ?><br>
                        <?php echo e($order->shipping_city); ?>

                        <?php if($order->shipping_postal_code): ?>, <?php echo e($order->shipping_postal_code); ?><?php endif; ?><br>
                        <?php echo e($order->shipping_country == 'CI' ? 'Côte d\'Ivoire' : $order->shipping_country); ?>

                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Articles -->
    <table class="items">
        <thead>
            <tr>
                <th style="width: 50%;">Article</th>
                <th class="text-center" style="width: 15%;">Quantité</th>
                <th class="text-right" style="width: 17.5%;">Prix unitaire</th>
                <th class="text-right" style="width: 17.5%;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <?php echo e($item->product_name); ?>

                    <?php if($item->attributes && (is_array($item->attributes) || is_object($item->attributes)) && count((array)$item->attributes) > 0): ?>
                        <div style="margin-top: 5px; font-size: 11px; color: #666;">
                            <?php $__currentLoopData = $item->attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attrName => $attrValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>
                                    <strong><?php echo e(ucfirst($attrName)); ?>:</strong>
                                    <?php echo e(is_array($attrValue) ? implode(', ', $attrValue) : $attrValue); ?>

                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?php echo e($item->quantity); ?></td>
                <td class="text-right"><?php echo e(number_format($item->price, 0, ',', ' ')); ?> FCFA</td>
                <td class="text-right"><?php echo e(number_format($item->total, 0, ',', ' ')); ?> FCFA</td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>Sous-total:</strong></td>
                <td class="text-right"><?php echo e(number_format($order->subtotal, 0, ',', ' ')); ?> FCFA</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>Livraison:</strong></td>
                <td class="text-right"><?php echo e($order->shipping_cost == 0 ? 'Gratuite' : number_format($order->shipping_cost, 0, ',', ' ') . ' FCFA'); ?></td>
            </tr>
            <?php if($order->discount > 0): ?>
            <tr>
                <td colspan="3" class="text-right"><strong>Réduction:</strong></td>
                <td class="text-right">-<?php echo e(number_format($order->discount, 0, ',', ' ')); ?> FCFA</td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="3" class="text-right"><strong>TOTAL À PAYER:</strong></td>
                <td class="text-right" style="color: #f04e27; font-size: 18px;"><?php echo e(number_format($order->total, 0, ',', ' ')); ?> FCFA</td>
            </tr>
        </tfoot>
    </table>

    <!-- Mode de paiement -->
    <div style="background-color: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #f04e27;">
        <strong>Mode de paiement:</strong>
        <?php if($order->payment_method == 'card'): ?>
            Carte bancaire
        <?php elseif($order->payment_method == 'mobile_money'): ?>
            Mobile Money
        <?php else: ?>
            Paiement à la livraison (en espèces)
        <?php endif; ?>
    </div>

    <?php if($order->customer_notes): ?>
    <div style="background-color: #fff3cd; padding: 15px; margin: 20px 0;">
        <strong>Notes du client:</strong><br>
        <?php echo e($order->customer_notes); ?>

    </div>
    <?php endif; ?>

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>Merci pour votre confiance !</strong><br>
            Pour toute question, contactez-nous à contact@kazaria.ci ou au +225 XX XX XX XX XX<br>
            <br>
            <small>KAZARIA - E-commerce en Côte d'Ivoire - www.kazaria.ci</small>
        </p>
    </div>
</body>
</html>

<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views/invoice-pdf.blade.php ENDPATH**/ ?>