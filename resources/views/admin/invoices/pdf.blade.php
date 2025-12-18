<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->invoice_number }}</title>
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
                <div class="logo">{{ $companyName }}</div>
                <div class="company-info">
                    @if($companyAddress)
                        {{ $companyAddress }}<br>
                    @endif
                    Email: {{ $companyEmail }}<br>
                    Tél: {{ $companyPhone }}
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">FACTURE</div>
                <div class="invoice-info">
                    <strong>N° Facture:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Date:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}<br>
                    @if($invoice->due_date)
                        <strong>Date d'échéance:</strong> {{ $invoice->due_date->format('d/m/Y') }}<br>
                    @endif
                    <strong>Statut:</strong> {{ $invoice->status_label }}
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
                        <strong>{{ $invoice->client_name }}</strong><br>
                        Email: {{ $invoice->client_email }}<br>
                        @if($invoice->client_phone)
                            Téléphone: {{ $invoice->client_phone }}<br>
                        @endif
                        @if($invoice->client_address)
                            {{ $invoice->client_address }}<br>
                        @endif
                        @if($invoice->client_city)
                            {{ $invoice->client_city }}
                            @if($invoice->client_postal_code), {{ $invoice->client_postal_code }}@endif<br>
                        @endif
                        @if($invoice->client_country)
                            {{ $invoice->client_country == 'CI' ? 'Côte d\'Ivoire' : $invoice->client_country }}
                        @endif
                    </div>
                </td>
                <td style="width: 50%;">
                    @if($invoice->order)
                        <div class="section-title">Commande associée</div>
                        <div class="client-info">
                            N° Commande: {{ $invoice->order->order_number }}<br>
                            Date: {{ $invoice->order->created_at->format('d/m/Y') }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Articles -->
    @php
        // S'assurer que les items sont bien un tableau
        $items = $invoice->items;
        if (is_string($items)) {
            $items = json_decode($items, true) ?? [];
        }
        if (!is_array($items)) {
            $items = [];
        }
    @endphp
    @if(is_array($items) && count($items) > 0)
    <div class="section-title" style="margin-bottom: 10px;">Détail des articles</div>
    <table class="items">
        <thead>
            <tr>
                <th style="width: 45%;">Description</th>
                <th class="text-center" style="width: 15%;">Quantité</th>
                <th class="text-right" style="width: 20%;">Prix unitaire</th>
                <th class="text-right" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>
                    <strong>{{ $item['description'] ?? ($item['name'] ?? 'Article') }}</strong>
                </td>
                <td class="text-center">{{ $item['quantity'] ?? 1 }}</td>
                <td class="text-right">{{ number_format(floatval($item['price'] ?? 0), 0, ',', ' ') }} FCFA</td>
                <td class="text-right"><strong>{{ number_format(floatval($item['total'] ?? (($item['quantity'] ?? 1) * ($item['price'] ?? 0))), 0, ',', ' ') }} FCFA</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>Sous-total:</strong></td>
                <td class="text-right">{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</td>
            </tr>
            @if($invoice->tax_rate > 0)
            <tr>
                <td colspan="3" class="text-right"><strong>TVA ({{ $invoice->tax_rate }}%):</strong></td>
                <td class="text-right">{{ number_format($invoice->tax_amount, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            @if($invoice->shipping_cost > 0)
            <tr>
                <td colspan="3" class="text-right"><strong>Frais de livraison:</strong></td>
                <td class="text-right">{{ number_format($invoice->shipping_cost, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            @if($invoice->discount > 0)
            <tr>
                <td colspan="3" class="text-right"><strong>Réduction:</strong></td>
                <td class="text-right" style="color: #28a745;">-{{ number_format($invoice->discount, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="3" class="text-right"><strong>TOTAL À PAYER:</strong></td>
                <td class="text-right" style="color: #f04e27; font-size: 16px;">{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tfoot>
    </table>
    @elseif($invoice->description)
    <div class="section-title" style="margin-bottom: 10px;">Description</div>
    <div style="padding: 15px; background-color: #f8f9fa; border-left: 4px solid #f04e27; margin-bottom: 20px;">
        {{ $invoice->description }}
    </div>
    <div style="margin-top: 20px;">
        <table class="items">
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>Sous-total:</strong></td>
                    <td class="text-right">{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</td>
                </tr>
                @if($invoice->tax_rate > 0)
                <tr>
                    <td colspan="3" class="text-right"><strong>TVA ({{ $invoice->tax_rate }}%):</strong></td>
                    <td class="text-right">{{ number_format($invoice->tax_amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
                @if($invoice->shipping_cost > 0)
                <tr>
                    <td colspan="3" class="text-right"><strong>Frais de livraison:</strong></td>
                    <td class="text-right">{{ number_format($invoice->shipping_cost, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
                @if($invoice->discount > 0)
                <tr>
                    <td colspan="3" class="text-right"><strong>Réduction:</strong></td>
                    <td class="text-right" style="color: #28a745;">-{{ number_format($invoice->discount, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td colspan="3" class="text-right"><strong>TOTAL À PAYER:</strong></td>
                    <td class="text-right" style="color: #f04e27; font-size: 16px;">{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <!-- Mode de paiement -->
    @if($invoice->payment_method)
    <div class="payment-info">
        <strong>Mode de paiement:</strong>
        @if($invoice->payment_method == 'card')
            Carte bancaire
        @elseif($invoice->payment_method == 'mobile_money')
            Mobile Money
        @elseif($invoice->payment_method == 'cash')
            Espèces
        @elseif($invoice->payment_method == 'bank_transfer')
            Virement bancaire
        @else
            {{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}
        @endif
        @if($invoice->payment_reference)
            <br><strong>Référence:</strong> {{ $invoice->payment_reference }}
        @endif
        @if($invoice->status == 'paid')
            <br><span style="color: #28a745; font-weight: bold;">✓ Paiement reçu</span>
            @if($invoice->paid_date)
                <br><span style="font-size: 10px;">Le {{ $invoice->paid_date->format('d/m/Y') }}</span>
            @endif
        @else
            <br><span style="color: #ffc107; font-weight: bold;">⏳ Paiement en attente</span>
        @endif
    </div>
    @endif

    @if($invoice->terms)
    <div class="notes-box">
        <strong>Conditions générales:</strong><br>
        {{ $invoice->terms }}
    </div>
    @endif

    <!-- Pied de page -->
    <div class="footer">
        <p>
            <strong>Merci pour votre confiance !</strong><br>
            Pour toute question, contactez-nous à <strong>{{ $companyEmail }}</strong> ou au <strong>{{ $companyPhone }}</strong><br>
            <br>
            <small>{{ $companyName }} - E-commerce en Côte d'Ivoire</small>
        </p>
    </div>
</body>
</html>

