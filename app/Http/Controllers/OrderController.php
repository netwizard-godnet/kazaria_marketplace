<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Store;
use App\Services\StockService;
use App\Services\OrderStatusService;
use App\Notifications\NewOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\OrderConfirmationMail;

class OrderController extends Controller
{
    /**
     * Page de checkout (WEB - Sessions)
     */
    public function checkout(Request $request)
    {
        // Vérifier si l'utilisateur est connecté via session
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login')->with('message', 'Veuillez vous connecter pour passer commande');
        }
        
        // Récupérer les articles du panier
        $cartItems = CartItem::getCartItems($user->id, null);
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('product-cart')->with('error', 'Votre panier est vide');
        }
        
        $total = CartItem::getCartTotal($user->id, null);
        
        return view('checkout', compact('user', 'cartItems', 'total'));
    }

    /**
     * Traiter la commande et afficher la page de livraison (API - Tokens)
     */
    public function processCheckout(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        // Récupérer les articles du panier
        $cartItems = CartItem::getCartItems($user->id, null);
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre panier est vide'
            ], 400);
        }
        
        return response()->json([
            'success' => true,
            'redirect' => route('shipping')
        ]);
    }

    /**
     * Afficher la page de détails de livraison (WEB - Sessions)
     */
    public function shipping(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        $cartItems = CartItem::getCartItems($user->id, null);
        $subtotal = CartItem::getCartTotal($user->id, null);
        // Calculs avec paramètres
        $shippingCostSetting = \App\Models\Setting::get('shipping_cost', 0);
        $freeThreshold = \App\Models\Setting::get('free_shipping_threshold', 0);
        $shippingCost = ($freeThreshold && $subtotal >= $freeThreshold) ? 0 : (float)$shippingCostSetting;
        $total = $subtotal + $shippingCost;
        
        return view('shipping', compact('user', 'cartItems', 'subtotal', 'shippingCost', 'total'));
    }

    /**
     * Créer la commande (API - Tokens)
     */
    public function createOrder(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        $validator = Validator::make($request->all(), [
            'shipping_name' => 'required|string|max:255',
            'shipping_email' => 'required|email|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:10',
            'shipping_country' => 'required|string|max:2',
            'payment_method' => 'required|in:card,mobile_money,cash_on_delivery',
            'customer_notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();
            
            // Récupérer les articles du panier
            $cartItems = CartItem::getCartItems($user->id, null);
            
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre panier est vide'
                ], 400);
            }
            
            // Calculer les montants
            $subtotal = CartItem::getCartTotal($user->id, null);
            // Frais et seuil depuis paramètres
            $shippingCostSetting = \App\Models\Setting::get('shipping_cost', 0);
            $freeThreshold = \App\Models\Setting::get('free_shipping_threshold', 0);
            $shippingCost = ($freeThreshold && $subtotal >= $freeThreshold) ? 0 : (float)$shippingCostSetting;
            $tax = 0;
            $discount = 0;
            $total = $subtotal + $shippingCost + $tax - $discount;
            
            // Vérifier la disponibilité du stock
            $stockErrors = StockService::checkStockAvailability($cartItems);
            if (!empty($stockErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuffisant',
                    'errors' => $stockErrors
                ], 400);
            }
            
            // Créer la commande
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'shipping_name' => $request->shipping_name,
                'shipping_email' => $request->shipping_email,
                'shipping_phone' => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_postal_code' => $request->shipping_postal_code,
                'shipping_country' => $request->shipping_country,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'status' => OrderStatusService::STATUS_PENDING,
                'payment_status' => OrderStatusService::PAYMENT_PENDING,
                'payment_method' => $request->payment_method,
                'customer_notes' => $request->customer_notes
            ]);
            
            // Créer les articles de la commande
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'store_id' => $cartItem->product->store_id,
                    'product_name' => $cartItem->product->name,
                    'product_image' => $cartItem->product->image,
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'total' => $cartItem->price * $cartItem->quantity
                ]);
            }
            
            // Réserver le stock
            StockService::reserveStock($order);
            
            // Vider le panier
            CartItem::where('user_id', $user->id)->delete();
            
            // NE PAS marquer comme payée si paiement à la livraison
            // Le statut de paiement reste "pending" jusqu'à la livraison effective
            
            // Générer le PDF de la facture
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice-pdf', ['order' => $order]);
            $pdfPath = storage_path('app/public/invoices/');
            
            // Créer le dossier s'il n'existe pas
            if (!file_exists($pdfPath)) {
                mkdir($pdfPath, 0777, true);
            }
            
            $pdfFileName = 'facture-' . $order->order_number . '.pdf';
            $pdfFullPath = $pdfPath . $pdfFileName;
            $pdf->save($pdfFullPath);
            
            // Enregistrer le chemin dans la BDD
            $order->update([
                'invoice_path' => 'invoices/' . $pdfFileName
            ]);
            
            // Envoyer l'email de confirmation avec la facture
            try {
                Mail::to($order->shipping_email)->send(new OrderConfirmationMail($order, $pdfFullPath));
            } catch (\Exception $e) {
                \Log::error('Erreur envoi email commande: ' . $e->getMessage());
                // On continue même si l'email échoue
            }
            
            // Notifier les vendeurs des nouvelles commandes
            try {
                $this->notifySellers($order);
            } catch (\Exception $e) {
                \Log::error('Erreur notification vendeurs: ' . $e->getMessage());
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Commande créée avec succès',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect' => route('order-invoice', $order->order_number) . '?token=' . $request->bearerToken()
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur création commande: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la commande: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher la facture
     */
    public function invoice($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('items.product', 'user')
            ->firstOrFail();
        
        return view('invoice', compact('order'));
    }

    /**
     * Télécharger la facture en PDF
     */
    public function downloadInvoice($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('items.product', 'user')
            ->firstOrFail();
        
        // Générer et télécharger le PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice-pdf', compact('order'));
        
        return $pdf->download('facture-' . $order->order_number . '.pdf');
    }

    /**
     * Obtenir les commandes de l'utilisateur
     */
    public function myOrders(Request $request)
    {
        // Support à la fois pour session et token
        $user = auth()->user() ?? $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        $orders = Order::forUser($user->id)
            ->recent()
            ->with('items')
            ->get();
        
        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    /**
     * Détails d'une commande (API)
     */
    public function getOrderDetails($orderNumber, Request $request)
    {
        $user = $request->user();
        
        $order = Order::where('order_number', $orderNumber)
            ->with('orderItems.product')
            ->first();
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }
        
        // Vérifier que la commande appartient à l'utilisateur
        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }

    /**
     * Détails d'une commande (vue)
     */
    public function orderDetails($orderNumber, Request $request)
    {
        $user = $request->user();
        
        $order = Order::where('order_number', $orderNumber)
            ->with('orderItems.product')
            ->firstOrFail();
        
        // Vérifier que la commande appartient à l'utilisateur
        if ($order->user_id !== $user->id) {
            abort(403, 'Accès non autorisé');
        }
        
        return view('order-details', compact('order'));
    }
    
    /**
     * Notifier les vendeurs d'une nouvelle commande
     */
    private function notifySellers(Order $order)
    {
        $storeIds = $order->orderItems->pluck('store_id')->unique();
        $stores = Store::whereIn('id', $storeIds)
            ->where('status', 'active')
            ->with('user')
            ->get();

        foreach ($stores as $store) {
            if ($store->user && $store->user->email) {
                try {
                    $store->user->notify(new NewOrderNotification($order, $store));
                    \Log::info("Notification envoyée au vendeur {$store->name} pour la commande {$order->order_number}");
                } catch (\Exception $e) {
                    \Log::error("Erreur envoi notification vendeur {$store->name}: " . $e->getMessage());
                }
            }
        }
    }
}
