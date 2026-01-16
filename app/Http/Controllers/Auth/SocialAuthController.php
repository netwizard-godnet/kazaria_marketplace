<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Liste des providers supportés.
     */
    private array $providers = ['google', 'facebook'];

    /**
     * Redirige l'utilisateur vers le provider OAuth.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->ensureProviderSupported($provider);

        return Socialite::driver($provider)
            ->stateless()
            ->redirect();
    }

    /**
     * Handle le callback OAuth et connecte/crée l'utilisateur.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->ensureProviderSupported($provider);

        try {
            $socialUser = Socialite::driver($provider)
                ->stateless()
                ->user();
        } catch (\Throwable $th) {
            Log::error('Social login failed', [
                'provider' => $provider,
                'message' => $th->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->with('error', "Connexion {$provider} impossible. Merci de réessayer.");
        }

        if (!$socialUser->getEmail()) {
            return redirect()
                ->route('login')
                ->with('error', "Nous n'avons pas reçu votre adresse email {$provider}. Merci d'utiliser une autre méthode de connexion.");
        }

        $user = User::where('provider_name', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if (!$user) {
            $user = User::where('email', $socialUser->getEmail())->first();
        }

        $shouldFetchAvatar = !$user || empty($user->profile_pic_url);
        $avatarPath = $shouldFetchAvatar
            ? $this->downloadAvatar($socialUser->getAvatar(), $provider, (string) $socialUser->getId())
            : null;

        $userData = [
            'provider_name' => $provider,
            'provider_id' => $socialUser->getId(),
            'provider_token' => $socialUser->token ?? null,
            'provider_refresh_token' => $socialUser->refreshToken ?? null,
            'email_verified_at' => now(),
            'is_verified' => true,
        ];

        if ($avatarPath) {
            $userData['profile_pic_url'] = $avatarPath;
        }

        if (!$user) {
            [$lastName, $firstName] = $this->splitName($socialUser->getName());

            $user = User::create(array_merge($userData, [
                'nom' => $lastName,
                'prenoms' => $firstName,
                'email' => $socialUser->getEmail(),
                'telephone' => null,
                'password' => Str::password(32),
                'statut' => 'actif',
                'termes_condition' => true,
                'newsletter' => false,
                'profile_pic_url' => $avatarPath,
            ]));
        } else {
            $user->update($userData);
        }

        // S'assurer que la session est démarrée
        if (!request()->hasSession()) {
            request()->setLaravelSession(app('session.store'));
        }
        
        $session = request()->session();
        if (!$session->isStarted()) {
            // ⚠️ IMPORTANT : Lire l'ID de session depuis les cookies AVANT de démarrer
            // Sinon, une nouvelle session sera créée et l'utilisateur sera déconnecté
            $sessionId = request()->cookies->get($session->getName());
            if ($sessionId) {
                $session->setId($sessionId);
            }
            $session->start();
        }
        
        // Connecter l'utilisateur dans la session
        Auth::login($user, true);
        
        // Régénérer l'ID de session APRÈS le login pour la sécurité
        request()->session()->regenerate();
        
        // Stocker le hash du mot de passe dans la session APRÈS la régénération
        // pour que AuthenticateSession puisse vérifier l'authenticité de la session
        request()->session()->put('password_hash_web', $user->getAuthPassword());
        
        // Régénérer le token CSRF
        request()->session()->regenerateToken();

        // Fusionner le panier invité avec le panier utilisateur
        try {
            $guestSessionId = request()->header('X-Session-ID');
            if ($guestSessionId) {
                $guestSessionId = is_array($guestSessionId) ? ($guestSessionId[0] ?? null) : (string)$guestSessionId;
                $this->mergeGuestCart($user, $guestSessionId ?: null);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la fusion du panier invité (social auth): ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e
            ]);
        }

        // Sauvegarder la session pour garantir la persistance
        request()->session()->save();
        
        \Log::info('✅ [SOCIAL AUTH] Connexion sociale réussie', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'provider' => $provider,
            'session_id' => substr(request()->session()->getId(), 0, 15) . '...',
            'password_hash_present' => request()->session()->has('password_hash_web'),
        ]);

        // Rediriger vers l'accueil avec cache-busting pour forcer le rechargement
        return redirect(route('accueil') . '?login=' . time())
            ->with('success', "Bienvenue {$user->prenoms} !");
    }

    private function ensureProviderSupported(string $provider): void
    {
        if (!in_array($provider, $this->providers, true)) {
            abort(404);
        }
    }

    /**
     * Découpe une chaîne en nom / prénom(s)
     */
    private function splitName(?string $fullName): array
    {
        $fullName = trim($fullName ?? '');

        if ($fullName === '') {
            return ['Utilisateur', 'Kazaria'];
        }

        $parts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);

        if (count($parts) === 1) {
            return [$parts[0], $parts[0]];
        }

        $lastName = array_shift($parts);
        $firstName = implode(' ', $parts);

        return [$lastName, $firstName];
    }

    /**
     * Fusionner le panier invité avec le panier utilisateur lors de la connexion sociale
     * 
     * @param \App\Models\User $user L'utilisateur qui vient de se connecter
     * @param string|null $guestSessionId L'ID de session invité (X-Session-ID header)
     */
    private function mergeGuestCart($user, $guestSessionId = null)
    {
        // Si pas de session invité, rien à fusionner
        if (!$guestSessionId) {
            return;
        }

        try {
            // Récupérer les items du panier invité
            $guestItems = \App\Models\CartItem::where('session_id', $guestSessionId)
                ->whereNull('user_id')
                ->get();

            if ($guestItems->isEmpty()) {
                return; // Pas d'items à fusionner
            }

            foreach ($guestItems as $item) {
                // Vérifier si l'utilisateur a déjà ce produit dans son panier
                $existingItem = \App\Models\CartItem::where('user_id', $user->id)
                    ->where('product_id', $item->product_id)
                    ->where('variation_id', $item->variation_id)
                    ->where('attributes', $item->attributes)
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
                    $item->session_id = null;
                    $item->save();
                }
            }

            \Log::info("Panier invité fusionné pour l'utilisateur {$user->id} (social auth)", [
                'guest_session_id' => $guestSessionId,
                'items_merged' => $guestItems->count()
            ]);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer la connexion
            \Log::error("Erreur lors de la fusion du panier invité (social auth): " . $e->getMessage(), [
                'user_id' => $user->id,
                'guest_session_id' => $guestSessionId
            ]);
        }
    }

    private function downloadAvatar(?string $url, string $provider, string $socialId): ?string
    {
        if (!$url) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get($url);
            if (!$response->successful()) {
                return null;
            }

            $extension = $this->guessExtension($response->header('Content-Type'));
            $directory = public_path('images/profiles');

            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            $filename = sprintf('%s_%s_%s.%s', $provider, $socialId, time(), $extension);
            file_put_contents($directory . '/' . $filename, $response->body());

            return 'images/profiles/' . $filename;
        } catch (\Throwable $th) {
            Log::warning('Impossible de télécharger la photo de profil', [
                'provider' => $provider,
                'message' => $th->getMessage(),
            ]);
            return null;
        }
    }

    private function guessExtension(?string $mime): string
    {
        return match (true) {
            str_contains((string) $mime, 'png') => 'png',
            str_contains((string) $mime, 'gif') => 'gif',
            default => 'jpg',
        };
    }

    private function isLocalPath(?string $path): bool
    {
        return $path && !preg_match('/^https?:\/\//i', $path);
    }

    private function deleteLocalAvatar(string $path): void
    {
        $fullPath = public_path($path);
        if ($path && File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    /**
     * Authentification sociale pour mobile (retourne un token JSON)
     */
    public function mobileAuth(Request $request, string $provider): JsonResponse
    {
        $this->ensureProviderSupported($provider);

        $validator = \Validator::make($request->all(), [
            'access_token' => 'required|string',
            'id' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string',
            'avatar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $socialId = $request->input('id');
            $email = $request->input('email');
            $name = $request->input('name');
            $avatar = $request->input('avatar');

            // Vérifier si l'utilisateur existe déjà
            $user = User::where('provider_name', $provider)
                ->where('provider_id', $socialId)
                ->first();

            if (!$user) {
                $user = User::where('email', $email)->first();
            }

            $shouldFetchAvatar = !$user || empty($user->profile_pic_url);
            $avatarPath = $shouldFetchAvatar && $avatar
                ? $this->downloadAvatar($avatar, $provider, $socialId)
                : null;

            $userData = [
                'provider_name' => $provider,
                'provider_id' => $socialId,
                'provider_token' => $request->input('access_token'),
                'email_verified_at' => now(),
                'is_verified' => true,
            ];

            if ($avatarPath) {
                $userData['profile_pic_url'] = $avatarPath;
            }

            if (!$user) {
                [$lastName, $firstName] = $this->splitName($name);

                $user = User::create(array_merge($userData, [
                    'nom' => $lastName,
                    'prenoms' => $firstName,
                    'email' => $email,
                    'telephone' => null,
                    'password' => Str::password(32),
                    'statut' => 'actif',
                    'termes_condition' => true,
                    'newsletter' => false,
                    'profile_pic_url' => $avatarPath,
                ]));
            } else {
                $user->update($userData);
            }

            // Créer un token Sanctum pour l'app mobile
            $token = $user->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenoms' => $user->prenoms,
                    'email' => $user->email,
                    'telephone' => $user->telephone,
                    'profile_pic_url' => $user->profile_pic_url,
                    'is_seller' => $user->isSeller(),
                ],
            ]);
        } catch (\Throwable $th) {
            Log::error('Social mobile auth failed', [
                'provider' => $provider,
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la connexion {$provider}: " . $th->getMessage(),
            ], 500);
        }
    }
}

