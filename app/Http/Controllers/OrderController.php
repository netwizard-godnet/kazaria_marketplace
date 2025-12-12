<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Store;
use App\Models\Setting;
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
        
        $subtotal = CartItem::getCartTotal($user->id, null);
        // Appliquer remise promo éventuelle (affichage checkout)
        $promo = session('promo');
        $discount = 0;
        if ($promo && isset($promo['percent'])) {
            $discount = round($subtotal * ((int)$promo['percent']) / 100);
        }
        $total = max(0, $subtotal - $discount); // livraison gérée plus tard
        
        return view('checkout', compact('user', 'cartItems', 'total', 'subtotal', 'discount', 'promo'));
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
        $promo = session('promo');
        $discount = 0;
        if ($promo && isset($promo['percent'])) {
            $discount = round($subtotal * ((int)$promo['percent']) / 100);
        }
        // Calculs avec paramètres
        $shippingCostSetting = \App\Models\Setting::get('shipping_cost', 0);
        $freeThreshold = \App\Models\Setting::get('free_shipping_threshold', 0);
        $shippingCost = ($freeThreshold && $subtotal >= $freeThreshold) ? 0 : (float)$shippingCostSetting;
        $total = max(0, $subtotal - $discount) + $shippingCost;
        
        return view('shipping', compact('user', 'cartItems', 'subtotal', 'shippingCost', 'total', 'discount', 'promo'));
    }

    /**
     * Créer la commande (API - Tokens OU WEB - Sessions)
     */
    public function createOrder(Request $request)
    {
        // Support à la fois pour les tokens (API) et les sessions (WEB)
        $user = $request->user() ?? auth()->user();
        
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
            // Appliquer code promo éventuel
            $promo = session('promo');
            $discount = 0;
            if ($promo && isset($promo['percent'])) {
                $discount = round($subtotal * ((int)$promo['percent']) / 100);
            }
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
            
            // Créer les articles de la commande et collecter les données pour la réservation du stock
            $orderItemsData = [];
            foreach ($cartItems as $cartItem) {
                // S'assurer que les attributs sont bien un objet pour être stockés comme JSON objet
                $attributes = $cartItem->attributes ?? (object)[];
                
                // Si c'est un tableau, le convertir en objet
                if (is_array($attributes)) {
                    $attributes = empty($attributes) ? (object)[] : (object)$attributes;
                }
                
                // Si c'est une chaîne JSON, la décoder en objet
                if (is_string($attributes)) {
                    $decoded = json_decode($attributes, false); // false pour obtenir un objet
                    $attributes = $decoded ?? (object)[];
                }
                
                // S'assurer que c'est un objet
                if (!is_object($attributes)) {
                    $attributes = (object)[];
                }
                
                // Obtenir le SKU de la variation si elle existe
                $productSku = null;
                if ($cartItem->variation_id) {
                    $variation = \App\Models\ProductVariation::find($cartItem->variation_id);
                    if ($variation) {
                        $productSku = $variation->sku;
                    }
                }
                
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'variation_id' => $cartItem->variation_id,
                    'store_id' => $cartItem->product->store_id ?? null,
                    'product_name' => $cartItem->product->name,
                    'product_image' => $cartItem->product->image,
                    'product_sku' => $productSku ?? $cartItem->product->sku ?? null,
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'total' => $cartItem->price * $cartItem->quantity,
                    'attributes' => $attributes
                ]);
                
                // Pour la réservation du stock, utiliser la variation si elle existe
                $orderItemsData[] = [
                    'product_id' => $cartItem->product_id,
                    'variation_id' => $cartItem->variation_id,
                    'quantity' => $cartItem->quantity
                ];
            }
            
            // Réserver le stock directement avec les données collectées
            // (plus fiable que de recharger depuis la base dans une transaction)
            foreach ($orderItemsData as $itemData) {
                // Si une variation existe, décrémenter le stock de la variation
                if (isset($itemData['variation_id']) && $itemData['variation_id']) {
                    $variation = \App\Models\ProductVariation::find($itemData['variation_id']);
                    if ($variation) {
                        $oldStock = $variation->stock;
                        
                        // Décrémenter le stock de la variation
                        DB::table('product_variations')
                            ->where('id', $variation->id)
                            ->decrement('stock', $itemData['quantity']);
                        
                        $newStock = DB::table('product_variations')->where('id', $variation->id)->value('stock');
                        \Log::info("Stock réservé pour la variation (ID: {$variation->id}) du produit {$variation->product->name}. Quantité: {$itemData['quantity']}. Ancien stock: {$oldStock}, Nouveau stock: {$newStock}");
                        
                        // Décrémenter aussi le stock du produit principal
                        $product = Product::find($itemData['product_id']);
                        if ($product) {
                            DB::table('products')
                                ->where('id', $product->id)
                                ->decrement('stock', $itemData['quantity']);
                        }
                    }
                } else {
                    // Pas de variation, décrémenter le stock du produit
                    $product = Product::find($itemData['product_id']);
                    if ($product) {
                        $oldStock = $product->stock;
                        
                        // Utiliser une mise à jour directe en base pour garantir la persistance
                        DB::table('products')
                            ->where('id', $product->id)
                            ->decrement('stock', $itemData['quantity']);
                        
                        $newStock = DB::table('products')->where('id', $product->id)->value('stock');
                        \Log::info("Stock réservé pour le produit {$product->name} (ID: {$product->id}). Quantité: {$itemData['quantity']}. Ancien stock: {$oldStock}, Nouveau stock: {$newStock}");
                    } else {
                        \Log::warning("Produit introuvable pour Product ID: {$itemData['product_id']}");
                        throw new \Exception("Produit introuvable pour Product ID: {$itemData['product_id']}");
                    }
                }
            }
            
            // Vider le panier
            CartItem::where('user_id', $user->id)->delete();
            // Consommer le code promo (incrément d'utilisation et nettoyer la session)
            if ($promo && isset($promo['code'])) {
                \App\Models\Coupon::where('code', $promo['code'])->increment('uses');
            }
            session()->forget('promo');
            
            // NE PAS marquer comme payée si paiement à la livraison
            // Le statut de paiement reste "pending" jusqu'à la livraison effective
            
            // Récupérer les paramètres de contact depuis la BD
            $siteEmail = Setting::get('site_email', 'contact@kazaria.ci');
            $sitePhone = Setting::get('site_phone', '+225 XX XX XX XX XX');
            $siteName = Setting::get('site_name', 'KAZARIA');
            $siteAddress = Setting::get('site_address', 'Côte d\'Ivoire');
            
            // Générer le PDF de la facture
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice-pdf', [
                'order' => $order,
                'siteEmail' => $siteEmail,
                'sitePhone' => $sitePhone,
                'siteName' => $siteName,
                'siteAddress' => $siteAddress
            ]);
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
            
            // Construire l'URL de redirection (avec token seulement si c'est une requête API)
            $redirectUrl = route('order-invoice', $order->order_number);
            $bearerToken = $request->bearerToken();
            if ($bearerToken) {
                $redirectUrl .= '?token=' . $bearerToken;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Commande créée avec succès',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect' => $redirectUrl
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
            ->with(['items.product', 'items.variation.attributeValues.attribute', 'user'])
            ->firstOrFail();
        
        // Récupérer les paramètres de contact depuis la BD
        $siteEmail = Setting::get('site_email', 'contact@kazaria.ci');
        $sitePhone = Setting::get('site_phone', '+225 XX XX XX XX XX');
        $siteName = Setting::get('site_name', 'KAZARIA');
        $siteAddress = Setting::get('site_address', 'Côte d\'Ivoire');
        
        return view('invoice', compact('order', 'siteEmail', 'sitePhone', 'siteName', 'siteAddress'));
    }

    /**
     * Télécharger la facture en PDF
     */
    public function downloadInvoice($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'items.variation.attributeValues.attribute', 'user'])
            ->firstOrFail();
        
        // Récupérer les paramètres de contact depuis la BD
        $siteEmail = Setting::get('site_email', 'contact@kazaria.ci');
        $sitePhone = Setting::get('site_phone', '+225 XX XX XX XX XX');
        $siteName = Setting::get('site_name', 'KAZARIA');
        $siteAddress = Setting::get('site_address', 'Côte d\'Ivoire');
        
        // Générer et télécharger le PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice-pdf', compact('order', 'siteEmail', 'sitePhone', 'siteName', 'siteAddress'));
        
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
     * Annuler une commande (client)
     */
    public function cancelOrder(Request $request, $orderNumber)
    {
        // Support à la fois pour session et token
        $user = auth()->user() ?? $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }
        
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', $user->id)
            ->first();
        
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], 404);
        }
        
        // Vérifier que la commande peut être annulée (seulement si statut = pending)
        if ($order->status !== OrderStatusService::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut plus être annulée. Elle est déjà en cours de livraison ou a été livrée.'
            ], 422);
        }
        
        try {
            $reason = $request->input('reason', 'Annulation par le client');
            $order->changeStatus(OrderStatusService::STATUS_CANCELLED, $reason);
            
            return response()->json([
                'success' => true,
                'message' => 'Commande annulée avec succès. Le stock a été libéré.',
                'order' => $order->fresh()
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'annulation de la commande: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation de la commande: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Détails d'une commande (API)
     */
    public function getOrderDetails($orderNumber, Request $request)
    {
        $user = $request->user();
        
        $order = Order::where('order_number', $orderNumber)
            ->with(['orderItems.product', 'orderItems.variation.attributeValues.attribute'])
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
            ->with(['orderItems.product', 'orderItems.variation.attributeValues.attribute'])
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

    /**
     * Rechercher une commande par numéro et email (pour le suivi public)
     */
    public function trackOrder(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'email' => 'required|email',
        ]);

        try {
            $order = Order::where('order_number', $request->order_number)
                ->where('shipping_email', $request->email)
                ->with(['orderItems.product', 'user'])
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande non trouvée. Vérifiez votre numéro de commande et votre email.'
                ], 404);
            }

            // Préparer les données de suivi
            $trackingHistory = [];
            
            // Ajouter les statuts de la commande
            if ($order->created_at) {
                $trackingHistory[] = [
                    'date' => $order->created_at->format('Y-m-d'),
                    'time' => $order->created_at->format('H:i'),
                    'status' => 'Commande confirmée',
                    'location' => 'Abidjan',
                    'active' => true
                ];
            }

            if ($order->status === 'processing' || $order->status === 'shipped' || $order->status === 'delivered') {
                $trackingHistory[] = [
                    'date' => $order->created_at->addDay()->format('Y-m-d'),
                    'time' => '09:00',
                    'status' => 'Préparation en cours',
                    'location' => 'Entrepôt KAZARIA',
                    'active' => in_array($order->status, ['shipped', 'delivered'])
                ];
            }

            if ($order->status === 'shipped' || $order->status === 'delivered') {
                $trackingHistory[] = [
                    'date' => $order->shipped_at ? $order->shipped_at->format('Y-m-d') : $order->created_at->addDays(2)->format('Y-m-d'),
                    'time' => $order->shipped_at ? $order->shipped_at->format('H:i') : '10:00',
                    'status' => 'En cours de livraison',
                    'location' => 'En route',
                    'active' => $order->status === 'shipped'
                ];
            }

            if ($order->status === 'delivered') {
                $trackingHistory[] = [
                    'date' => $order->delivered_at ? $order->delivered_at->format('Y-m-d') : $order->created_at->addDays(3)->format('Y-m-d'),
                    'time' => $order->delivered_at ? $order->delivered_at->format('H:i') : '14:00',
                    'status' => 'Livrée',
                    'location' => $order->shipping_city ?? 'Abidjan',
                    'active' => true
                ];
            }

            if ($order->status === 'cancelled') {
                $trackingHistory[] = [
                    'date' => $order->updated_at->format('Y-m-d'),
                    'time' => $order->updated_at->format('H:i'),
                    'status' => 'Commande annulée',
                    'location' => 'Abidjan',
                    'active' => true
                ];
            }

            // Obtenir le label du statut
            $statusLabels = [
                'pending' => 'En attente',
                'processing' => 'En cours de traitement',
                'shipped' => 'Expédiée',
                'delivered' => 'Livrée',
                'cancelled' => 'Annulée',
                'refunded' => 'Remboursée'
            ];

            return response()->json([
                'success' => true,
                'order' => [
                    'number' => $order->order_number,
                    'date' => $order->created_at->format('d/m/Y'),
                    'status' => $statusLabels[$order->status] ?? $order->status,
                    'status_code' => $order->status,
                    'total' => number_format($order->total, 0, ',', ' '),
                    'subtotal' => number_format($order->subtotal, 0, ',', ' '),
                    'shipping_cost' => number_format($order->shipping_cost, 0, ',', ' '),
                    'discount' => number_format($order->discount, 0, ',', ' '),
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'shipping_name' => $order->shipping_name,
                    'shipping_address' => $order->shipping_address,
                    'shipping_city' => $order->shipping_city,
                    'shipping_phone' => $order->shipping_phone,
                    'items' => $order->orderItems->map(function($item) {
                        // Récupérer les attributs
                        $attributes = $item->attributes ?? (object)[];
                        $attrsArray = is_object($attributes) ? (array)$attributes : (is_array($attributes) ? $attributes : []);
                        $hasAttributes = !empty($attrsArray) && count($attrsArray) > 0;
                        
                        return [
                            'name' => $item->product_name,
                            'quantity' => $item->quantity,
                            'price' => number_format($item->price, 0, ',', ' '),
                            'total' => number_format($item->total, 0, ',', ' '),
                            'image' => $item->product_image ? asset($item->product_image) : null,
                            'attributes' => $hasAttributes ? $attrsArray : null,
                        ];
                    }),
                    'tracking' => $trackingHistory
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors du suivi de commande: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la recherche de votre commande.'
            ], 500);
        }
    }
}
