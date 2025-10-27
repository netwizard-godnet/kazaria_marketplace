<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;

class PaymentController extends Controller
{
    /**
     * Afficher la liste des paiements
     */
    public function index(Request $request)
    {
        $query = Payment::with(['order', 'user', 'store']);
        
        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_reference', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('nom', 'like', "%{$search}%")
                               ->orWhere('prenoms', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }
        
        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $payments = $query->paginate(15)->withQueryString();
        
        // Statistiques
        $stats = [
            'total' => Payment::count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
            'refunded' => Payment::where('status', 'refunded')->count(),
            'total_amount' => Payment::where('status', 'completed')->sum('amount'),
            'total_commission' => Payment::where('status', 'completed')->sum('commission_amount'),
            'average_amount' => Payment::where('status', 'completed')->avg('amount'),
        ];
        
        return view('admin.payments.index', compact('payments', 'stats'));
    }
    
    /**
     * Afficher les détails d'un paiement
     */
    public function show(Payment $payment)
    {
        $payment->load(['order.orderItems.product', 'user', 'store']);
        
        return view('admin.payments.show', compact('payment'));
    }
    
    /**
     * Rembourser un paiement
     */
    public function refund(Request $request, Payment $payment)
    {
        $request->validate([
            'refund_amount' => 'required|numeric|min:0.01|max:' . $payment->amount,
            'refund_reason' => 'required|string|max:500'
        ]);
        
        $payment->refund($request->refund_amount, $request->refund_reason);
        
        // Créer une notification pour l'utilisateur
        \App\Models\Notification::createNotification(
            $payment->user_id,
            'payment',
            'Remboursement effectué',
            'Votre paiement de ' . number_format($request->refund_amount, 2) . ' FCFA a été remboursé.',
            ['payment_id' => $payment->id, 'refund_amount' => $request->refund_amount]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Remboursement effectué avec succès'
        ]);
    }
    
    /**
     * Annuler un paiement
     */
    public function cancel(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les paiements en attente peuvent être annulés'
            ], 400);
        }
        
        $payment->update(['status' => 'cancelled']);
        
        // Créer une notification pour l'utilisateur
        \App\Models\Notification::createNotification(
            $payment->user_id,
            'payment',
            'Paiement annulé',
            'Votre paiement a été annulé par l\'administrateur.',
            ['payment_id' => $payment->id]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Paiement annulé avec succès'
        ]);
    }
    
    /**
     * Marquer un paiement comme terminé
     */
    public function markAsCompleted(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Seuls les paiements en attente peuvent être marqués comme terminés'
            ], 400);
        }
        
        $payment->markAsPaid();
        
        // Mettre à jour le statut de la commande
        $payment->order->update([
            'status' => 'paid',
            'payment_status' => 'completed',
            'paid_at' => now()
        ]);
        
        // Créer une notification pour l'utilisateur
        \App\Models\Notification::createNotification(
            $payment->user_id,
            'payment',
            'Paiement confirmé',
            'Votre paiement de ' . number_format($payment->amount, 2) . ' FCFA a été confirmé.',
            ['payment_id' => $payment->id, 'order_id' => $payment->order_id]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Paiement marqué comme terminé'
        ]);
    }
    
    /**
     * Obtenir les statistiques des paiements
     */
    public function getStats(Request $request)
    {
        $period = $request->get('period', '30'); // jours
        
        $startDate = now()->subDays($period);
        
        $stats = [
            'total_payments' => Payment::where('created_at', '>=', $startDate)->count(),
            'completed_payments' => Payment::where('status', 'completed')
                                          ->where('created_at', '>=', $startDate)
                                          ->count(),
            'total_amount' => Payment::where('status', 'completed')
                                   ->where('created_at', '>=', $startDate)
                                   ->sum('amount'),
            'total_commission' => Payment::where('status', 'completed')
                                       ->where('created_at', '>=', $startDate)
                                       ->sum('commission_amount'),
            'average_amount' => Payment::where('status', 'completed')
                                     ->where('created_at', '>=', $startDate)
                                     ->avg('amount'),
            'refunded_amount' => Payment::where('status', 'refunded')
                                      ->where('created_at', '>=', $startDate)
                                      ->sum('refund_amount'),
        ];
        
        // Paiements par méthode
        $paymentsByMethod = Payment::where('created_at', '>=', $startDate)
                                 ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                                 ->groupBy('payment_method')
                                 ->get();
        
        // Paiements par statut
        $paymentsByStatus = Payment::where('created_at', '>=', $startDate)
                                 ->selectRaw('status, COUNT(*) as count, SUM(amount) as total')
                                 ->groupBy('status')
                                 ->get();
        
        // Évolution des paiements (par jour)
        $dailyPayments = Payment::where('created_at', '>=', $startDate)
                               ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
                               ->groupBy('date')
                               ->orderBy('date')
                               ->get();
        
        return response()->json([
            'stats' => $stats,
            'payments_by_method' => $paymentsByMethod,
            'payments_by_status' => $paymentsByStatus,
            'daily_payments' => $dailyPayments
        ]);
    }
    
    /**
     * Exporter les paiements en CSV
     */
    public function export(Request $request)
    {
        $query = Payment::with(['order', 'user', 'store']);
        
        // Appliquer les mêmes filtres que l'index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $payments = $query->get();
        
        $filename = 'paiements_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Référence',
                'Utilisateur',
                'Email',
                'Boutique',
                'Montant',
                'Commission',
                'Méthode',
                'Statut',
                'Date',
                'Transaction ID'
            ]);
            
            // Données
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->payment_reference,
                    $payment->user->nom . ' ' . $payment->user->prenoms,
                    $payment->user->email,
                    $payment->store->name ?? 'N/A',
                    $payment->amount,
                    $payment->commission_amount,
                    $payment->payment_method,
                    $payment->status,
                    $payment->created_at->format('Y-m-d H:i:s'),
                    $payment->transaction_id ?? 'N/A'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
