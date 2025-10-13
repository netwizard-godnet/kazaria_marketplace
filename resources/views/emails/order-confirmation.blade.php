<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f04e27;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
        }
        .order-box {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #f04e27;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">KAZARIA</h1>
            <p style="margin: 5px 0;">Confirmation de commande</p>
        </div>
        
        <div class="content">
            <h2 style="color: #f04e27;">Bonjour {{ $order->shipping_name }} !</h2>
            
            <p>Merci pour votre commande sur KAZARIA. Votre commande a été enregistrée avec succès.</p>
            
            <div class="order-box">
                <h3 style="margin-top: 0;">Détails de la commande</h3>
                <p>
                    <strong>Numéro de commande:</strong> {{ $order->order_number }}<br>
                    <strong>Date:</strong> {{ $order->created_at->format('d/m/Y à H:i') }}<br>
                    <strong>Statut:</strong> <span style="color: #f04e27;">{{ $order->status_label }}</span>
                </p>
                
                <h4>Articles commandés:</h4>
                <table>
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th>Article</th>
                            <th style="text-align: center;">Qté</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td style="text-align: center;">{{ $item->quantity }}</td>
                            <td style="text-align: right;">{{ number_format($item->total, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background-color: #f8f9fa;">
                            <td colspan="2">Total:</td>
                            <td style="text-align: right; color: #f04e27; font-size: 18px;">{{ number_format($order->total, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="order-box">
                <h4 style="margin-top: 0;">Adresse de livraison</h4>
                <p>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}
                    @if($order->shipping_postal_code), {{ $order->shipping_postal_code }}@endif<br>
                    {{ $order->shipping_country == 'CI' ? 'Côte d\'Ivoire' : $order->shipping_country }}
                </p>
            </div>
            
            <div class="order-box">
                <h4 style="margin-top: 0;">Mode de paiement</h4>
                <p>
                    @if($order->payment_method == 'card')
                        <strong>Carte bancaire</strong>
                    @elseif($order->payment_method == 'mobile_money')
                        <strong>Mobile Money</strong>
                    @else
                        <strong>Paiement à la livraison</strong>
                    @endif
                </p>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('order-invoice', $order->order_number) }}" class="button">
                    Voir ma facture
                </a>
            </div>
            
            <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin-top: 20px;">
                <h4 style="margin-top: 0;"><i>📦</i> Que se passe-t-il maintenant ?</h4>
                <ol style="margin: 0; padding-left: 20px;">
                    <li>Votre commande est en cours de préparation</li>
                    <li>Vous recevrez un email lorsqu'elle sera expédiée</li>
                    <li>Livraison sous 2-5 jours ouvrables</li>
                    <li>Vous pouvez suivre votre commande depuis votre profil</li>
                </ol>
            </div>
        </div>
        
        <div class="footer">
            <p>
                <strong>Besoin d'aide ?</strong><br>
                Contactez-nous à contact@kazaria.ci ou au +225 XX XX XX XX XX
            </p>
            <p style="color: #999;">
                KAZARIA - E-commerce en Côte d'Ivoire<br>
                © {{ date('Y') }} Tous droits réservés
            </p>
        </div>
    </div>
</body>
</html>

