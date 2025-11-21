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

        // Régénérer l'ID de session AVANT le login pour éviter les problèmes
        request()->session()->regenerate();
        
        // Créer une session web persistante
        Auth::login($user, true);
        
        // Régénérer le token CSRF
        request()->session()->regenerateToken();
        
        // Forcer la sauvegarde de la session
        request()->session()->save();

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

