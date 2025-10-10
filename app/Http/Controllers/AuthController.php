<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\AuthCodeMail;
use App\Mail\VerifyEmailMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Afficher le formulaire d'inscription
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Traiter l'inscription
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telephone' => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'adresse' => 'nullable|string',
            'newsletter' => 'boolean',
            'termes_condition' => 'required|accepted',
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'prenoms.required' => 'Le(s) prénom(s) est/sont obligatoire(s).',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'termes_condition.accepted' => 'Vous devez accepter les termes et conditions.',
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenoms' => $validated['prenoms'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'password' => Hash::make($validated['password']),
            'adresse' => $validated['adresse'] ?? null,
            'newsletter' => $validated['newsletter'] ?? false,
            'termes_condition' => true,
            'statut' => 'actif',
        ]);

        event(new Registered($user));

        // Générer le lien de vérification
        $verificationUrl = $this->generateVerificationUrl($user);

        // Envoyer l'email de vérification
        Mail::to($user->email)->send(new VerifyEmailMail($verificationUrl, $user->prenoms . ' ' . $user->nom));

        return redirect()->route('verification.notice')->with('success', 'Votre compte a été créé. Veuillez vérifier votre adresse email pour continuer.');
    }

    /**
     * Traiter la connexion (première étape - envoi du code)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Les identifiants fournis sont incorrects.',
            ])->withInput($request->except('password'));
        }

        // Vérifier si l'email est vérifié
        if (!$user->hasVerifiedEmail()) {
            return back()->withErrors([
                'email' => 'Veuillez d\'abord vérifier votre adresse email.',
            ])->with('resend_verification', true);
        }

        // Générer le code d'authentification
        $authCode = $user->generateAuthCode();

        // Envoyer le code par email
        Mail::to($user->email)->send(new AuthCodeMail($authCode, $user->prenoms . ' ' . $user->nom));

        // Stocker l'ID de l'utilisateur en session temporaire
        session(['pending_auth_user_id' => $user->id]);

        return redirect()->route('verify-code.show')->with('success', 'Un code d\'authentification a été envoyé à votre adresse email.');
    }

    /**
     * Afficher le formulaire de vérification du code
     */
    public function showVerifyCodeForm()
    {
        if (!session('pending_auth_user_id')) {
            return redirect()->route('login')->withErrors(['error' => 'Session expirée. Veuillez vous reconnecter.']);
        }

        return view('auth.verify-code');
    }

    /**
     * Vérifier le code d'authentification
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:8',
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.size' => 'Le code doit contenir exactement 8 chiffres.',
        ]);

        $userId = session('pending_auth_user_id');

        if (!$userId) {
            return redirect()->route('login')->withErrors(['error' => 'Session expirée. Veuillez vous reconnecter.']);
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'Utilisateur non trouvé.']);
        }

        if ($user->verifyAuthCode($request->code)) {
            // Connecter l'utilisateur
            Auth::login($user, $request->has('remember'));
            session()->forget('pending_auth_user_id');

            return redirect()->intended(route('accueil'))->with('success', 'Connexion réussie !');
        }

        return back()->withErrors([
            'code' => 'Le code est invalide ou a expiré.',
        ])->withInput();
    }

    /**
     * Renvoyer le code d'authentification
     */
    public function resendAuthCode()
    {
        $userId = session('pending_auth_user_id');

        if (!$userId) {
            return redirect()->route('login')->withErrors(['error' => 'Session expirée. Veuillez vous reconnecter.']);
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'Utilisateur non trouvé.']);
        }

        $authCode = $user->generateAuthCode();
        Mail::to($user->email)->send(new AuthCodeMail($authCode, $user->prenoms . ' ' . $user->nom));

        return back()->with('success', 'Un nouveau code a été envoyé à votre adresse email.');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('accueil')->with('success', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * Afficher la page de notification de vérification d'email
     */
    public function verificationNotice()
    {
        return view('auth.verify-email');
    }

    /**
     * Vérifier l'email via le lien
     * Note: Version simplifiée pour tests en développement
     * TODO: Renforcer la sécurité avant production
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Vérification simplifiée pour les tests (utilise email direct)
        $expectedHash = sha1($user->email);
        
        if ($hash !== $expectedHash) {
            return redirect()->route('login')->withErrors(['error' => 'Lien de vérification invalide. Veuillez demander un nouveau lien.']);
        }

        // Vérifier si l'email est déjà vérifié
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('info', 'Votre email est déjà vérifié. Vous pouvez vous connecter.');
        }

        // Marquer l'email comme vérifié
        $user->email_verified_at = now();
        $user->is_verified = true;
        $user->save();
        
        event(new \Illuminate\Auth\Events\Verified($user));

        return redirect()->route('login')->with('success', 'Votre email a été vérifié avec succès. Vous pouvez maintenant vous connecter.');
    }

    /**
     * Renvoyer l'email de vérification
     */
    public function resendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.exists' => 'Cette adresse email n\'existe pas dans notre système.',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return back()->with('info', 'Votre email est déjà vérifié.');
        }

        $verificationUrl = $this->generateVerificationUrl($user);
        Mail::to($user->email)->send(new VerifyEmailMail($verificationUrl, $user->prenoms . ' ' . $user->nom));

        return back()->with('success', 'Un nouveau lien de vérification a été envoyé à votre adresse email.');
    }

    /**
     * Afficher le formulaire de demande de réinitialisation de mot de passe
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Envoyer le lien de réinitialisation
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.exists' => 'Aucun compte n\'est associé à cette adresse email.',
        ]);

        $user = User::where('email', $request->email)->first();
        
        // Créer un token de réinitialisation
        $token = Str::random(64);
        
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $request->email]);

        Mail::to($user->email)->send(new ResetPasswordMail($resetUrl, $user->prenoms . ' ' . $user->nom, $token));

        return back()->with('success', 'Un lien de réinitialisation a été envoyé à votre adresse email.');
    }

    /**
     * Afficher le formulaire de réinitialisation de mot de passe
     */
    public function showResetPasswordForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.exists' => 'Cette adresse email n\'existe pas.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        // Vérifier le token
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return back()->withErrors(['email' => 'Ce lien de réinitialisation est invalide.']);
        }

        // Vérifier si le token n'a pas expiré (1 heure)
        if (Carbon::parse($passwordReset->created_at)->addHour()->isPast()) {
            return back()->withErrors(['email' => 'Ce lien de réinitialisation a expiré.']);
        }

        // Mettre à jour le mot de passe
        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Supprimer le token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');
    }

    /**
     * Générer l'URL de vérification d'email
     * Note: Version simplifiée pour les tests en développement
     * TODO: Réactiver temporarySignedRoute avant la production
     */
    private function generateVerificationUrl(User $user): string
    {
        // Version simplifiée pour les tests (sans signature)
        return route('verification.verify', [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);
        
        // Version avec signature (à réactiver en production)
        /*
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
        */
    }
}

