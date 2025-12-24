<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        $this->mergeGuestCart($user, request()->header('X-Session-ID'));

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
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}

