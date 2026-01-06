<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuthCode;
use App\Models\CartItem;
use App\Mail\AuthCodeMail;
use App\Mail\VerifyEmailMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Ajouter le cookie de session à une réponse JSON
     * Nécessaire pour les routes API car le middleware StartSession n'est pas appliqué
     * 
     * @param \Illuminate\Http\JsonResponse $response
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function addSessionCookieToResponse($response, $request)
    {
        if (!$request->hasSession()) {
            \Log::warning('addSessionCookieToResponse: Pas de session disponible');
            return $response;
        }

        $session = $request->session();
        
        // S'assurer que la session est sauvegardée avant de créer le cookie
        if (!$session->isStarted()) {
            \Log::warning('addSessionCookieToResponse: Session non démarrée');
            return $response;
        }
        
        // Sauvegarder la session pour s'assurer qu'elle est persistée
        $session->save();
        
        $sessionName = (string)config('session.cookie');
        $sessionId = (string)$session->getId();
        
        if (empty($sessionId)) {
            \Log::error('addSessionCookieToResponse: ID de session vide');
            return $response;
        }
        
        $sessionLifetime = (int)config('session.lifetime') * 60; // Convertir en secondes
        
        // Récupérer la configuration de sécurité
        $secure = config('session.secure');
        if ($secure === null) {
            // Auto-détection : true si HTTPS, false sinon
            $secure = $request->secure();
        }
        
        $cookie = cookie(
            $sessionName,
            $sessionId,
            $sessionLifetime,
            (string)config('session.path', '/'),
            (string)(config('session.domain') ?? ''),
            (bool)$secure,
            (bool)config('session.http_only', true),
            (bool)config('session.partitioned', false),
            (string)(config('session.same_site', 'lax') ?? 'lax')
        );

        \Log::info('🍪 [SESSION COOKIE] Cookie de session créé et envoyé', [
            'session_name' => $sessionName,
            'session_id' => substr($sessionId, 0, 15) . '...',
            'session_id_length' => strlen($sessionId),
            'lifetime_minutes' => config('session.lifetime'),
            'secure' => $secure,
            'same_site' => config('session.same_site', 'lax'),
            'path' => config('session.path', '/'),
            'domain' => config('session.domain'),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return $response->withCookie($cookie);
    }

    /**
     * Inscription d'un nouvel utilisateur
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telephone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'termes_condition' => 'required|accepted',
            'newsletter' => 'boolean',
        ], [
            'nom.required' => 'Le nom est obligatoire',
            'nom.max' => 'Le nom ne peut pas dépasser 255 caractères',
            'prenoms.required' => 'Le prénom est obligatoire',
            'prenoms.max' => 'Le prénom ne peut pas dépasser 255 caractères',
            'email.required' => 'L\'adresse email est obligatoire',
            'email.email' => 'Veuillez saisir une adresse email valide (exemple : nom@exemple.com)',
            'email.unique' => 'Cette adresse email est déjà utilisée. Avez-vous déjà un compte ?',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères',
            'telephone.required' => 'Le numéro de téléphone est obligatoire',
            'telephone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères',
            'password.required' => 'Le mot de passe est obligatoire',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères pour votre sécurité',
            'password.confirmed' => 'Les mots de passe ne correspondent pas. Veuillez vérifier votre saisie',
            'termes_condition.required' => 'Vous devez accepter les conditions d\'utilisation',
            'termes_condition.accepted' => 'Vous devez accepter les conditions d\'utilisation pour continuer',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $formattedErrors = [];
            
            // Formater les erreurs par champ
            foreach ($errors->messages() as $field => $messages) {
                $formattedErrors[$field] = $messages;
            }
            
            // Créer un message principal compréhensible
            $firstError = $errors->first();
            $errorCount = $errors->count();
            $mainMessage = $errorCount === 1 
                ? $firstError 
                : "Veuillez corriger {$errorCount} erreur(s) dans le formulaire";
            
            return response()->json([
                'success' => false,
                'message' => $mainMessage,
                'errors' => $formattedErrors
            ], 422);
        }

        try {
            // Créer l'utilisateur
            $user = User::create([
                'nom' => $request->nom,
                'prenoms' => $request->prenoms,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'password' => Hash::make($request->password),
                'termes_condition' => $request->boolean('termes_condition'),
                'newsletter' => $request->boolean('newsletter'),
                'statut' => 'actif',
                'is_verified' => false,
            ]);

            // Créer un token de vérification
            $verificationToken = Str::random(64);
            $user->update(['email_verification_token' => $verificationToken]);

            // URL de vérification
            $verificationUrl = route('verify-email', ['token' => $verificationToken]);

            // Envoyer l'email de vérification (optionnel)
            Mail::to($user->email)->send(new VerifyEmailMail($user, $verificationUrl));

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès ! Vous pouvez maintenant vous connecter. Un email de vérification a été envoyé à votre adresse email pour devenir un utilisateur vérifié.',
                'user' => $user->only(['id', 'nom', 'prenoms', 'email'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du compte. Veuillez réessayer.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Connexion avec code de vérification
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $messages = [];
            
            if ($errors->has('email')) {
                $messages[] = 'Veuillez saisir une adresse email valide';
            }
            if ($errors->has('password')) {
                $messages[] = 'Le mot de passe est obligatoire';
            }
            
            return response()->json([
                'success' => false,
                'message' => implode('. ', $messages) ?: 'Veuillez remplir tous les champs requis',
                'errors' => $errors->messages()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun compte trouvé avec cette adresse email. Vérifiez votre saisie ou créez un compte.',
                'error_type' => 'email_not_found'
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Le mot de passe est incorrect. Vérifiez votre saisie ou utilisez "Mot de passe oublié" si nécessaire.',
                'error_type' => 'invalid_password'
            ], 401);
        }

        // Détecter si c'est une requête API (mobile/Flutter)
        // Pour les requêtes depuis le frontend web, même si c'est /api/login, on doit utiliser la session
        // Si X-Requested-With est présent, c'est une requête AJAX depuis le frontend web
        $isApiRequest = ($request->expectsJson() || $request->is('api/*')) 
            && !$request->header('X-Requested-With'); // Si X-Requested-With est présent, c'est une requête web

        // Vérifier si l'authentification à deux facteurs est activée
        if (!$user->two_factor_enabled) {
            // Connexion directe sans code si le 2FA n'est pas activé
            try {
                if ($isApiRequest) {
                    // Pour les requêtes API (Flutter/mobile) : créer un token Sanctum
                    $token = $user->createToken('mobile-app')->plainTextToken;
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Connexion réussie',
                        'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'two_factor_enabled']),
                        'token' => $token,
                        'requires_code' => false
                    ]);
                } else {
                    // Pour les requêtes web : utiliser la session
                    // S'assurer que la session est démarrée
                    if (!$request->hasSession()) {
                        $sessionStore = app('session.store');
                        // Lire l'ID de session depuis les cookies si disponible
                        $sessionId = $request->cookies->get($sessionStore->getName());
                        if ($sessionId) {
                            $sessionStore->setId($sessionId);
                        }
                        $request->setLaravelSession($sessionStore);
                    }
                    
                    $session = $request->session();
                    if (!$session->isStarted()) {
                        // ⚠️ IMPORTANT : Lire l'ID de session depuis les cookies AVANT de démarrer
                        // Sinon, une nouvelle session sera créée et l'utilisateur sera déconnecté
                        $sessionId = $request->cookies->get($session->getName());
                        if ($sessionId) {
                            $session->setId($sessionId);
                        }
                        $session->start();
                    }
                    
                    \Log::info('🔐 [LOGIN] Session démarrée pour connexion', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'session_id' => substr($session->getId(), 0, 15) . '...',
                        'session_started' => $session->isStarted(),
                        'cookie_present' => $request->cookies->has($session->getName()),
                        'remember' => $request->has('remember'),
                        'timestamp' => now()->toDateTimeString(),
                    ]);
                    
                    // Connecter l'utilisateur dans la session
                    Auth::login($user, $request->has('remember'));
                    
                    // Régénérer l'ID de session APRÈS le login pour la sécurité
                    // Cela crée une nouvelle session avec l'utilisateur déjà authentifié
                    $oldSessionId = $session->getId();
                    $request->session()->regenerate();
                    $newSessionId = $session->getId();
                    
                    \Log::info('🔄 [LOGIN] Session régénérée après connexion', [
                        'user_id' => $user->id,
                        'old_session_id' => substr($oldSessionId, 0, 15) . '...',
                        'new_session_id' => substr($newSessionId, 0, 15) . '...',
                        'password_hash_stored' => $request->session()->has('password_hash_web'),
                        'timestamp' => now()->toDateTimeString(),
                    ]);
                    
                    // Stocker le hash du mot de passe dans la session APRÈS la régénération
                    // pour que AuthenticateSession puisse vérifier l'authenticité de la session
                    $request->session()->put('password_hash_web', $user->getAuthPassword());
                    
                    // Régénérer le token CSRF
                    $request->session()->regenerateToken();

                    // Fusionner le panier invité avec le panier utilisateur
                    try {
                        $guestSessionId = $request->header('X-Session-ID');
                        // S'assurer que c'est une chaîne de caractères
                        if ($guestSessionId !== null) {
                            $guestSessionId = is_array($guestSessionId) ? ($guestSessionId[0] ?? null) : (string)$guestSessionId;
                            $this->mergeGuestCart($user, $guestSessionId ?: null);
                        }
                    } catch (\Exception $e) {
                        // Ne pas bloquer la connexion si la fusion du panier échoue
                        \Log::error('Erreur lors de la fusion du panier invité: ' . $e->getMessage(), [
                            'user_id' => $user->id,
                            'exception' => $e
                        ]);
                    }

                    // Vérifier que l'utilisateur est bien authentifié avant de sauvegarder
                    if (!Auth::check()) {
                        \Log::error('Login: Utilisateur non authentifié après Auth::login()', [
                            'user_id' => $user->id
                        ]);
                    }

                    // Sauvegarder la session pour s'assurer qu'elle est persistée
                    $request->session()->save();
                    
                    \Log::info('✅ [LOGIN] Connexion réussie - Session sauvegardée', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'session_id' => substr($session->getId(), 0, 15) . '...',
                        'auth_check' => Auth::check(),
                        'password_hash_present' => $request->session()->has('password_hash_web'),
                        'session_started' => $session->isStarted(),
                        'timestamp' => now()->toDateTimeString(),
                    ]);

                    // Créer la réponse JSON
                    $response = response()->json([
                        'success' => true,
                        'message' => 'Connexion réussie',
                        'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'two_factor_enabled']),
                        'requires_code' => false,
                        'redirect' => route('accueil')
                    ]);

                    // Ajouter le cookie de session à la réponse
                    // C'est nécessaire car les routes API n'ont pas le middleware StartSession
                    // qui ajoute automatiquement le cookie
                    return $this->addSessionCookieToResponse($response, $request);
                }
            } catch (\Exception $e) {
                \Log::error('Erreur connexion directe: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la connexion. Veuillez réessayer.'
                ], 500);
            }
        }

        // Si le 2FA est activé, envoyer le code de vérification
        try {
            // Générer et envoyer le code de connexion
            $authCode = AuthCode::createCode($user->email, 'login', $request);
            
            // Envoyer l'email avec le code
            Mail::to($user->email)->send(new AuthCodeMail($authCode->code, 'login', $user->prenoms));

            return response()->json([
                'success' => true,
                'message' => 'Code de connexion envoyé à votre email',
                'email' => $user->email,
                'requires_code' => true
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur envoi code connexion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du code. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Vérification du code de connexion (API - pour Flutter/mobile)
     */
    public function verifyLoginCodeApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:8',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $messages = [];
            
            if ($errors->has('email')) {
                $messages[] = 'L\'adresse email est requise';
            }
            if ($errors->has('code')) {
                if (strlen($request->code ?? '') !== 8) {
                    $messages[] = 'Le code doit contenir exactement 8 chiffres';
                } else {
                    $messages[] = 'Le code de vérification est requis';
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => implode('. ', $messages) ?: 'Veuillez remplir tous les champs requis',
                'errors' => $errors->messages()
            ], 422);
        }

        $authCode = AuthCode::where('email', $request->email)
                           ->where('code', $request->code)
                           ->where('type', 'login')
                           ->unused()
                           ->notExpired()
                           ->first();

        if (!$authCode) {
            // Vérifier si le code existe mais est expiré
            $expiredCode = AuthCode::where('email', $request->email)
                                 ->where('code', $request->code)
                                 ->where('type', 'login')
                                 ->first();
            
            if ($expiredCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce code a expiré. Veuillez demander un nouveau code de connexion.',
                    'error_type' => 'code_expired'
                ], 401);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Le code saisi est incorrect. Vérifiez votre boîte email et réessayez.',
                'error_type' => 'invalid_code'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable'
            ], 404);
        }

        // Marquer le code comme utilisé
        $authCode->markAsUsed();

        // Créer un token Sanctum pour Flutter/mobile
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'two_factor_enabled']),
            'token' => $token
        ]);
    }

    /**
     * Vérification du code de connexion (WEB - Sessions)
     */
    public function verifyLoginCode(Request $request)
    {
        // Vérifier que la session est disponible
        try {
            if (!$request->hasSession()) {
                \Log::error('Session store not set on request for verifyLoginCode');
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de session. Veuillez rafraîchir la page et réessayer.'
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur session verifyLoginCode: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de session. Veuillez rafraîchir la page et réessayer.'
            ], 500);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:8',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $messages = [];
            
            if ($errors->has('email')) {
                $messages[] = 'L\'adresse email est requise';
            }
            if ($errors->has('code')) {
                if (strlen($request->code ?? '') !== 8) {
                    $messages[] = 'Le code doit contenir exactement 8 chiffres';
                } else {
                    $messages[] = 'Le code de vérification est requis';
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => implode('. ', $messages) ?: 'Veuillez remplir tous les champs requis',
                'errors' => $errors->messages()
            ], 422);
        }

        $authCode = AuthCode::where('email', $request->email)
                           ->where('code', $request->code)
                           ->where('type', 'login')
                           ->unused()
                           ->notExpired()
                           ->first();

        if (!$authCode) {
            // Vérifier si le code existe mais est expiré
            $expiredCode = AuthCode::where('email', $request->email)
                                 ->where('code', $request->code)
                                 ->where('type', 'login')
                                 ->first();
            
            if ($expiredCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce code a expiré. Veuillez demander un nouveau code de connexion.',
                    'error_type' => 'code_expired'
                ], 401);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Le code saisi est incorrect. Vérifiez votre boîte email et réessayez.',
                'error_type' => 'invalid_code'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable'
            ], 404);
        }

        // Marquer le code comme utilisé
        $authCode->markAsUsed();

        // S'assurer que la session est démarrée
        if (!$request->hasSession()) {
            $sessionStore = app('session.store');
            // Lire l'ID de session depuis les cookies si disponible
            $sessionId = $request->cookies->get($sessionStore->getName());
            if ($sessionId) {
                $sessionStore->setId($sessionId);
            }
            $request->setLaravelSession($sessionStore);
        }
        
        $session = $request->session();
        if (!$session->isStarted()) {
            // ⚠️ IMPORTANT : Lire l'ID de session depuis les cookies AVANT de démarrer
            // Sinon, une nouvelle session sera créée et l'utilisateur sera déconnecté
            $sessionId = $request->cookies->get($session->getName());
            if ($sessionId) {
                $session->setId($sessionId);
            }
            $session->start();
        }
        
        \Log::info('🔐 [VERIFY CODE] Session démarrée pour vérification code', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'session_id' => substr($session->getId(), 0, 15) . '...',
            'session_started' => $session->isStarted(),
            'timestamp' => now()->toDateTimeString(),
        ]);
        
        // Connecter l'utilisateur dans la session
        Auth::login($user, true);
        
        // Régénérer l'ID de session APRÈS le login pour la sécurité
        $oldSessionId = $session->getId();
        $request->session()->regenerate();
        $newSessionId = $session->getId();
        
        \Log::info('🔄 [VERIFY CODE] Session régénérée après vérification', [
            'user_id' => $user->id,
            'old_session_id' => substr($oldSessionId, 0, 15) . '...',
            'new_session_id' => substr($newSessionId, 0, 15) . '...',
            'password_hash_stored' => $request->session()->has('password_hash_web'),
            'timestamp' => now()->toDateTimeString(),
        ]);
        
        // Stocker le hash du mot de passe dans la session APRÈS la régénération
        // pour que AuthenticateSession puisse vérifier l'authenticité de la session
        $request->session()->put('password_hash_web', $user->getAuthPassword());
        
        // Régénérer le token CSRF
        $request->session()->regenerateToken();

        // Fusionner le panier invité avec le panier utilisateur
        try {
            $guestSessionId = $request->header('X-Session-ID');
            // S'assurer que c'est une chaîne de caractères
            if ($guestSessionId !== null) {
                $guestSessionId = is_array($guestSessionId) ? ($guestSessionId[0] ?? null) : (string)$guestSessionId;
                $this->mergeGuestCart($user, $guestSessionId ?: null);
            }
        } catch (\Exception $e) {
            // Ne pas bloquer la connexion si la fusion du panier échoue
            \Log::error('Erreur lors de la fusion du panier invité: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e
            ]);
        }

        // Vérifier que l'utilisateur est bien authentifié avant de sauvegarder
        if (!Auth::check()) {
            \Log::error('verifyLoginCode: Utilisateur non authentifié après Auth::login()', [
                'user_id' => $user->id
            ]);
        }

        // Sauvegarder la session pour s'assurer qu'elle est persistée
        $request->session()->save();
        
        \Log::info('✅ [VERIFY CODE] Connexion réussie après vérification - Session sauvegardée', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'session_id' => substr($session->getId(), 0, 15) . '...',
            'auth_check' => Auth::check(),
            'password_hash_present' => $request->session()->has('password_hash_web'),
            'session_started' => $session->isStarted(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Créer la réponse JSON
        $response = response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'two_factor_enabled']),
            'redirect' => route('accueil')
        ]);

        // Ajouter le cookie de session à la réponse
        // C'est nécessaire car les routes API n'ont pas le middleware StartSession
        // qui ajoute automatiquement le cookie
        return $this->addSessionCookieToResponse($response, $request);
    }

    /**
     * Demande de réinitialisation de mot de passe
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Adresse email invalide ou inexistante'
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        // Créer un token de réinitialisation
        $resetToken = Str::random(64);
        $user->update([
            'password_reset_token' => $resetToken,
            'password_reset_expires_at' => now()->addHour()
        ]);

        // URL de réinitialisation
        $resetUrl = route('reset-password', ['token' => $resetToken]);

        // Envoyer l'email de réinitialisation
        Mail::to($user->email)->send(new ResetPasswordMail($resetUrl, $user->prenoms));

        return response()->json([
            'success' => true,
            'message' => 'Email de réinitialisation envoyé'
        ]);
    }

    /**
     * Réinitialisation du mot de passe
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('password_reset_token', $request->token)
                   ->where('password_reset_expires_at', '>', now())
                   ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ], 401);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->password),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
    }

    /**
     * Vérification de l'email
     */
    public function verifyEmail(Request $request, $token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return view('auth.email-verification', [
                'success' => false,
                'message' => 'Token de vérification invalide ou expiré',
                'title' => 'Vérification échouée'
            ]);
        }

        $user->update([
            'is_verified' => true,
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);

        return view('auth.email-verification', [
            'success' => true,
            'message' => 'Email vérifié avec succès ! Votre compte est maintenant actif.',
            'title' => 'Vérification réussie',
            'user' => $user
        ]);
    }

    /**
     * Renvoyer le code de vérification
     */
    public function resendVerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'type' => 'required|in:login,register,password_reset',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides'
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable'
            ], 404);
        }

        // Créer un nouveau code
        $authCode = AuthCode::createCode($user->email, $request->type, $request);
        
        // Envoyer l'email
        Mail::to($user->email)->send(new AuthCodeMail($authCode->code, $request->type, $user->prenoms));

        return response()->json([
            'success' => true,
            'message' => 'Code renvoyé avec succès'
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Informations de l'utilisateur connecté
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user()->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'is_verified'])
        ]);
    }

    /**
     * Déconnexion côté client (sans middleware d'authentification)
     */
    public function logoutClient(Request $request)
    {
        $token = $request->bearerToken();
        
        if ($token) {
            // Trouver et supprimer le token
            $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($personalAccessToken) {
                $personalAccessToken->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * Déconnecter tous les appareils de l'utilisateur
     */
    public function logoutAllDevices(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            // Supprimer tous les tokens de l'utilisateur
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
     * Fusionner le panier invité avec le panier utilisateur lors de la connexion
     * 
     * @param \App\Models\User $user L'utilisateur qui vient de se connecter
     * @param string|null $guestSessionId L'ID de session invité (X-Session-ID header)
     */
    private function mergeGuestCart($user, $guestSessionId = null)
    {
        // Si pas de session invité, rien à fusionner
        if (!$guestSessionId) {
            \Log::info('mergeGuestCart: Pas de session invité fournie', [
                'user_id' => $user->id ?? null
            ]);
            return;
        }

        try {
            // Récupérer les items du panier invité
            $guestItems = CartItem::where('session_id', $guestSessionId)
                ->whereNull('user_id')
                ->get();

            \Log::info('mergeGuestCart: Items invités trouvés', [
                'user_id' => $user->id,
                'guest_session_id' => $guestSessionId,
                'items_count' => $guestItems->count()
            ]);

            if ($guestItems->isEmpty()) {
                \Log::info('mergeGuestCart: Aucun item invité à fusionner');
                return; // Pas d'items à fusionner
            }

            foreach ($guestItems as $item) {
                // Vérifier si l'utilisateur a déjà ce produit dans son panier
                // Comparer par product_id, variation_id et attributes (JSON)
                // Normaliser les attributs pour la comparaison
                $itemAttributes = is_string($item->attributes) ? $item->attributes : json_encode($item->attributes ?? []);
                
                $existingItem = CartItem::where('user_id', $user->id)
                    ->where('product_id', $item->product_id)
                    ->where('variation_id', $item->variation_id)
                    ->where(function($query) use ($itemAttributes) {
                        // Comparer les attributs JSON
                        if (empty($itemAttributes) || $itemAttributes === '[]' || $itemAttributes === '{}' || $itemAttributes === 'null') {
                            $query->where(function($q) {
                                $q->whereNull('attributes')
                                  ->orWhere('attributes', '[]')
                                  ->orWhere('attributes', '{}')
                                  ->orWhere('attributes', '');
                            });
                        } else {
                            $query->where('attributes', $itemAttributes)
                                  ->orWhereRaw('JSON_CONTAINS(attributes, ?) AND JSON_CONTAINS(?, attributes)', [$itemAttributes, $itemAttributes]);
                        }
                    })
                    ->first();

                if ($existingItem) {
                    // Fusionner les quantités
                    $existingItem->quantity += $item->quantity;
                    $existingItem->save();
                    // Supprimer l'item invité
                    $item->delete();
                } else {
                    // Transférer l'item à l'utilisateur
                    $item->user_id = $user->id;
                    $item->session_id = null; // Plus besoin de session_id pour les utilisateurs connectés
                    $item->save();
                }
            }

            \Log::info("Panier invité fusionné pour l'utilisateur {$user->id}", [
                'guest_session_id' => $guestSessionId,
                'items_merged' => $guestItems->count(),
                'user_id' => $user->id
            ]);
            
            // Vérifier que les items ont bien été transférés
            $userCartCount = CartItem::where('user_id', $user->id)->count();
            \Log::info('mergeGuestCart: Panier utilisateur après fusion', [
                'user_id' => $user->id,
                'cart_count' => $userCartCount
            ]);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer la connexion
            \Log::error("Erreur lors de la fusion du panier invité: " . $e->getMessage(), [
                'user_id' => $user->id,
                'guest_session_id' => $guestSessionId
            ]);
        }
    }
}
