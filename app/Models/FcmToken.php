<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FcmToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'device_name',
        'device_model',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les tokens actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour une plateforme spécifique
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Marquer le token comme utilisé
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Désactiver le token
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}
