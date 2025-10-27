<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Afficher la page de connexion admin
     */
    public function showLoginForm()
    {
        // Si l'utilisateur est déjà connecté en tant qu'admin, rediriger vers le dashboard
        if (Auth::check() && Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('auth.admin-login');
    }
    
    /**
     * Traiter la connexion admin
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        
        $credentials = $request->only('email', 'password');
        
        // Vérifier si l'utilisateur existe et est admin
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'Aucun compte trouvé avec cette adresse email.']);
        }
        
        if (!$user->is_admin) {
            return back()->withErrors(['email' => 'Accès refusé. Vous n\'êtes pas autorisé à accéder à l\'administration.']);
        }
        
        if (!Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }
        
        if (!$user->is_verified) {
            return back()->withErrors(['email' => 'Votre compte n\'est pas vérifié. Veuillez vérifier votre email.']);
        }
        
        // Connecter l'utilisateur
        Auth::login($user, $request->has('remember'));
        
        // Créer un token pour l'API
        $token = $user->createToken('admin-token')->plainTextToken;
        
        // Stocker le token dans la session
        session(['admin_token' => $token]);
        
        return redirect()->intended(route('admin.dashboard'));
    }
    
    /**
     * Déconnexion admin
     */
    public function logout(Request $request)
    {
        // Révoker le token API
        if (Auth::check()) {
            Auth::user()->tokens()->delete();
        }
        
        // Déconnecter la session
        Auth::logout();
        
        // Nettoyer la session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login')->with('success', 'Vous avez été déconnecté avec succès.');
    }
    
    /**
     * Vérifier l'authentification admin via API
     */
    public function checkAuth(Request $request)
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return response()->json([
                'authenticated' => true,
                'user' => Auth::user()
            ]);
        }
        
        return response()->json([
            'authenticated' => false
        ], 401);
    }
}
