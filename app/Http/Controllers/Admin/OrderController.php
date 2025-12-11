<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Category;
use App\Services\OrderStatusService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product'])->latest();

        // Filtre par statut (pending, processing, delivered, cancelled)
        if ($request->filled('status') && in_array($request->status, [
            'pending','processing','delivered','cancelled'
        ])) {
            $query->where('status', $request->status);
        }

        // Filtre par statut de paiement
        if ($request->filled('payment_status') && in_array($request->payment_status, [
            'pending','paid'
        ])) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filtre par date (période)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', \Carbon\Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', \Carbon\Carbon::parse($request->date_to)->endOfDay());
        }

        // Filtre par catégorie
        if ($request->filled('category_id')) {
            $categoryId = (int) $request->category_id;
            $query->whereHas('orderItems.product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Filtre par sous-catégorie
        if ($request->filled('subcategory_id')) {
            $subcategoryId = (int) $request->subcategory_id;
            $query->whereHas('orderItems.product', function ($q) use ($subcategoryId) {
                $q->where('subcategory_id', $subcategoryId);
            });
        }

        $orders = $query->paginate(15)->appends($request->except('page'));
        $currentStatus = $request->status;
        $currentPaymentStatus = $request->payment_status;

        // Statistiques
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'paid' => Order::where('payment_status', 'paid')->count(),
            'pending_payment' => Order::where('payment_status', 'pending')->count(),
        ];

        // Listes pour filtres
        $categories = Category::active()
            ->ordered()
            ->with(['subcategories' => function($q){ $q->orderBy('name'); }])
            ->get();

        // Préparer un JSON simple pour le script côté client
        $categoriesJson = $categories->map(function($c){
            return [
                'id' => $c->id,
                'name' => $c->name,
                'subcategories' => $c->subcategories->map(function($s){
                    return ['id' => $s->id, 'name' => $s->name];
                })->values(),
            ];
        })->values();

        return view('admin.orders.index', compact('orders', 'stats', 'currentStatus', 'currentPaymentStatus', 'categories', 'categoriesJson'));
    }


    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.product.store', 'orderItems.variation.attributeValues.attribute']);
        
        // Debug: Vérifier les attributs des items
        foreach ($order->orderItems as $item) {
            \Log::info('OrderItem Debug', [
                'item_id' => $item->id,
                'product_name' => $item->product_name,
                'attributes_raw' => $item->getAttributes()['attributes'] ?? null,
                'attributes_accessor' => $item->attributes,
                'attributes_type' => gettype($item->attributes),
                'attributes_is_array' => is_array($item->attributes),
                'attributes_is_object' => is_object($item->attributes),
                'attributes_count' => is_array($item->attributes) || is_object($item->attributes) ? count((array)$item->attributes) : 0,
            ]);
        }
        
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,processing,shipped,delivered,cancelled,refunded',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $order->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Commande mise à jour avec succès.'
            ]);
        }

        return redirect()->back()->with('success', 'Commande mise à jour avec succès.');
    }

    public function changeStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,delivered,cancelled',
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $order->changeStatus($request->status, $request->reason);
            
            return response()->json([
                'success' => true,
                'message' => 'Statut de la commande mis à jour avec succès.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur changement statut commande admin: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut: ' . $e->getMessage()
            ], 400);
        }
    }

    public function changePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid',
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $order->changePaymentStatus($request->payment_status, $request->reason);
            
            return response()->json([
                'success' => true,
                'message' => 'Statut de paiement mis à jour avec succès.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur changement statut paiement admin: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut de paiement: ' . $e->getMessage()
            ], 400);
        }
    }

    public function getAvailableStatuses(Order $order)
    {
        try {
            $statuses = $order->getAvailableStatuses();
            $paymentStatuses = $order->getAvailablePaymentStatuses();
            
            return response()->json([
                'success' => true,
                'order_statuses' => $statuses,
                'payment_statuses' => $paymentStatuses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statuts disponibles'
            ], 500);
        }
    }

    public function getStats()
    {
        try {
            $stats = [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'delivered' => Order::where('status', 'delivered')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
                'paid' => Order::where('payment_status', 'paid')->count(),
                'pending_payment' => Order::where('payment_status', 'pending')->count(),
                'total_revenue' => Order::where('status', 'delivered')->sum('total'),
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    public function destroy(Order $order)
    {
        try {
            \Log::info('Suppression de la commande', ['order_id' => $order->id, 'order_number' => $order->order_number]);
            
            // Supprimer d'abord les articles de la commande
            $order->orderItems()->delete();
            
            // Puis supprimer la commande
            $order->delete();

            \Log::info('Commande supprimée avec succès', ['order_id' => $order->id]);

            // Toujours retourner une réponse JSON pour les requêtes AJAX
            return response()->json([
                'success' => true,
                'message' => 'Commande supprimée avec succès.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de la commande', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la commande.'
            ], 500);
        }
    }
}

