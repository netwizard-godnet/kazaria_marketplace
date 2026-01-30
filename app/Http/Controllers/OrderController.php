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
     * Permet l'accès sans connexion - utilise session_id pour le panier invité
     */
    public function checkout(Request $request)
    {
        $user = auth()->user();
        
        // Récupérer les articles du panier (utilisateur connecté ou session invité)
        if ($user) {
            $cartItems = CartItem::getCartItems($user->id, null);
            $subtotal = CartItem::getCartTotal($user->id, null);
            $sessionId = null;
        } else {
            // Pour les invités, utiliser le session_id depuis le header X-Session-ID ou depuis la requête
            // C'est l'ID stocké dans localStorage côté client, pas la session Laravel
            $sessionId = $request->header('X-Session-ID') ?? $request->input('session_id');
            
            // Si pas de session_id fourni, essayer de récupérer depuis la session Laravel comme fallback
            // (pour compatibilité avec les anciennes sessions)
            if (!$sessionId && $request->hasSession()) {
                $storedSessionId = session('guest_session_id');
                if ($storedSessionId) {
                    $sessionId = $storedSessionId;
                }
            }
            
            // Stocker le session_id dans la session Laravel pour les prochaines requêtes
            if ($sessionId && $request->hasSession()) {
                session(['guest_session_id' => $sessionId]);
            }
            
            \Log::info('🔍 [CHECKOUT] Récupération panier invité', [
                'session_id_from_header' => $request->header('X-Session-ID'),
                'session_id_from_request' => $request->input('session_id'),
                'session_id_from_session' => session('guest_session_id'),
                'session_id_final' => $sessionId,
                'has_session' => $request->hasSession(),
            ]);
            
            // Si toujours pas de session_id, le panier sera vide (normal pour un nouvel invité)
            $cartItems = CartItem::getCartItems(null, $sessionId);
            $subtotal = CartItem::getCartTotal(null, $sessionId);
            
            \Log::info('🔍 [CHECKOUT] Panier invité récupéré', [
                'session_id' => $sessionId,
                'cart_items_count' => $cartItems->count(),
                'subtotal' => $subtotal,
            ]);
        }
        
        if ($cartItems->isEmpty()) {
            \Log::warning('⚠️ [CHECKOUT] Panier vide - redirection vers panier', [
                'user_id' => $user?->id,
                'session_id' => $sessionId ?? null,
                'is_authenticated' => (bool)$user,
            ]);
            return redirect()->route('product-cart')->with('error', 'Votre panier est vide');
        }
        
        // Appliquer remise promo éventuelle (affichage checkout)
        $promo = session('promo');
        $discount = 0;
        if ($promo && isset($promo['percent'])) {
            $discount = round($subtotal * ((int)$promo['percent']) / 100);
        }
        
        // Calculer le coût de livraison (comme dans shipping())
        $shippingCostSetting = Setting::get('shipping_cost', 0);
        $freeThreshold = Setting::get('free_shipping_threshold', 0);
        $shippingCost = ($freeThreshold && $subtotal >= $freeThreshold) ? 0 : (float)$shippingCostSetting;
        
        // Calculer le total avec livraison
        $total = max(0, $subtotal - $discount) + $shippingCost;
        
        return view('checkout', compact('user', 'cartItems', 'total', 'subtotal', 'discount', 'promo', 'shippingCost', 'freeThreshold', 'sessionId'));
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
     * Permet l'accès sans connexion - utilise session_id pour le panier invité
     */
    public function shipping(Request $request)
    {
        $user = auth()->user();
        $pendingOrderData = session('pending_order_data');
        
        // Récupérer les articles du panier (utilisateur connecté ou session invité)
        if ($user) {
            $cartItems = CartItem::getCartItems($user->id, null);
            $subtotal = CartItem::getCartTotal($user->id, null);
            $sessionId = null;
        } else {
            // Vérifier qu'on a des données en session
            if (!$pendingOrderData) {
                return redirect()->route('checkout')->with('error', 'Veuillez remplir vos informations de livraison');
            }
            
            // Pour les invités, utiliser le session_id depuis le header ou la requête
            $sessionId = $request->header('X-Session-ID') ?? $request->input('session_id');
            
            // Si pas de session_id fourni, essayer depuis la session Laravel
            if (!$sessionId && $request->hasSession()) {
                $storedSessionId = session('guest_session_id');
                if ($storedSessionId) {
                    $sessionId = $storedSessionId;
                }
            }
            
            $cartItems = CartItem::getCartItems(null, $sessionId);
            $subtotal = CartItem::getCartTotal(null, $sessionId);
        }
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('product-cart')->with('error', 'Votre panier est vide');
        }
        
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
        
        return view('shipping', compact('user', 'cartItems', 'subtotal', 'shippingCost', 'total', 'discount', 'promo', 'pendingOrderData'));
    }

    /**
     * Vérifier l'email et authentifier ou préparer la création de compte
     */
    public function verifyEmailAndAuthenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:10',
            'shipping_country' => 'required|string|max:2',
        ]);

        if ($validator->fails()) {
            \Log::warning('Validation échouée lors de la création de la commande', [
                'errors' => $validator->errors()->toArray(),
                'request' => $request->all(),
                'is_guest' => $isGuest,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($user) {
            // Compte existe - demander le mot de passe
            return response()->json([
                'success' => true,
                'account_exists' => true,
                'message' => 'Veuillez entrer votre mot de passe pour confirmer votre identité',
                'user_id' => $user->id
            ]);
        } else {
            // Compte n'existe pas - stocker les données temporairement et continuer
            // Les données seront utilisées pour créer le compte après la commande
            session([
                'pending_order_data' => [
                    'email' => $email,
                    'shipping_name' => $request->shipping_name,
                    'shipping_phone' => $request->shipping_phone,
                    'shipping_address' => $request->shipping_address,
                    'shipping_city' => $request->shipping_city,
                    'shipping_postal_code' => $request->shipping_postal_code,
                    'shipping_country' => $request->shipping_country,
                ]
            ]);

            return response()->json([
                'success' => true,
                'account_exists' => false,
                'message' => 'Vous pouvez continuer la commande'
            ]);
        }
    }

    /**
     * Créer la commande (API - Tokens OU WEB - Sessions)
     * Supporte maintenant les utilisateurs non connectés
     */
    public function createOrder(Request $request)
    {
        // Support à la fois pour les tokens (API) et les sessions (WEB)
        $user = $request->user() ?? auth()->user();
        
        // Si pas d'utilisateur connecté, vérifier si on a des données en session
        $pendingOrderData = null;
        $isGuest = !$user;
        if ($isGuest) {
            $pendingOrderData = session('pending_order_data');
            if (!$pendingOrderData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expirée. Veuillez recommencer.'
                ], 401);
            }
        }
        
        // Si utilisateur invité, utiliser les données de session pour la validation
        $validationData = $request->all();
        if ($isGuest && $pendingOrderData) {
            // Fusionner les données de session avec celles de la requête (la requête a priorité)
            // Inclure payment_method et customer_notes depuis la requête (choisis sur la page livraison)
            $validationData = array_merge($pendingOrderData, [
                'shipping_name' => $request->shipping_name ?? $pendingOrderData['shipping_name'],
                'shipping_email' => $request->shipping_email ?? $pendingOrderData['email'],
                'shipping_phone' => $request->shipping_phone ?? $pendingOrderData['shipping_phone'],
                'shipping_address' => $request->shipping_address ?? $pendingOrderData['shipping_address'],
                'shipping_city' => $request->shipping_city ?? $pendingOrderData['shipping_city'],
                'shipping_postal_code' => $request->shipping_postal_code ?? $pendingOrderData['shipping_postal_code'] ?? null,
                'shipping_country' => $request->shipping_country ?? $pendingOrderData['shipping_country'],
                'payment_method' => $request->payment_method,
                'customer_notes' => $request->customer_notes ?? null,
            ]);
        }

        // Si utilisateur connecté et corps de requête vide (JSON non reçu), message explicite
        if (!$isGuest && empty($validationData)) {
            \Log::warning('createOrder: requête sans données (utilisateur connecté)', [
                'content_type' => $request->header('Content-Type'),
                'user_id' => $user->id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Les données du formulaire n\'ont pas été reçues. Vérifiez que la page est bien chargée et réessayez.',
                'errors' => ['form' => ['Données manquantes.']]
            ], 422);
        }

        $validator = Validator::make($validationData, [
            'shipping_name' => 'required|string|max:255',
            'shipping_email' => 'required|email|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:10',
            'shipping_country' => 'required|string|max:2',
            'payment_method' => 'required|in:card,mobile_money,cash_on_delivery',
            'customer_notes' => 'nullable|string|max:500'
        ], [
            'shipping_name.required' => 'Le nom complet est obligatoire.',
            'shipping_name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'shipping_email.required' => 'L\'email est obligatoire.',
            'shipping_email.email' => 'L\'email n\'est pas valide.',
            'shipping_phone.required' => 'Le téléphone est obligatoire.',
            'shipping_phone.max' => 'Le téléphone ne doit pas dépasser 20 caractères.',
            'shipping_address.required' => 'L\'adresse de livraison est obligatoire.',
            'shipping_city.required' => 'La ville est obligatoire.',
            'shipping_city.max' => 'La ville ne doit pas dépasser 100 caractères.',
            'shipping_postal_code.max' => 'Le code postal ne doit pas dépasser 10 caractères.',
            'shipping_country.required' => 'Le pays est obligatoire.',
            'shipping_country.max' => 'Le pays doit contenir 2 caractères (ex. CI).',
            'payment_method.required' => 'Veuillez choisir un mode de paiement.',
            'payment_method.in' => 'Le mode de paiement choisi n\'est pas valide.',
            'customer_notes.max' => 'Les notes ne doivent pas dépasser 500 caractères.',
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
            
            // Récupérer le session_id pour les invités (depuis header ou requête)
            $sessionId = null;
            if ($isGuest) {
                $sessionId = $request->header('X-Session-ID') ?? $request->input('session_id');
                if (!$sessionId && $request->hasSession()) {
                    $storedSessionId = session('guest_session_id');
                    if ($storedSessionId) {
                        $sessionId = $storedSessionId;
                    }
                }
            }
            
            // Utiliser les données validées
            $shippingName = $validationData['shipping_name'];
            $shippingEmail = $validationData['shipping_email'];
            $shippingPhone = $validationData['shipping_phone'];
            $shippingAddress = $validationData['shipping_address'];
            $shippingCity = $validationData['shipping_city'];
            $shippingPostalCode = $validationData['shipping_postal_code'] ?? null;
            $shippingCountry = $validationData['shipping_country'];
            $paymentMethod = $validationData['payment_method'];
            $customerNotes = $validationData['customer_notes'] ?? null;
            
            // Récupérer les articles du panier (utilisateur connecté ou session invité)
            if ($user) {
                $cartItems = CartItem::getCartItems($user->id, null);
                $subtotal = CartItem::getCartTotal($user->id, null);
            } else {
                $cartItems = CartItem::getCartItems(null, $sessionId);
                $subtotal = CartItem::getCartTotal(null, $sessionId);
            }
            
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre panier est vide'
                ], 400);
            }
            
            // Calculer les montants
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
            // Calculer le total de manière cohérente avec checkout() et shipping()
            // max(0, ...) évite les totaux négatifs si le discount est supérieur au subtotal
            $total = max(0, $subtotal - $discount) + $shippingCost + $tax;
            
            // Vérifier la disponibilité du stock
            $stockErrors = StockService::checkStockAvailability($cartItems);
            if (!empty($stockErrors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuffisant',
                    'errors' => $stockErrors
                ], 400);
            }
            
            // Si utilisateur invité, créer le compte temporairement (sans mot de passe)
            // Le mot de passe sera défini après la commande
            if ($isGuest && $pendingOrderData) {
                // Extraire nom et prénom du shipping_name
                $nameParts = explode(' ', $shippingName, 2);
                $prenoms = $nameParts[0] ?? '';
                $nom = $nameParts[1] ?? $nameParts[0] ?? '';
                
                // Créer l'utilisateur sans mot de passe (sera défini après)
                $user = User::create([
                    'nom' => $nom,
                    'prenoms' => $prenoms,
                    'email' => $shippingEmail,
                    'telephone' => $shippingPhone,
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)), // Mot de passe temporaire
                    'statut' => 'actif',
                    'is_verified' => false,
                    'termes_condition' => true,
                    'newsletter' => false,
                ]);
                
                // Transférer le panier de session vers l'utilisateur
                CartItem::where('session_id', $sessionId)
                    ->whereNull('user_id')
                    ->update(['user_id' => $user->id, 'session_id' => null]);
                
                // Recharger les cartItems avec le user_id
                $cartItems = CartItem::getCartItems($user->id, null);
                
                // Stocker l'ID utilisateur et l'email dans la session pour la définition du mot de passe
                session([
                    'pending_password_setup' => [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'order_number' => null // Sera rempli après création de la commande
                    ]
                ]);
            }
            
            // Créer la commande
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'shipping_name' => $shippingName,
                'shipping_email' => $shippingEmail,
                'shipping_phone' => $shippingPhone,
                'shipping_address' => $shippingAddress,
                'shipping_city' => $shippingCity,
                'shipping_postal_code' => $shippingPostalCode,
                'shipping_country' => $shippingCountry,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'status' => OrderStatusService::STATUS_PENDING,
                'payment_status' => OrderStatusService::PAYMENT_PENDING,
                'payment_method' => $paymentMethod,
                'customer_notes' => $customerNotes
            ]);
            
            // Mettre à jour le numéro de commande dans la session si nécessaire
            if ($isGuest && session()->has('pending_password_setup')) {
                $pendingPasswordSetup = session('pending_password_setup');
                $pendingPasswordSetup['order_number'] = $order->order_number;
                session(['pending_password_setup' => $pendingPasswordSetup]);
            }
            
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
            
            // Nettoyer les données temporaires de commande
            session()->forget('pending_order_data');
            
            // NE PAS marquer comme payée si paiement à la livraison
            // Le statut de paiement reste "pending" jusqu'à la livraison effective
            
            // Récupérer les paramètres de contact depuis la BD
            $siteEmail = Setting::get('site_email', 'contact@kazaria.ci');
            $sitePhone = Setting::get('contact_phone', Setting::get('site_phone', '+225 07 00 00 00 00'));
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
            
            $pdfFileName = $this->generateInvoiceFileName($order);
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
            
            // Si c'est un nouvel utilisateur, indiquer qu'il doit définir un mot de passe
            $needsPasswordSetup = $isGuest && $user->needs_password_setup ?? false;
            
            // Construire l'URL de redirection
            $redirectUrl = route('order-invoice', $order->order_number);
            $bearerToken = $request->bearerToken();
            if ($bearerToken) {
                $redirectUrl .= '?token=' . $bearerToken;
            }
            
            // Si besoin de définir un mot de passe, ajouter un paramètre
            if ($needsPasswordSetup) {
                $redirectUrl .= (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'setup_password=1';
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Commande créée avec succès',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect' => $redirectUrl,
                'needs_password_setup' => $needsPasswordSetup,
                'user_id' => $user->id,
                'email' => $user->email
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
    public function invoice($orderNumber, Request $request)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'items.variation.attributeValues.attribute', 'user'])
            ->firstOrFail();
        
        // Récupérer les paramètres de contact depuis la BD
        $siteEmail = Setting::get('site_email', 'contact@kazaria.ci');
        $sitePhone = Setting::get('site_phone', '+225 XX XX XX XX XX');
        $siteName = Setting::get('site_name', 'KAZARIA');
        $siteAddress = Setting::get('site_address', 'Côte d\'Ivoire');
        
        // Vérifier si on doit afficher le modal de définition de mot de passe
        $needsPasswordSetup = $request->has('setup_password') && session()->has('pending_password_setup');
        
        return view('invoice', compact('order', 'siteEmail', 'sitePhone', 'siteName', 'siteAddress', 'needsPasswordSetup'));
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
        $sitePhone = Setting::get('contact_phone', Setting::get('site_phone', '+225 07 00 00 00 00'));
        $siteName = Setting::get('site_name', 'KAZARIA');
        $siteAddress = Setting::get('site_address', 'Côte d\'Ivoire');
        
        // Générer et télécharger le PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice-pdf', compact('order', 'siteEmail', 'sitePhone', 'siteName', 'siteAddress'));
        
        $fileName = $this->generateInvoiceFileName($order);
        return $pdf->download($fileName);
    }

    /**
     * Générer le nom de fichier de la facture au format: facture-KAZ-YYYYMMDD-XXXXXX.pdf
     */
    private function generateInvoiceFileName($order)
    {
        // Format de date: YYYYMMDD
        $date = $order->created_at->format('Ymd');
        
        // Générer un code de 6 caractères hexadécimaux basé sur la commande
        // Cela garantit que la même commande aura toujours le même nom de fichier
        $seed = $order->id . $order->order_number . $order->created_at->timestamp;
        $randomCode = strtoupper(substr(md5($seed), 0, 6));
        
        // Format: facture-KAZ-YYYYMMDD-XXXXXX.pdf
        return 'facture-KAZ-' . $date . '-' . $randomCode . '.pdf';
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
        
        $query = Order::forUser($user->id)
            ->with('items');
        
        // Filtrer par statut
        $status = $request->input('status');
        
        // Liste des statuts valides
        $validStatuses = ['pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        
        if ($status === 'all') {
            // Si status === 'all', on ne filtre pas par statut (afficher toutes les commandes)
            // Ne rien faire, laisser passer toutes les commandes
        } elseif ($status && $status !== '' && in_array($status, $validStatuses)) {
            // Filtrer par le statut spécifié si c'est un statut valide
            $query->where('status', $status);
        } elseif ($status === '' || $status === null) {
            // Par défaut (status vide ou null), afficher seulement les commandes en cours
            $query->whereIn('status', ['pending', 'processing']);
        }
        // Si le statut n'est pas valide et n'est pas 'all', on ne filtre pas (afficher toutes les commandes)
        
        // Filtrer par date
        $dateFilter = $request->input('date');
        if ($dateFilter) {
            $now = now();
            switch ($dateFilter) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'week':
                    $query->where('created_at', '>=', $now->copy()->subWeek());
                    break;
                case 'month':
                    $query->whereMonth('created_at', $now->month)
                          ->whereYear('created_at', $now->year);
                    break;
                case '3months':
                    $query->where('created_at', '>=', $now->copy()->subMonths(3));
                    break;
                case 'year':
                    $query->whereYear('created_at', $now->year);
                    break;
            }
        }
        
        $orders = $query->recent()->get();
        
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
     * Définir le mot de passe après création de compte via commande
     */
    public function setupPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($request->user_id);
            
            // Vérifier que c'est bien un compte qui nécessite la définition du mot de passe
            $pendingPasswordSetup = session('pending_password_setup');
            if (!$pendingPasswordSetup || $pendingPasswordSetup['user_id'] != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session invalide ou expirée'
                ], 403);
            }

            // Définir le mot de passe
            $user->update([
                'password' => \Illuminate\Support\Facades\Hash::make($request->password)
            ]);

            // Nettoyer la session
            session()->forget('pending_password_setup');

            // Connecter automatiquement l'utilisateur
            \Illuminate\Support\Facades\Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe défini avec succès. Vous êtes maintenant connecté.',
                'redirect' => route('order-invoice', $pendingPasswordSetup['order_number'] ?? '')
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur définition mot de passe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la définition du mot de passe'
            ], 500);
        }
    }

    /**
     * Authentifier avec mot de passe pour commande (compte existant)
     */
    public function authenticateForOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun compte trouvé avec cet email'
            ], 404);
        }

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe incorrect'
            ], 401);
        }

        // Connecter l'utilisateur
        \Illuminate\Support\Facades\Auth::login($user);

        // Transférer le panier de session vers l'utilisateur
        $sessionId = $request->session()->getId();
        $guestCartItems = CartItem::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->get();

        foreach ($guestCartItems as $item) {
            // Vérifier si l'utilisateur a déjà ce produit dans son panier
            $existingItem = CartItem::where('user_id', $user->id)
                ->where('product_id', $item->product_id)
                ->where('variation_id', $item->variation_id)
                ->where('attributes', $item->attributes)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $item->quantity;
                $existingItem->save();
                $item->delete();
            } else {
                $item->user_id = $user->id;
                $item->session_id = null;
                $item->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Authentification réussie',
            'user' => $user->only(['id', 'nom', 'prenoms', 'email'])
        ]);
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
