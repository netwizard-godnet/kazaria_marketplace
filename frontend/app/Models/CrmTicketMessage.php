<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CrmTicketMessage extends Model
{
    use HasFactory;

    protected $table = 'crm_ticket_messages';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_internal',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_internal' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(CrmTicket::class, 'ticket_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


