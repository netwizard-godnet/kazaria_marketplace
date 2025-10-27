<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Message;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HeaderController extends Controller
{
    public function __construct()
    {
        // Temporairement désactivé pour debug
        // $this->middleware('auth');
    }
    /**
     * Recherche globale dans l'admin
     */
    public function search(Request $request): JsonResponse
    {
        try {
            // Vérification d'authentification manuelle (optionnelle)
            // if (!auth()->check() || !auth()->user()->is_admin) {
            //     return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
            // }

            $query = $request->get('q', '');
            $results = [];

            // Recherche dans les produits
            try {
                $products = \DB::table('products')
                    ->where('name', 'like', "%{$query}%")
                    ->limit(3)
                    ->get(['id', 'name', 'price']);

                if ($products->count() > 0) {
                    $results['products'] = $products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'sku' => 'N/A',
                            'price' => number_format($product->price, 0, ',', ' ') . ' FCFA',
                            'image' => '/images/no-image.png',
                            'url' => '/admin/products/' . $product->id
                        ];
                    });
                }
            } catch (\Exception $e) {
                \Log::error('Error searching products: ' . $e->getMessage());
            }

            // Recherche dans les commandes
            try {
                $orders = \DB::table('orders')
                    ->where('order_number', 'like', "%{$query}%")
                    ->limit(3)
                    ->get(['id', 'order_number', 'total']);

                if ($orders->count() > 0) {
                    $results['orders'] = $orders->map(function ($order) {
                        return [
                            'id' => $order->id,
                            'order_number' => $order->order_number,
                            'customer_name' => 'Client',
                            'customer_email' => 'N/A',
                            'status' => 'pending',
                            'total' => number_format($order->total, 0, ',', ' ') . ' FCFA',
                            'url' => '/admin/orders/' . $order->id
                        ];
                    });
                }
            } catch (\Exception $e) {
                \Log::error('Error searching orders: ' . $e->getMessage());
            }

            // Recherche dans les utilisateurs
            try {
                $users = \DB::table('users')
                    ->where('prenoms', 'like', "%{$query}%")
                    ->orWhere('nom', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->limit(3)
                    ->get(['id', 'prenoms', 'nom', 'email']);

                if ($users->count() > 0) {
                    $results['users'] = $users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => trim($user->prenoms . ' ' . $user->nom),
                            'email' => $user->email,
                            'avatar' => null,
                            'url' => '/admin/users/' . $user->id
                        ];
                    });
                }
            } catch (\Exception $e) {
                \Log::error('Error searching users: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'total' => collect($results)->sum(fn($items) => count($items))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les notifications non lues
     */
    public function getNotifications(): JsonResponse
    {
        $notifications = Notification::forUser(auth()->id())
            ->unread()
            ->notExpired()
            ->recent()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'priority' => $notification->priority,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'icon' => $this->getNotificationIcon($notification->type),
                    'color' => $this->getNotificationColor($notification->priority)
                ];
            }),
            'count' => $notifications->count()
        ]);
    }

    /**
     * Récupérer les messages non lus
     */
    public function getMessages(): JsonResponse
    {
        $messages = Message::where('receiver_id', auth()->id())
            ->unread()
            ->recent()
            ->with('sender')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'subject' => $message->subject,
                    'body' => \Str::limit($message->body, 100),
                    'sender_name' => $message->sender ? trim($message->sender->prenoms . ' ' . $message->sender->nom) : 'Utilisateur inconnu',
                    'sender_avatar' => $message->sender && $message->sender->profile_pic_url 
                        ? asset('storage/' . $message->sender->profile_pic_url) 
                        : null,
                    'created_at' => $message->created_at->diffForHumans(),
                    'priority' => $message->priority
                ];
            }),
            'count' => $messages->count()
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markNotificationAsRead(Request $request): JsonResponse
    {
        $notificationId = $request->get('notification_id');
        
        if ($notificationId) {
            $notification = Notification::where('id', $notificationId)
                ->where('user_id', auth()->id())
                ->first();
            
            if ($notification) {
                $notification->markAsRead();
            }
        } else {
            // Marquer toutes les notifications comme lues
            Notification::forUser(auth()->id())
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Marquer un message comme lu
     */
    public function markMessageAsRead(Request $request): JsonResponse
    {
        $messageId = $request->get('message_id');
        
        if ($messageId) {
            $message = Message::where('id', $messageId)
                ->where('receiver_id', auth()->id())
                ->first();
            
            if ($message) {
                $message->markAsRead();
            }
        } else {
            // Marquer tous les messages comme lus
            Message::where('receiver_id', auth()->id())
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now()
                ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Obtenir l'icône pour le type de notification
     */
    private function getNotificationIcon($type)
    {
        $icons = [
            'order' => 'fa-shopping-cart',
            'payment' => 'fa-credit-card',
            'user' => 'fa-user-plus',
            'product' => 'fa-box',
            'system' => 'fa-cog',
            'warning' => 'fa-exclamation-triangle',
            'success' => 'fa-check-circle',
            'error' => 'fa-times-circle'
        ];

        return $icons[$type] ?? 'fa-bell';
    }

    /**
     * Obtenir la couleur pour la priorité de notification
     */
    private function getNotificationColor($priority)
    {
        $colors = [
            1 => 'primary',
            2 => 'warning',
            3 => 'danger'
        ];

        return $colors[$priority] ?? 'primary';
    }
}