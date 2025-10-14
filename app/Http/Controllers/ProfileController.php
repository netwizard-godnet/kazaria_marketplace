<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\ProductView;
use App\Models\CartItem;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ProfileController extends Controller
{
    /**
     * Afficher la page de profil
     */
    public function index(Request $request)
    {
        // L'utilisateur est maintenant authentifié via le middleware
        $user = $request->user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Utilisateur non authentifié.');
        }

        // Statistiques de l'utilisateur
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'total_favorites' => Favorite::where('user_id', $user->id)->count(),
            'total_reviews' => 0, // À implémenter avec une table reviews
        ];

        // Produits récemment vus
        $recentProducts = \App\Models\ProductView::getRecentViews(6, null);

        return view('profil', compact('user', 'stats', 'recentProducts'));
    }

    /**
     * Mettre à jour les informations du profil
     */
    public function update(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'prenoms' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'telephone' => 'required|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:100',
            'pays' => 'nullable|string|max:2',
            'bio' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->update([
                'prenoms' => $request->prenoms,
                'nom' => $request->nom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'adresse' => $request->adresse,
                'code_postal' => $request->code_postal,
                'ville' => $request->ville,
                'pays' => $request->pays,
                'bio' => $request->bio,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'adresse', 'code_postal', 'ville', 'pays', 'bio'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil'
            ], 500);
        }
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe actuel incorrect'
            ], 422);
        }

        try {
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du mot de passe'
            ], 500);
        }
    }

    /**
     * Mettre à jour la photo de profil
     */
    public function updatePhoto(Request $request)
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120', // 5 MB max
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                
                // Créer un nom de fichier unique
                $filename = 'profile_' . $user->id . '_' . time() . '.' . $photo->getClientOriginalExtension();
                
                // Créer le dossier s'il n'existe pas
                $uploadPath = public_path('images/profiles');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                
                // Supprimer l'ancienne photo si elle existe
                if ($user->profile_pic_url && file_exists(public_path($user->profile_pic_url))) {
                    unlink(public_path($user->profile_pic_url));
                }
                
                // Déplacer le fichier
                $photo->move($uploadPath, $filename);
                
                // Mettre à jour l'URL de la photo dans la base de données
                $photoUrl = 'images/profiles/' . $filename;
                $user->update([
                    'profile_pic_url' => $photoUrl
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Photo de profil mise à jour avec succès',
                    'photo_url' => asset($photoUrl),
                    'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'profile_pic_url'])
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Aucune photo fournie'
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Erreur upload photo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload de la photo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer l'utilisateur actuel depuis le token
     */
    private function getCurrentUser()
    {
        $token = request()->bearerToken();
        
        if (!$token) {
            // Essayer de récupérer depuis localStorage côté client
            return null;
        }

        $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        
        if (!$personalAccessToken) {
            return null;
        }

        return $personalAccessToken->tokenable;
    }

    /**
     * Obtenir l'activité récente de l'utilisateur
     */
    public function getRecentActivity(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        try {
            $activities = [];

            // 1. Dernières commandes (limit 5)
            $recentOrders = Order::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($recentOrders as $order) {
                $activities[] = [
                    'type' => 'order',
                    'title' => 'Commande passée',
                    'description' => "Commande #{$order->order_number} pour un total de " . number_format($order->total, 0, ',', ' ') . " FCFA",
                    'time_ago' => $this->getTimeAgo($order->created_at),
                    'created_at' => $order->created_at
                ];
            }

            // 2. Derniers favoris ajoutés (limit 10)
            $recentFavorites = Favorite::where('user_id', $user->id)
                ->with('product')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($recentFavorites as $favorite) {
                if ($favorite->product) {
                    $activities[] = [
                        'type' => 'favorite',
                        'title' => 'Produit ajouté aux favoris',
                        'description' => $favorite->product->name,
                        'time_ago' => $this->getTimeAgo($favorite->created_at),
                        'created_at' => $favorite->created_at
                    ];
                }
            }

            // 3. Derniers produits consultés (limit 10)
            $recentViews = ProductView::where('user_id', $user->id)
                ->with('product')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($recentViews as $view) {
                if ($view->product) {
                    $activities[] = [
                        'type' => 'view',
                        'title' => 'Produit consulté',
                        'description' => $view->product->name,
                        'time_ago' => $this->getTimeAgo($view->created_at),
                        'created_at' => $view->created_at
                    ];
                }
            }

            // Trier toutes les activités par date décroissante
            usort($activities, function($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });

            // Limiter à 20 activités les plus récentes
            $activities = array_slice($activities, 0, 20);

            // Retirer le champ created_at avant de retourner
            foreach ($activities as &$activity) {
                unset($activity['created_at']);
            }

            return response()->json([
                'success' => true,
                'activities' => $activities
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'activité'
            ], 500);
        }
    }

    /**
     * Calculer le temps écoulé depuis une date
     */
    private function getTimeAgo($datetime)
    {
        $carbon = Carbon::parse($datetime);
        $now = Carbon::now();
        
        $diff = $carbon->diff($now);
        
        if ($diff->y > 0) {
            return $diff->y == 1 ? 'Il y a 1 an' : "Il y a {$diff->y} ans";
        }
        
        if ($diff->m > 0) {
            return $diff->m == 1 ? 'Il y a 1 mois' : "Il y a {$diff->m} mois";
        }
        
        if ($diff->d > 0) {
            return $diff->d == 1 ? 'Il y a 1 jour' : "Il y a {$diff->d} jours";
        }
        
        if ($diff->h > 0) {
            return $diff->h == 1 ? 'Il y a 1 heure' : "Il y a {$diff->h} heures";
        }
        
        if ($diff->i > 0) {
            return $diff->i == 1 ? 'Il y a 1 minute' : "Il y a {$diff->i} minutes";
        }
        
        return "À l'instant";
    }

    /**
     * Vérifier le statut de vendeur de l'utilisateur
     */
    public function checkSellerStatus(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'is_seller' => $user->is_seller,
            'has_store' => $user->store()->exists(),
            'store_status' => $user->store ? $user->store->status : null,
        ]);
    }
}