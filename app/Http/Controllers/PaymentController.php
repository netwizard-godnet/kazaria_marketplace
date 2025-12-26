<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Obtenir l'historique des paiements de l'utilisateur
     */
    public function getPaymentHistory(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        $payments = Payment::where('user_id', $user->id)
            ->with(['order', 'store'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'payments' => $payments->map(fn($p) => $this->formatPayment($p)),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ]
        ]);
    }

    /**
     * Obtenir les détails d'un paiement
     */
    public function getPaymentDetails(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        $payment = Payment::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['order.items', 'store'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'payment' => $this->formatPayment($payment, true)
        ]);
    }

    /**
     * Obtenir l'historique des factures (basé sur les commandes)
     */
    public function getInvoiceHistory(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        // Les factures sont liées aux commandes
        $orders = Order::where('user_id', $user->id)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'invoices' => $orders->map(fn($o) => $this->formatInvoice($o)),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    /**
     * Télécharger une facture
     */
    public function downloadInvoice(Request $request, $orderNumber)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        // Extraire l'ID de la commande depuis le numéro
        $orderId = (int) str_replace('CMD-', '', $orderNumber);
        $orderId = (int) ltrim($orderId, '0');

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Générer l'URL de téléchargement de la facture
        $invoiceUrl = route('order-download', ['orderNumber' => $orderNumber]);

        return response()->json([
            'success' => true,
            'invoice_url' => $invoiceUrl,
            'order_number' => $orderNumber,
        ]);
    }

    /**
     * Formater un paiement pour l'API
     */
    private function formatPayment($payment, $includeDetails = false)
    {
        $data = [
            'id' => $payment->id,
            'order_id' => $payment->order_id,
            'order_number' => $payment->order ? 'CMD-' . str_pad($payment->order->id, 6, '0', STR_PAD_LEFT) : null,
            'payment_method' => $payment->payment_method,
            'payment_reference' => $payment->payment_reference,
            'amount' => $payment->amount,
            'status' => $payment->status,
            'status_label' => $payment->status_label,
            'transaction_id' => $payment->transaction_id,
            'paid_at' => $payment->paid_at?->toISOString(),
            'created_at' => $payment->created_at->toISOString(),
            'store' => $payment->store ? [
                'id' => $payment->store->id,
                'name' => $payment->store->name,
                'slug' => $payment->store->slug,
            ] : null,
        ];

        if ($includeDetails && $payment->order) {
            $data['order'] = [
                'id' => $payment->order->id,
                'order_number' => 'CMD-' . str_pad($payment->order->id, 6, '0', STR_PAD_LEFT),
                'total' => $payment->order->total,
                'status' => $payment->order->status,
                'items_count' => $payment->order->items()->count(),
            ];
        }

        return $data;
    }

    /**
     * Formater une facture pour l'API
     */
    private function formatInvoice($order)
    {
        return [
            'id' => $order->id,
            'order_number' => 'CMD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
            'total' => $order->total,
            'subtotal' => $order->subtotal,
            'shipping_cost' => $order->shipping_cost,
            'tax' => $order->tax,
            'discount' => $order->discount,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'created_at' => $order->created_at->toISOString(),
            'paid_at' => $order->paid_at?->toISOString(),
            'invoice_url' => route('order-download', ['orderNumber' => 'CMD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT)]),
        ];
    }

    /**
     * Obtenir les méthodes de paiement disponibles
     */
    public function getPaymentMethods(Request $request)
    {
        return response()->json([
            'success' => true,
            'payment_methods' => [
                [
                    'id' => 'mobile_money',
                    'name' => 'Mobile Money',
                    'description' => 'Paiement via Orange Money, MTN Mobile Money ou Moov Money',
                    'icon' => 'mobile_money',
                    'available' => true,
                    'providers' => ['orange', 'mtn', 'moov'],
                ],
                [
                    'id' => 'cash_on_delivery',
                    'name' => 'Paiement à la livraison',
                    'description' => 'Payez lorsque vous recevez votre commande',
                    'icon' => 'cash',
                    'available' => true,
                ],
                [
                    'id' => 'card',
                    'name' => 'Carte bancaire',
                    'description' => 'Paiement par carte bancaire (si disponible)',
                    'icon' => 'credit_card',
                    'available' => false, // À activer selon votre configuration
                ],
            ],
        ]);
    }
}
