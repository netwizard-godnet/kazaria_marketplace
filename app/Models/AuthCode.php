<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuthCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'code',
        'type', // 'login', 'register', 'password_reset'
        'expires_at',
        'used_at',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    // Générer un code de 8 chiffres
    public static function generateCode(): string
    {
        return str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    }

    // Créer un nouveau code d'authentification
    public static function createCode(string $email, string $type, $request = null): self
    {
        // Supprimer les anciens codes pour cet email et ce type
        static::where('email', $email)
               ->where('type', $type)
               ->delete();

        return static::create([
            'email' => $email,
            'code' => static::generateCode(),
            'type' => $type,
            'expires_at' => now()->addMinutes(15), // Code valide 15 minutes
            'ip_address' => $request ? $request->ip() : request()->ip(),
            'user_agent' => $request ? $request->userAgent() : request()->userAgent(),
        ]);
    }

    // Vérifier si un code est valide
    public function isValid(): bool
    {
        return $this->expires_at->isFuture() && is_null($this->used_at);
    }

    // Marquer le code comme utilisé
    public function markAsUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    // Scope pour les codes non utilisés
    public function scopeUnused($query)
    {
        return $query->whereNull('used_at');
    }

    // Scope pour les codes non expirés
    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    // Scope pour un type spécifique
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Nettoyer les anciens codes (à appeler périodiquement)
    public static function cleanup()
    {
        static::where('expires_at', '<', now()->subHour())->delete();
    }
}
