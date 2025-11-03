<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Convertir un numéro de commande masqué en ID réel
     */
    private function unmaskOrderNumber($maskedNumber)
    {
        // Si c'est un format CMD-XXXXXX, extraire l'ID
        if (preg_match('/^CMD-(\d+)$/', $maskedNumber, $matches)) {
            return (int) $matches[1];
        }
        // Sinon, retourner le numéro tel quel (ancien format)
        return $maskedNumber;
    }
    
    /**
     * Récupérer les commandes de la boutique
     */
    public function getOrders(Request $request)
    {
        // Support pour l'authentification par session et par token
        $user = auth()->user() ?? $request->user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Utilisateur non authentifié'], 401);
        }
        
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        try {
            // Récupérer les commandes qui contiennent des produits de cette boutique
            $query = Order::query()
                ->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })
                ->with(['orderItems.product' => function($q) use ($store) {
                    $q->where('store_id', $store->id);
                }, 'user']);

            // Filtres
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('shipping_name', 'like', "%{$search}%")
                      ->orWhere('shipping_email', 'like', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $orders = $query->paginate($perPage);

            // Formater les données
            $formattedOrders = $orders->map(function($order) use ($store) {
                // Calculer le total pour cette boutique seulement
                $storeTotal = $order->orderItems
                    ->where('product.store_id', $store->id)
                    ->sum('total');

                return [
                    'id' => $order->id,
                    'order_number' => 'CMD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT), // Numéro masqué
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    // Masquer les informations sensibles du client
                    'shipping_name' => 'Client KAZARIA',
                    'shipping_email' => 'client@kazaria.com',
                    'shipping_phone' => '***',
                    'shipping_address' => '***',
                    'shipping_city' => '***',
                    'total' => $storeTotal,
                    'items_count' => $order->orderItems->where('product.store_id', $store->id)->count(),
                    'items' => $order->orderItems->where('product.store_id', $store->id)->map(function($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name,
                            'product_image' => $item->product_image,
                            'price' => $item->price,
                            'quantity' => $item->quantity,
                            'total' => $item->total,
                            'product' => $item->product ? [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'slug' => $item->product->slug,
                                'image' => $item->product->image
                            ] : null
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'orders' => $formattedOrders,
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération commandes vendeur: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des commandes'
            ], 500);
        }
    }

    /**
     * Récupérer les commandes récentes
     */
    public function getRecentOrders(Request $request)
    {
        $user = auth()->user() ?? $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $limit = $request->get('limit', 5);

        try {
            $orders = Order::query()
                ->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })
                ->with(['orderItems.product' => function($q) use ($store) {
                    $q->where('store_id', $store->id);
                }])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $formattedOrders = $orders->map(function($order) use ($store) {
                $storeTotal = $order->orderItems
                    ->where('product.store_id', $store->id)
                    ->sum('total');

                return [
                    'id' => $order->id,
                    'order_number' => 'CMD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT), // Numéro masqué
                    'created_at' => $order->created_at,
                    'status' => $order->status,
                    // Masquer les informations sensibles du client
                    'shipping_name' => 'Client KAZARIA',
                    'total' => $storeTotal,
                    'items_count' => $order->orderItems->where('product.store_id', $store->id)->count()
                ];
            });

            return response()->json([
                'success' => true,
                'orders' => $formattedOrders
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération commandes récentes: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des commandes récentes'
            ], 500);
        }
    }

    /**
     * Détails d'une commande
     */
    public function getOrderDetails(Request $request, $orderNumber)
    {
        $user = auth()->user() ?? $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        try {
            // Vérifier si c'est un numéro masqué (CMD-XXXXXX)
            $orderId = $this->unmaskOrderNumber($orderNumber);
            
            // Rechercher par ID si c'est un numéro masqué, sinon par order_number
            $order = $orderId !== $orderNumber 
                ? Order::where('id', $orderId)->with(['orderItems.product', 'user'])->first()
                : Order::where('order_number', $orderNumber)->with(['orderItems.product', 'user'])->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande non trouvée'
                ], 404);
            }

            // Filtrer les articles qui appartiennent à cette boutique
            $storeItems = $order->orderItems->filter(function($item) use ($store) {
                return $item->product && $item->product->store_id == $store->id;
            });
            
            // Calculer le total pour cette boutique seulement
            $storeSubtotal = $storeItems->sum('total');
            
            // Calculer le total de la commande complète
            $orderTotalSubtotal = $order->orderItems->sum('total');
            
            // Calculer le pourcentage que représente cette boutique dans le total
            $percentageOfTotal = $orderTotalSubtotal > 0 ? $storeSubtotal / $orderTotalSubtotal : 0;
            
            // Appliquer ce pourcentage aux frais de livraison, taxes et remises
            $storeShippingCost = $order->shipping_cost * $percentageOfTotal;
            $storeTax = $order->tax * $percentageOfTotal;
            $storeDiscount = $order->discount * $percentageOfTotal;
            
            // Calculer le total final pour cette boutique
            $storeTotal = $storeSubtotal + $storeShippingCost + $storeTax - $storeDiscount;

            $formattedOrder = [
                'id' => $order->id,
                'order_number' => 'CMD-' . str_pad($order->id, 6, '0', STR_PAD_LEFT), // Numéro masqué
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                // Masquer les informations sensibles du client
                'shipping_name' => 'Client KAZARIA',
                'shipping_email' => 'client@kazaria.com',
                'shipping_phone' => '***',
                'shipping_address' => '***',
                'shipping_city' => '***',
                'shipping_postal_code' => '***',
                'shipping_country' => '***',
                'customer_notes' => $order->customer_notes, // Garder les notes client pour contexte
                'total' => $storeTotal,
                'subtotal' => $storeSubtotal,
                'shipping_cost' => $storeShippingCost,
                'tax' => $storeTax,
                'discount' => $storeDiscount,
                'items' => $storeItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'product_image' => $item->product_image,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'total' => $item->total,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'slug' => $item->product->slug,
                            'image' => $item->product->image
                        ] : null
                    ];
                })->values() // Pour garantir que c'est un tableau
            ];

            \Log::info('=== STRUCTURE COMMANDE ENVOYÉE ===', [
                'order_number' => $formattedOrder['order_number'],
                'items_count' => count($formattedOrder['items']),
                'items_type' => gettype($formattedOrder['items']),
                'items_sample' => $formattedOrder['items']
            ]);

            return response()->json([
                'success' => true,
                'order' => $formattedOrder
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération détails commande: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails de la commande'
            ], 500);
        }
    }

    /**
     * Mettre à jour le statut d'une commande
     */
    public function updateOrderStatus(Request $request, $orderNumber)
    {
        $user = auth()->user() ?? $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,delivered,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Vérifier si c'est un numéro masqué (CMD-XXXXXX)
            $orderId = $this->unmaskOrderNumber($orderNumber);
            
            // Rechercher par ID si c'est un numéro masqué, sinon par order_number
            $order = $orderId !== $orderNumber 
                ? Order::where('id', $orderId)->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })->first()
                : Order::where('order_number', $orderNumber)->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande non trouvée'
                ], 404);
            }

            $oldStatus = $order->status;
            // Utiliser le nouveau système de statut
            $order->changeStatus($request->status, $request->notes);

            // Log de l'activité
            \Log::info("Commande {$orderNumber} mise à jour de '{$oldStatus}' vers '{$request->status}' par la boutique {$store->name}");

            return response()->json([
                'success' => true,
                'message' => 'Statut de la commande mis à jour avec succès',
                'order' => [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'updated_at' => $order->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour statut commande: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut'
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des commandes
     */
    public function getOrderStats(Request $request)
    {
        $user = auth()->user() ?? $request->user();
        
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Utilisateur non authentifié'], 401);
        }
        
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        try {
            // Récupérer les commandes de cette boutique
            $ordersQuery = Order::query()
                ->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                });

            // Statistiques générales - recréer la requête pour chaque statut
            $totalOrders = $ordersQuery->count();
            $pendingOrders = Order::query()
                ->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })
                ->where('status', 'pending')->count();
            $processingOrders = Order::query()
                ->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })
                ->where('status', 'processing')->count();
            $deliveredOrders = Order::query()
                ->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })
                ->where('status', 'delivered')->count();
            $cancelledOrders = Order::query()
                ->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })
                ->where('status', 'cancelled')->count();
            $paidOrders = Order::query()
                ->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })
                ->where('payment_status', 'paid')->count();
            $pendingPayment = Order::query()
                ->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })
                ->where('payment_status', 'pending')->count();

            // Calculer le total des ventes pour cette boutique
            $totalSales = Order::query()
                ->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })
                ->where('status', 'delivered')
                ->get()
                ->sum(function($order) use ($store) {
                    return $order->orderItems
                        ->where('product.store_id', $store->id)
                        ->sum('total');
                });

            // Commandes des 30 derniers jours
            $recentOrders = $ordersQuery->where('created_at', '>=', now()->subDays(30))->count();

            // Commandes de cette semaine
            $thisWeekOrders = $ordersQuery->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count();

            // Commandes d'aujourd'hui
            $todayOrders = $ordersQuery->whereDate('created_at', today())->count();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_orders' => $totalOrders,
                    'pending_orders' => $pendingOrders,
                    'processing_orders' => $processingOrders,
                    'delivered_orders' => $deliveredOrders,
                    'cancelled_orders' => $cancelledOrders,
                    'paid_orders' => $paidOrders,
                    'pending_payment' => $pendingPayment,
                    'total_sales' => $totalSales,
                    'recent_orders' => $recentOrders,
                    'this_week_orders' => $thisWeekOrders,
                    'today_orders' => $todayOrders,
                    'total_revenue' => $totalSales * (1 - $store->commission_rate / 100)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération statistiques commandes: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Marquer une commande comme expédiée
     */
    public function markAsShipped(Request $request, $orderNumber)
    {
        $user = auth()->user() ?? $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $request->validate([
            'tracking_number' => 'nullable|string|max:100',
            'shipping_company' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            // Vérifier si c'est un numéro masqué (CMD-XXXXXX)
            $orderId = $this->unmaskOrderNumber($orderNumber);
            
            // Rechercher par ID si c'est un numéro masqué, sinon par order_number
            $order = $orderId !== $orderNumber 
                ? Order::where('id', $orderId)->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })->first()
                : Order::where('order_number', $orderNumber)->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande non trouvée'
                ], 404);
            }

            if ($order->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible d\'expédier une commande annulée'
                ], 400);
            }

            $order->update([
                'status' => 'shipped',
                'updated_at' => now()
            ]);

            // Log de l'expédition
            \Log::info("Commande {$orderNumber} marquée comme expédiée par la boutique {$store->name}");

            return response()->json([
                'success' => true,
                'message' => 'Commande marquée comme expédiée avec succès',
                'order' => [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'updated_at' => $order->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur marquage commande expédiée: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage de la commande'
            ], 500);
        }
    }

    /**
     * Annuler une commande
     */
    public function cancelOrder(Request $request, $orderNumber)
    {
        $user = auth()->user() ?? $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            // Vérifier si c'est un numéro masqué (CMD-XXXXXX)
            $orderId = $this->unmaskOrderNumber($orderNumber);
            
            // Rechercher par ID si c'est un numéro masqué, sinon par order_number
            $order = $orderId !== $orderNumber 
                ? Order::where('id', $orderId)->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })->first()
                : Order::where('order_number', $orderNumber)->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande non trouvée'
                ], 404);
            }

            // Utiliser le nouveau système de statut
            $reason = $request->reason ?? 'Annulation par le vendeur';
            $order->changeStatus(OrderStatusService::STATUS_CANCELLED, $reason);

            // Log de l'annulation
            \Log::info("Commande {$orderNumber} annulée par la boutique {$store->name}. Raison: {$reason}");

            return response()->json([
                'success' => true,
                'message' => 'Commande annulée avec succès',
                'order' => [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'updated_at' => $order->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur annulation commande: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation de la commande'
            ], 500);
        }
    }

    /**
     * Marquer une commande comme livrée
     */
    public function markAsDelivered(Request $request, $orderNumber)
    {
        $user = auth()->user() ?? $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            // Vérifier si c'est un numéro masqué (CMD-XXXXXX)
            $orderId = $this->unmaskOrderNumber($orderNumber);
            
            // Rechercher par ID si c'est un numéro masqué, sinon par order_number
            $order = $orderId !== $orderNumber 
                ? Order::where('id', $orderId)->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })->first()
                : Order::where('order_number', $orderNumber)->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande non trouvée'
                ], 404);
            }

            // Utiliser le nouveau système de statut
            $order->changeStatus(OrderStatusService::STATUS_DELIVERED, $request->reason);

            // Log de la livraison
            \Log::info("Commande {$orderNumber} marquée comme livrée par la boutique {$store->name}. Raison: {$request->reason}");

            return response()->json([
                'success' => true,
                'message' => 'Commande marquée comme livrée avec succès',
                'order' => [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'updated_at' => $order->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur marquage livraison: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage de la livraison'
            ], 500);
        }
    }

    /**
     * Changer le statut de paiement d'une commande
     */
    public function changePaymentStatus(Request $request, $orderNumber)
    {
        $user = auth()->user() ?? $request->user();
        $store = $user->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Boutique non trouvée'], 404);
        }

        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            // Vérifier si c'est un numéro masqué (CMD-XXXXXX)
            $orderId = $this->unmaskOrderNumber($orderNumber);
            
            // Rechercher par ID si c'est un numéro masqué, sinon par order_number
            $order = $orderId !== $orderNumber 
                ? Order::where('id', $orderId)->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })->first()
                : Order::where('order_number', $orderNumber)->whereHas('orderItems.product', function($q) use ($store) {
                    $q->where('store_id', $store->id);
                })->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Commande non trouvée'
                ], 404);
            }

            // Utiliser le nouveau système de statut de paiement
            $order->changePaymentStatus($request->payment_status, $request->reason);

            // Log du changement de statut de paiement
            \Log::info("Statut de paiement de la commande {$orderNumber} changé vers '{$request->payment_status}' par la boutique {$store->name}. Raison: {$request->reason}");

            return response()->json([
                'success' => true,
                'message' => 'Statut de paiement mis à jour avec succès',
                'order' => [
                    'order_number' => $order->order_number,
                    'payment_status' => $order->payment_status,
                    'updated_at' => $order->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur changement statut paiement: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement du statut de paiement'
            ], 500);
        }
    }
}
