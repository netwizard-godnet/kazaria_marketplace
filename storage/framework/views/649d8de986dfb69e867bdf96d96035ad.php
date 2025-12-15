<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture <?php echo e($order->order_number); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f04e27;
        }
        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }
        .logo {
            font-size: 32px;
            color: #f04e27;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .company-info {
            font-size: 11px;
            color: #666;
            line-height: 1.8;
        }
        .invoice-title {
            font-size: 24px;
            color: #f04e27;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .invoice-info {
            font-size: 11px;
            line-height: 1.8;
        }
        .invoice-details {
            margin-bottom: 25px;
        }
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details td {
            padding: 8px;
            vertical-align: top;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .client-info {
            font-size: 11px;
            line-height: 1.8;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 1px solid #ddd;
        }
        table.items th {
            background-color: #f04e27;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        table.items td {
            border-bottom: 1px solid #e0e0e0;
            padding: 10px 8px;
            font-size: 11px;
        }
        table.items tbody tr:last-child td {
            border-bottom: none;
        }
        table.items tfoot tr {
            background-color: #f8f9fa;
        }
        table.items tfoot td {
            padding: 12px 8px;
            font-weight: bold;
            border-top: 2px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #fff3e0;
            font-size: 13px;
        }
        .total-row td {
            font-size: 14px;
            color: #f04e27;
            font-weight: bold;
        }
        .payment-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #f04e27;
            font-size: 11px;
        }
        .notes-box {
            background-color: #fff3cd;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
            font-size: 11px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .footer strong {
            color: #333;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <div class="header">
        <div class="header-top">
            <div class="header-left">
                <div class="logo"><?php echo e($siteName ?? 'KAZARIA'); ?></div>
                <div class="company-info">
                    <strong>E-commerce en Côte d'Ivoire</strong><br>
                    Email: <?php echo e($siteEmail ?? 'contact@kazaria.ci'); ?><br>
                    Tél: <?php echo e($sitePhone ?? '+225 XX XX XX XX XX'); ?><br>
                    <?php if($siteAddress): ?>
                        <?php echo e($siteAddress); ?>

                    <?php endif; ?>
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">FACTURE</div>
                <div class="invoice-info">
                    <strong>N° Facture:</strong> <?php echo e($order->order_number); ?><br>
                    <strong>Date:</strong> <?php echo e($order->created_at->format('d/m/Y')); ?><br>
                    <strong>Heure:</strong> <?php echo e($order->created_at->format('H:i')); ?><br>
                    <strong>Statut:</strong> <?php echo e($order->status_label ?? ucfirst($order->status)); ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Informations client -->
    <div class="invoice-details">
        <table>
            <tr>
                <td style="width: 50%;">
                    <div class="section-title">Informations client</div>
                    <div class="client-info">
                        <strong><?php echo e($order->shipping_name); ?></strong><br>
                        Email: <?php echo e($order->shipping_email); ?><br>
                        Téléphone: <?php echo e($order->shipping_phone); ?>

                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="section-title">Adresse de livraison</div>
                    <div class="client-info">
                        <?php echo e($order->shipping_address); ?><br>
                        <?php echo e($order->shipping_city); ?>

                        <?php if($order->shipping_postal_code): ?>, <?php echo e($order->shipping_postal_code); ?><?php endif; ?><br>
                        <?php echo e($order->shipping_country == 'CI' ? 'Côte d\'Ivoire' : $order->shipping_country); ?>

                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Articles -->
    <div class="section-title" style="margin-bottom: 10px;">Détail des articles</div>
    <table class="items">
        <thead>
            <tr>
                <th style="width: 45%;">Article</th>
                <th class="text-center" style="width: 15%;">Quantité</th>
                <th class="text-right" style="width: 20%;">Prix unitaire</th>
                <th class="text-right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <strong><?php echo e($item->product_name); ?></strong>
                    <?php if($item->attributes && (is_array($item->attributes) || is_object($item->attributes)) && count((array)$item->attributes) > 0): ?>
                        <div style="margin-top: 5px; font-size: 10px; color: #666;">
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
                <td class="text-right"><strong><?php echo e(number_format($item->total, 0, ',', ' ')); ?> FCFA</strong></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>Sous-total:</strong></td>
                <td class="text-right"><?php echo e(number_format($order->subtotal, 0, ',', ' ')); ?> FCFA</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>Frais de livraison:</strong></td>
                <td class="text-right"><?php echo e($order->shipping_cost == 0 ? 'Gratuite' : number_format($order->shipping_cost, 0, ',', ' ') . ' FCFA'); ?></td>
            </tr>
            <?php if($order->discount > 0): ?>
            <tr>
                <td colspan="3" class="text-right"><strong>Réduction:</strong></td>
                <td class="text-right" style="color: #28a745;">-<?php echo e(number_format($order->discount, 0, ',', ' ')); ?> FCFA</td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="3" class="text-right"><strong>TOTAL À PAYER:</strong></td>
                <td class="text-right" style="color: #f04e27; font-size: 16px;"><?php echo e(number_format($order->total, 0, ',', ' ')); ?> FCFA</td>
            </tr>
        </tfoot>
    </table>

    <!-- Mode de paiement -->
    <div class="payment-info">
        <strong>Mode de paiement:</strong>
        <?php if($order->payment_method == 'card'): ?>
            Carte bancaire
        <?php elseif($order->payment_method == 'mobile_money'): ?>
            Mobile Money
        <?php else: ?>
            Paiement à la livraison (en espèces)
        <?php endif; ?>
        <?php if($order->payment_status == 'paid'): ?>
            <br><span style="color: #28a745; font-weight: bold;">✓ Paiement reçu</span>
        <?php else: ?>
            <br><span style="color: #ffc107; font-weight: bold;">⏳ Paiement en attente</span>
        <?php endif; ?>
    </div>

    <?php if($order->customer_notes): ?>
    <div class="notes-box">
        <strong>Notes du client:</strong><br>
        <?php echo e($order->customer_notes); ?>

    </div>
    <?php endif; ?>

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>Merci pour votre confiance !</strong><br>
            Pour toute question, contactez-nous à <strong><?php echo e($siteEmail ?? 'contact@kazaria.ci'); ?></strong> ou au <strong><?php echo e($sitePhone ?? '+225 XX XX XX XX XX'); ?></strong><br>
            <br>
            <small><?php echo e($siteName ?? 'KAZARIA'); ?> - E-commerce en Côte d'Ivoire - www.kazaria.ci</small>
        </p>
    </div>
</body>
</html>

<?php /**PATH C:\laragon\www\kazaria laravel v0\resources\views\invoice-pdf.blade.php ENDPATH**/ ?>