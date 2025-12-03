<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Popup extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'slug',
        'content',
        'cta_text',
        'cta_url',
        'image',
        'layout',
        'is_active',
        'display_start',
        'display_end',
        'display_pages',
        'display_devices',
        'frequency',
        'delay_seconds',
        'max_impressions',
        'priority',
        'options',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_start' => 'datetime',
        'display_end' => 'datetime',
        'display_pages' => 'array',
        'display_devices' => 'array',
        'options' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('display_start')
                  ->orWhere('display_start', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('display_end')
                  ->orWhere('display_end', '>=', $now);
            });
    }
}
