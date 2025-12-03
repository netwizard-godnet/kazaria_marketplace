<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user1_id',
        'user2_id',
        'subject',
        'last_message_at',
        'is_archived',
        'is_important',
        'conversation_type'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_archived' => 'boolean',
        'is_important' => 'boolean'
    ];

    /**
     * Relation avec le premier utilisateur
     */
    public function user1()
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    /**
     * Relation avec le deuxième utilisateur
     */
    public function user2()
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    /**
     * Relation avec les messages
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Dernier message de la conversation
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    /**
     * Obtenir l'autre utilisateur de la conversation
     */
    public function getOtherUser($userId)
    {
        if ($this->user1_id == $userId) {
            return $this->user2;
        }
        return $this->user1;
    }

    /**
     * Mettre à jour la date du dernier message
     */
    public function updateLastMessage()
    {
        $this->update(['last_message_at' => now()]);
    }

    /**
     * Scope pour les conversations d'un utilisateur
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user1_id', $userId)
                    ->orWhere('user2_id', $userId);
    }

    /**
     * Scope pour les conversations non archivées
     */
    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope pour les conversations importantes
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }
}
