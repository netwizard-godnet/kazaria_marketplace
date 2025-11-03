<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon; // Laravel Carbon wrapper

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_percent',
        'max_uses',
        'uses',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'discount_percent' => 'integer',
        'max_uses' => 'integer',
        'uses' => 'integer',
    ];

    public function isCurrentlyValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        $now = Carbon::now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }
        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }
        if ($this->max_uses !== null && $this->uses >= $this->max_uses) {
            return false;
        }
        return true;
    }
}


