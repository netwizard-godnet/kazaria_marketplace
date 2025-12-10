<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuthCode;
use App\Mail\AuthCodeMail;
use App\Mail\VerifyEmailMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
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
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = $errors->first();
            
            return response()->json([
                'success' => false,
                'message' => $firstError,
                'errors' => $errors
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

            // Envoyer l'email de vérification
            Mail::to($user->email)->send(new VerifyEmailMail($user, $verificationUrl));

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès ! Un email de vérification a été envoyé à votre adresse email.',
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
            return response()->json([
                'success' => false,
                'message' => 'Email et mot de passe requis'
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        if (!$user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez d\'abord vérifier votre adresse email'
            ], 401);
        }

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
            Log::error('Erreur envoi code connexion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du code. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Vérification du code de connexion
     */
    public function verifyLoginCode(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Code de 8 chiffres requis'
            ], 422);
        }

        // Vérifier le code d'authentification
        $authCode = AuthCode::where('email', $request->email)
                           ->where('code', $request->code)
                           ->where('type', 'login')
                           ->unused()
                           ->notExpired()
                           ->first();

        if (!$authCode) {
            return response()->json([
                'success' => false,
                'message' => 'Code invalide ou expiré'
            ], 401);
        }

        // Récupérer l'utilisateur
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable'
            ], 404);
        }

        // Détecter si c'est une requête API (mobile) ou web
        $isApiRoute = $request->is('api/*');
        
        // Vérifier si c'est une vraie app mobile
        $userAgent = $request->header('User-Agent', '');
        $isMobileApp = strpos($userAgent, 'Dart') !== false 
            || strpos($userAgent, 'Flutter') !== false
            || $request->header('X-Mobile-App') === 'true';
        
        // Pour les routes API mobiles, utiliser les tokens Sanctum (pas de session)
        if ($isApiRoute || $isMobileApp) {
            try {
                // Créer le token AVANT de marquer le code comme utilisé
                $token = $user->createToken('mobile-app')->plainTextToken;
                
                // Marquer le code comme utilisé seulement après succès
                $authCode->markAsUsed();

                return response()->json([
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'token' => $token,
                    'token_type' => 'Bearer',
                    'user' => array_merge(
                        $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'is_verified', 'is_seller']),
                        ['has_store' => $user->store()->exists()]
                    )
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur création token mobile: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la connexion'
                ], 500);
            }
        }

        // Pour les routes web, utiliser les sessions
        // Vérifier que la session est disponible pour le web
        if (!$request->hasSession()) {
            Log::error('Session store not set on request for web verifyLoginCode');
            return response()->json([
                'success' => false,
                'message' => 'Erreur de session. Veuillez rafraîchir la page et réessayer.'
            ], 500);
        }

        try {
            // Marquer le code comme utilisé
            $authCode->markAsUsed();
            
            // Régénérer l'ID de session AVANT le login pour éviter les problèmes
            $request->session()->regenerate();
            
            // Créer une session web persistante
            Auth::login($user, true);
            
            // Régénérer le token CSRF
            $request->session()->regenerateToken();
            
            // Forcer la sauvegarde de la session
            $request->session()->save();

            // Créer aussi un token pour les appels API depuis le web
            $token = $user->createToken('web-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'token' => $token, // Token pour les appels API
                'user' => array_merge(
                    $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'is_verified', 'is_seller']),
                    ['has_store' => $user->store()->exists()]
                ),
                'redirect' => route('accueil')
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur connexion web: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la connexion'
            ], 500);
        }
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
            'message' => 'Email de réinitialisation envoyé',
            'token' => $resetToken, // ✅ Retourner le token pour l'app mobile
            'email' => $user->email, // ✅ Retourner l'email pour faciliter la réinitialisation
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
        // D'abord essayer avec le token (API mobile)
        $user = $request->user();
        
        // Si pas d'utilisateur via token, essayer avec la session (web)
        if (!$user && Auth::check()) {
            $user = Auth::user();
        }
        
        if (!$user) {
        return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }
        
        // Si l'utilisateur est connecté via session mais n'a pas de token, en créer un
        $token = $request->bearerToken();
        if (!$token && Auth::check()) {
            // Créer un token pour les appels API depuis le web
            $token = $user->createToken('web-app')->plainTextToken;
        }
        
        $response = [
            'success' => true,
            'user' => array_merge(
                $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'is_verified', 'is_seller']),
                ['has_store' => $user->store()->exists()]
            )
        ];
        
        // Inclure le token si créé
        if ($token) {
            $response['token'] = $token;
        }
        
        return response()->json($response);
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
            Log::error('Erreur lors de la déconnexion de tous les appareils: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion'
            ], 500);
        }
    }
}
