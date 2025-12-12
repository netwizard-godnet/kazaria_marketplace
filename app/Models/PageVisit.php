<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_path',
        'page_name',
        'session_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer',
        'click_count',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeForPage($query, $pagePath)
    {
        return $query->where('page_path', $pagePath);
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
    public static function trackVisit($pagePath, $pageName = null, $request = null)
    {
        $sessionId = $request ? $request->session()->getId() : session()->getId();
        $userId = auth()->id();
        $ipAddress = $request ? $request->ip() : request()->ip();
        $userAgent = $request ? $request->userAgent() : request()->userAgent();
        $referrer = $request ? $request->header('referer') : request()->header('referer');

        // Éviter de tracker plusieurs fois la même page dans la même session récemment
        $recentVisit = static::where('page_path', $pagePath)
            ->where(function($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();

        if (!$recentVisit) {
            return static::create([
                'page_path' => $pagePath,
                'page_name' => $pageName ?: self::getPageNameFromPath($pagePath),
                'session_id' => $userId ? null : $sessionId,
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'referrer' => $referrer,
                'click_count' => 0,
            ]);
        }

        return $recentVisit;
    }

    public static function incrementClick($pagePath, $request = null)
    {
        $sessionId = $request ? $request->session()->getId() : session()->getId();
        $userId = auth()->id();

        $visit = static::where('page_path', $pagePath)
            ->where(function($query) use ($sessionId, $userId) {
                if ($userId) {
                    $query->where('user_id', $userId);
                } else {
                    $query->where('session_id', $sessionId);
                }
            })
            ->orderBy('created_at', 'desc')
            ->first();

        if ($visit) {
            $visit->increment('click_count');
            return $visit->fresh();
        }

        // Si pas de visite trouvée, créer une nouvelle entrée avec 1 clic
        $visit = static::trackVisit($pagePath, null, $request);
        $visit->increment('click_count');
        return $visit->fresh();
    }

    private static function getPageNameFromPath($path)
    {
        // Extraire un nom de page à partir du chemin
        $path = trim($path, '/');
        
        if (empty($path)) {
            return 'Accueil';
        }

        // Remplacer les tirets et underscores par des espaces et capitaliser
        $name = str_replace(['-', '_'], ' ', $path);
        $name = ucwords($name);
        
        return $name;
    }
}

