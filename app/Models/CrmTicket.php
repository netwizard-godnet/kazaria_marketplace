<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmTicket extends Model
{
    use HasFactory;

    protected $table = 'crm_tickets';

    protected $fillable = [
        'ticket_number',
        'client_id',
        'seller_id',
        'order_id',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_to',
        'sla_deadline',
        'resolved_at',
        'closed_at',
        'satisfaction_rating',
        'satisfaction_comment',
    ];

    protected $casts = [
        'sla_deadline' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'satisfaction_rating' => 'decimal:1',
    ];

    public function messages()
    {
        return $this->hasMany(CrmTicketMessage::class, 'ticket_id')->orderBy('created_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('client_id', $userId)
              ->orWhere('seller_id', $userId)
              ->orWhere('assigned_to', $userId);
        });
    }
}


