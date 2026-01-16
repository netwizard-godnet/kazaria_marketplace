<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Product;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\ProductView;
use App\Models\CartItem;
use App\Models\CrmTicket;
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
        // L'utilisateur est authentifié via le middleware auth.redirect
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Utilisateur non authentifié.');
        }

        // Statistiques de l'utilisateur
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'total_favorites' => Favorite::where('user_id', $user->id)->count(),
            'total_reviews' => \App\Models\Review::where('user_id', $user->id)->count(),
        ];

        // Calculer la note de l'utilisateur
        // Si l'utilisateur est vendeur, utiliser la note de sa boutique
        // Sinon, utiliser la moyenne des notes des avis qu'il a donnés
        $userRating = null;
        if ($user->is_seller && $user->store) {
            // Note basée sur la boutique (moyenne des notes des produits)
            $userRating = $user->store->rating ?? 0;
        } else {
            // Note basée sur les avis donnés par l'utilisateur (si applicable)
            // Pour l'instant, on utilise la note de la boutique si vendeur, sinon null
            $userRating = null;
        }
        
        // Si pas de note, utiliser une valeur par défaut ou ne pas afficher
        if ($userRating === null || $userRating == 0) {
            $userRating = null; // Pas de note disponible
        }

        // Produits récemment vus
        $recentProducts = \App\Models\ProductView::getRecentViews(6, null);

        $tickets = CrmTicket::forUser($user->id)
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at')->with('author:id,nom,prenoms');
            }])
            ->orderByDesc('updated_at')
            ->get();

        return view('profil', compact('user', 'stats', 'recentProducts', 'tickets', 'userRating'));
    }

    /**
     * Mettre à jour les informations du profil (WEB - Sessions)
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
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
     * Changer le mot de passe (WEB - Sessions)
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();
        
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
            $oldPasswordHash = $user->getAuthPassword();
            
            // Mettre à jour le mot de passe
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);
            
            // ⚠️ CRITIQUE : Mettre à jour password_hash_web dans la session actuelle
            // pour éviter que AuthenticateSession ne déconnecte l'utilisateur
            if ($request->hasSession()) {
                $request->session()->put('password_hash_web', $user->getAuthPassword());
                $request->session()->save();
                
                \Log::info('✅ [PASSWORD CHANGE] Mot de passe changé - Session actuelle mise à jour', [
                    'user_id' => $user->id,
                    'session_id' => substr($request->session()->getId(), 0, 15) . '...',
                ]);
            }
            
            // Supprimer tous les tokens Sanctum pour forcer la reconnexion sur les autres appareils
            // La session actuelle reste active car password_hash_web a été mis à jour
            $user->tokens()->delete();
            
            \Log::info('✅ [PASSWORD CHANGE] Tous les tokens invalidés', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès. Tous vos autres appareils ont été déconnectés pour des raisons de sécurité.'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ [PASSWORD CHANGE] Erreur lors du changement de mot de passe', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du mot de passe'
            ], 500);
        }
    }

    /**
     * Mettre à jour le statut de l'authentification à deux facteurs (WEB - Sessions)
     */
    public function updateTwoFactor(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'two_factor_enabled' => 'required|boolean',
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
                'two_factor_enabled' => $request->boolean('two_factor_enabled')
            ]);

            $message = $user->two_factor_enabled 
                ? 'Authentification à deux facteurs activée avec succès'
                : 'Authentification à deux facteurs désactivée avec succès';

            return response()->json([
                'success' => true,
                'message' => $message,
                'two_factor_enabled' => $user->two_factor_enabled
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour 2FA: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'authentification à deux facteurs'
            ], 500);
        }
    }

    /**
     * Déconnecter tous les appareils de l'utilisateur (WEB - Sessions)
     */
    public function logoutAllDevices(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        try {
            // Supprimer tous les tokens Sanctum de l'utilisateur
            $user->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tous les appareils ont été déconnectés avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la déconnexion de tous les appareils: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion'
            ], 500);
        }
    }

    /**
     * Mettre à jour la photo de profil (WEB - Sessions)
     */
    public function updatePhoto(Request $request)
    {
        $user = auth()->user();
        
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
                
                // Supprimer l'ancienne photo si chemin relatif et fichier existant
                $old = $user->profile_pic_url;
                if ($old && !preg_match('/^https?:\/\//i', $old)) {
                    $oldPath = public_path($old);
                    if ($old && File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
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
     * Obtenir l'activité récente de l'utilisateur (WEB - Sessions)
     */
    public function getRecentActivity(Request $request)
    {
        $user = auth()->user();
        
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
     * Mettre à jour le profil (API - Tokens)
     */
    public function updateApi(Request $request)
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
     * Changer le mot de passe (API - Tokens)
     */
    public function changePasswordApi(Request $request)
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

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mot de passe actuel incorrect'
            ], 422);
        }

        try {
            // Mettre à jour le mot de passe
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);
            
            // ⚠️ CRITIQUE : Pour les requêtes API avec token, on ne peut pas mettre à jour la session
            // car il n'y a pas de session web. On supprime simplement tous les tokens.
            // L'utilisateur devra se reconnecter avec le nouveau mot de passe.
            $user->tokens()->delete();
            
            \Log::info('✅ [PASSWORD CHANGE API] Mot de passe changé - Tous les tokens invalidés', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès. Veuillez vous reconnecter avec votre nouveau mot de passe.'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ [PASSWORD CHANGE API] Erreur lors du changement de mot de passe', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du mot de passe'
            ], 500);
        }
    }

    /**
     * Mettre à jour la photo (API - Tokens)
     */
    public function updatePhotoApi(Request $request)
    {
        // Authentification Sanctum (tokens)
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120', // 5 MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation: ' . $validator->errors()->first('photo'),
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('photo')) {
            try {
                $file = $request->file('photo');
                
                // Créer un nom de fichier unique
                $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $storagePath = 'profiles';
                
                // S'assurer que le dossier existe dans storage/app/public/profiles
                if (!Storage::disk('public')->exists($storagePath)) {
                    Storage::disk('public')->makeDirectory($storagePath);
                }
                
                // Supprimer l'ancienne photo si elle existe (stockée en public)
                $old = $user->profile_pic_url;
                if ($old) {
                    // Si chemin relatif (images/profiles/...)
                    if (strpos($old, 'images/profiles/') !== false) {
                        $oldFile = str_replace('images/profiles/', '', basename($old));
                        if (Storage::disk('public')->exists($storagePath . '/' . $oldFile)) {
                            Storage::disk('public')->delete($storagePath . '/' . $oldFile);
                        }
                    }
                    // Si déjà dans storage/profiles
                    elseif (strpos($old, 'profiles/') !== false) {
                        $oldFile = basename($old);
                        if (Storage::disk('public')->exists($storagePath . '/' . $oldFile)) {
                            Storage::disk('public')->delete($storagePath . '/' . $oldFile);
                        }
                    }
                }
                
                // Stocker le fichier dans storage/app/public/profiles
                $path = $file->storeAs($storagePath, $filename, 'public');
                
                // Mettre à jour l'URL de la photo dans la base de données
                // Le chemin sera accessible via storage/profiles/filename
                $photoUrl = 'storage/' . $path;
                $user->update([
                    'profile_pic_url' => $photoUrl
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Photo de profil mise à jour avec succès',
                    'photo_url' => asset($photoUrl),
                    'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'profile_pic_url'])
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur upload photo profil', [
                    'user_id' => $user->id ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'upload de la photo: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Aucune photo fournie'
        ], 422);
    }

    /**
     * Récupérer les tickets de support (API - Tokens)
     */
    public function getInboxApi(Request $request)
    {
        // Forcer la réponse JSON pour les requêtes API
        $request->headers->set('Accept', 'application/json');
        
        // Support à la fois session et token
        $user = auth()->user() ?? $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401)->header('Content-Type', 'application/json');
        }

        try {
            $tickets = CrmTicket::forUser($user->id)
                ->with(['messages' => function ($query) {
                    $query->where('is_internal', false)
                        ->orderBy('created_at')
                        ->with('author:id,nom,prenoms');
                }])
                ->orderByDesc('updated_at')
                ->get()
                ->map(function ($ticket) {
                    $latestMessage = $ticket->messages->where('is_internal', false)->sortByDesc('created_at')->first();
                    
                    return [
                        'id' => $ticket->id,
                        'ticket_number' => $ticket->ticket_number,
                        'subject' => $ticket->subject,
                        'description' => $ticket->description,
                        'status' => $ticket->status,
                        'priority' => $ticket->priority,
                        'created_at' => $ticket->created_at?->format('Y-m-d H:i:s'),
                        'updated_at' => $ticket->updated_at?->format('Y-m-d H:i:s'),
                        'messages_count' => $ticket->messages->where('is_internal', false)->count(),
                        'latest_message' => $latestMessage ? [
                            'message' => $latestMessage->message,
                            'created_at' => $latestMessage->created_at?->format('Y-m-d H:i:s'),
                            'author' => $latestMessage->author ? [
                                'nom' => $latestMessage->author->nom,
                                'prenoms' => $latestMessage->author->prenoms,
                            ] : null,
                        ] : null,
                        'has_unread' => false, // TODO: Implémenter la logique de lecture
                    ];
                })
                ->values()
                ->toArray();

            return response()->json([
                'success' => true,
                'tickets' => $tickets,
                'count' => count($tickets)
            ])->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            \Log::error('Erreur récupération tickets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des messages'
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Activité récente (API - Tokens)
     */
    public function getRecentActivityApi(Request $request)
    {
        // Forcer la réponse JSON pour les requêtes API
        $request->headers->set('Accept', 'application/json');
        
        // Support à la fois session et token
        $user = auth()->user() ?? $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401)->header('Content-Type', 'application/json');
        }

        // Récupérer les activités récentes
        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();
            
        $recentFavorites = Favorite::where('user_id', $user->id)
            ->with('product')
            ->latest()
            ->take(5)
            ->get();

        // Construire la liste des activités avec les timestamps pour le tri
        $activities = [];
        
        // Ajouter les commandes récentes
        foreach ($recentOrders as $order) {
            $activities[] = [
                'type' => 'order',
                'title' => 'Nouvelle commande',
                'description' => "Commande #{$order->order_number} pour " . number_format($order->total, 0, ',', ' ') . " FCFA",
                'date' => $order->created_at->diffForHumans(),
                'timestamp' => $order->created_at->timestamp, // Pour le tri
                'icon' => 'bag'
            ];
        }
        
        // Ajouter les favoris récents
        foreach ($recentFavorites as $favorite) {
            if ($favorite->product) { // Ne pas inclure les favoris sans produit
            $activities[] = [
                'type' => 'favorite',
                'title' => 'Produit ajouté aux favoris',
                'description' => $favorite->product->name ?? 'Produit inconnu',
                'date' => $favorite->created_at->diffForHumans(),
                    'timestamp' => $favorite->created_at->timestamp, // Pour le tri
                'icon' => 'heart'
            ];
            }
        }
        
        // Trier par timestamp (plus récent en premier)
        usort($activities, function($a, $b) {
            return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
        });
        
        // Retirer le timestamp avant de retourner (pas nécessaire côté client)
        $activities = array_map(function($activity) {
            unset($activity['timestamp']);
            return $activity;
        }, $activities);

        return response()->json([
            'success' => true,
            'activities' => $activities
        ])->header('Content-Type', 'application/json');
    }

    /**
     * Vérifier le statut de vendeur de l'utilisateur (API - Tokens)
     */
    public function checkSellerStatus(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'is_seller' => $user->is_seller,
            'has_store' => $user->store()->exists(),
            'store_status' => $user->store ? $user->store->effective_kyc_status : null,
        ]);
    }

    /**
     * Demander l'envoi d'un email de vérification (WEB - Sessions)
     */
    public function requestEmailVerification(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        // Vérifier si l'email est déjà vérifié
        if ($user->is_verified && $user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Votre email est déjà vérifié.'
            ], 422);
        }

        try {
            // Créer un nouveau token de vérification
            $verificationToken = \Illuminate\Support\Str::random(64);
            $user->update(['email_verification_token' => $verificationToken]);

            // URL de vérification
            $verificationUrl = route('verify-email', ['token' => $verificationToken]);

            // Envoyer l'email de vérification
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\VerifyEmailMail($user, $verificationUrl));

            return response()->json([
                'success' => true,
                'message' => 'Email de vérification envoyé avec succès. Veuillez vérifier votre boîte de réception.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur envoi email vérification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email de vérification. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Demander l'envoi d'un email de vérification (API - Tokens)
     */
    public function requestEmailVerificationApi(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        // Vérifier si l'email est déjà vérifié
        if ($user->is_verified && $user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Votre email est déjà vérifié.'
            ], 422);
        }

        try {
            // Créer un nouveau token de vérification
            $verificationToken = \Illuminate\Support\Str::random(64);
            $user->update(['email_verification_token' => $verificationToken]);

            // URL de vérification
            $verificationUrl = route('verify-email', ['token' => $verificationToken]);

            // Envoyer l'email de vérification
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\VerifyEmailMail($user, $verificationUrl));

            return response()->json([
                'success' => true,
                'message' => 'Email de vérification envoyé avec succès. Veuillez vérifier votre boîte de réception.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur envoi email vérification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email de vérification. Veuillez réessayer.'
            ], 500);
        }
    }
}