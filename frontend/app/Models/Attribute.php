<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_filterable',
        'order'
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($attribute) {
            if (empty($attribute->slug)) {
                $attribute->slug = Str::slug($attribute->name);
            }
        });
    }

    // Relations
    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('order');
    }

    // Scopes
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
