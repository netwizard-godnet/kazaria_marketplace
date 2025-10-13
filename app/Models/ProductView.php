<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'session_id',
        'user_id',
        'ip_address',
        'user_agent'
    ];

    // Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeRecent($query, $minutes = 60)
    {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Méthodes statiques utilitaires
    public static function trackView($productId, $request = null)
    {
        $sessionId = $request ? $request->session()->getId() : session()->getId();
        $userId = auth()->id();
        $ipAddress = $request ? $request->ip() : request()->ip();
        $userAgent = $request ? $request->userAgent() : request()->userAgent();

        // Éviter de tracker plusieurs fois le même produit dans la même session récemment
        $recentView = static::where('product_id', $productId)
            ->where(function($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->recent(5) // Dans les 5 dernières minutes
            ->first();

        if (!$recentView) {
            static::create([
                'product_id' => $productId,
                'session_id' => $userId ? null : $sessionId,
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ]);
        }
    }

    public static function getRecentViews($limit = 12, $excludeProductId = null)
    {
        $userId = auth()->id();
        $sessionId = session()->getId();

        // Si pas d'utilisateur connecté et pas de session, retourner des produits populaires
        if (!$userId && !$sessionId) {
            return Product::active()
                ->inStock()
                ->where('id', '!=', $excludeProductId)
                ->orderBy('views_count', 'desc')
                ->take($limit)
                ->get();
        }

        $query = static::where(function($query) use ($userId, $sessionId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } elseif ($sessionId) {
                    $query->where('session_id', $sessionId);
                }
            })
            ->recent(1440) // 24 heures
            ->orderBy('created_at', 'desc');

        if ($excludeProductId) {
            $query->where('product_id', '!=', $excludeProductId);
        }

        // Récupérer les IDs uniques (pas de doublons) avec la date
        $viewData = $query->select('product_id', 'created_at')
            ->get()
            ->groupBy('product_id')
            ->map(function($views) {
                return $views->first(); // Prendre la vue la plus récente pour chaque produit
            })
            ->sortByDesc('created_at')
            ->take($limit)
            ->pluck('product_id');

        // Si pas assez de vues récentes, compléter avec des produits populaires
        $recentProducts = Product::whereIn('id', $viewData)
            ->active()
            ->inStock()
            ->get()
            ->sortBy(function($product) use ($viewData) {
                return array_search($product->id, $viewData->toArray());
            });

        if ($recentProducts->count() < $limit) {
            $popularProducts = Product::active()
                ->inStock()
                ->where('id', '!=', $excludeProductId)
                ->whereNotIn('id', $viewData)
                ->orderBy('views_count', 'desc')
                ->take($limit - $recentProducts->count())
                ->get();
            
            $recentProducts = $recentProducts->merge($popularProducts);
        }

        return $recentProducts;
    }
}
