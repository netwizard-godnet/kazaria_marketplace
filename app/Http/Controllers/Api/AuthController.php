<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // 📝 Inscription
    public function register(Request $request)
    {
        $rules = [
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'email_verified_at' => 'string|max:255',
            'telephone' => 'required|string|max:255|unique:users',
            'telephone_verified_at' => 'string|max:255',
            'profile_pic_url' => 'string|max:255',
            'is_verified' => 'string|max:255',
            'adresse' => 'string|max:255',
            'newsletter' => 'string|max:255',
            'termes_condition' => 'string|max:255',
            'statut' => 'string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ];

        // Messages personnalisés
        $messages = [
            'nom.required' => 'Le nom est obligatoire.',
            'prenoms.required' => 'Les prénoms sont obligatoires.',
            'email.required' => "L'email est obligatoire.",
            'email.email' => "L'email doit être une adresse email valide.",
            'email.unique' => "Cet email est déjà utilisé.",
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Le mot de passe et sa confirmation ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'termes_condition.accepted' => 'Vous devez accepter les termes et conditions.',
        ];

        // Création du validator
        $validator = Validator::make($request->all(), $rules, $messages);

        // Vérifier si la validation échoue
        if ($validator->fails()) {
            $allErrors = implode('<br>', $validator->errors()->all());

            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation.',
                'errors' => $allErrors // renvoie toutes les erreurs
            ], 422);
        }

        $user = User::create([
            'nom' => $request->nom,
            'prenoms' => $request->prenoms,
            'email' => $request->email,
            'email_verified_at' => "false",
            'telephone' => $request->telephone,
            'telephone_verified_at' => "false",
            'profile_pic_url' => $request->profile_pic_url,
            'is_verified' => "false",
            'adresse' => $request->adresse,
            'newsletter' => $request->newsletter,
            'termes_condition' => $request->termes_condition,
            'statut' => "actif",
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        return response()->json([
            'success' => true,
            'message' => 'Compte créé. Veuillez vérifier votre email.'
        ]);
    }

    // 🔐 Connexion
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants incorrects'], 401);
        }

        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Veuillez vérifier votre adresse email.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie ! Redirection automatique...',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);

        if (auth()->check()) {
            return redirect()->route('profile');
        }
        return view('auth.authentification');
    }

    // 🚪 Déconnexion
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnexion réussie']);
    }

    // 🔁 Mot de passe oublié
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Lien de réinitialisation envoyé.'])
            : response()->json(['message' => 'Erreur d’envoi du lien.'], 500);
    }

    // 🔄 Réinitialisation du mot de passe
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Mot de passe réinitialisé avec succès.'])
            : response()->json(['message' => 'Erreur de réinitialisation.'], 500);
    }
}
