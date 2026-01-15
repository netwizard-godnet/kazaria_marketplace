<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FcmToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Enregistrer un token FCM pour l'utilisateur connecté
     */
    public function registerToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|max:500',
            'platform' => 'required|string|in:android,ios',
            'device_name' => 'nullable|string|max:255',
            'device_model' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié',
                ], 401);
            }

            // Vérifier si le token existe déjà
            $existingToken = FcmToken::where('token', $request->token)->first();

            if ($existingToken) {
                // Mettre à jour le token existant
                $existingToken->update([
                    'user_id' => $user->id,
                    'platform' => $request->platform,
                    'device_name' => $request->device_name,
                    'device_model' => $request->device_model,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);

                Log::info("✅ [FCM] Token mis à jour pour l'utilisateur {$user->id}");
            } else {
                // Créer un nouveau token
                FcmToken::create([
                    'user_id' => $user->id,
                    'token' => $request->token,
                    'platform' => $request->platform,
                    'device_name' => $request->device_name,
                    'device_model' => $request->device_model,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);

                Log::info("✅ [FCM] Nouveau token enregistré pour l'utilisateur {$user->id}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Token enregistré avec succès',
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [FCM] Erreur enregistrement token: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du token',
            ], 500);
        }
    }

    /**
     * Désenregistrer un token FCM
     */
    public function unregisterToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Token requis',
            ], 422);
        }

        try {
            $user = $request->user();
            
            FcmToken::where('token', $request->token)
                ->where('user_id', $user->id)
                ->update(['is_active' => false]);

            Log::info("🗑️ [FCM] Token désenregistré pour l'utilisateur {$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Token désenregistré avec succès',
            ]);
        } catch (\Exception $e) {
            Log::error('❌ [FCM] Erreur désenregistrement token: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du désenregistrement',
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des tokens (pour admin)
     */
    public function getStats(): JsonResponse
    {
        $totalTokens = FcmToken::count();
        $activeTokens = FcmToken::active()->count();
        $androidTokens = FcmToken::active()->platform('android')->count();
        $iosTokens = FcmToken::active()->platform('ios')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalTokens,
                'active' => $activeTokens,
                'android' => $androidTokens,
                'ios' => $iosTokens,
            ],
        ]);
    }
}

