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

        // Vérifier si l'authentification à deux facteurs est activée
        if (!$user->two_factor_enabled) {
            // Connexion directe sans code si le 2FA n'est pas activé
            try {
                // S'assurer que la session est démarrée
                if (!$request->hasSession()) {
                    $request->setLaravelSession(app('session.store'));
                }
                
                $session = $request->session();
                if (!$session->isStarted()) {
                    $session->start();
                }
                
                // Connecter l'utilisateur dans la session
                Auth::login($user, $request->has('remember'));
                
                // Régénérer l'ID de session APRÈS le login pour la sécurité
                // Cela crée une nouvelle session avec l'utilisateur déjà authentifié
                $request->session()->regenerate();
                
                // Stocker le hash du mot de passe dans la session APRÈS la régénération
                // pour que AuthenticateSession puisse vérifier l'authenticité de la session
                $request->session()->put('password_hash_web', $user->getAuthPassword());
                
                // Régénérer le token CSRF
                $request->session()->regenerateToken();

                return response()->json([
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'two_factor_enabled']),
                    'requires_code' => false,
                    'redirect' => route('accueil')
                ]);
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
     * Vérification du code de connexion
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
            $request->setLaravelSession(app('session.store'));
        }
        
        $session = $request->session();
        if (!$session->isStarted()) {
            $session->start();
        }
        
        // Connecter l'utilisateur dans la session
        Auth::login($user, true);
        
        // Régénérer l'ID de session APRÈS le login pour la sécurité
        $request->session()->regenerate();
        
        // Stocker le hash du mot de passe dans la session APRÈS la régénération
        // pour que AuthenticateSession puisse vérifier l'authenticité de la session
        $request->session()->put('password_hash_web', $user->getAuthPassword());
        
        // Régénérer le token CSRF
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => $user->only(['id', 'nom', 'prenoms', 'email', 'telephone', 'two_factor_enabled']),
            'redirect' => route('accueil')
        ]);
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
}
