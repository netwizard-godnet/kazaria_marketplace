<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
    ];

    // Générer automatiquement le slug
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($permission) {
            if (empty($permission->slug)) {
                $permission->slug = Str::slug($permission->name);
            }
        });
        
        static::updating(function ($permission) {
            if ($permission->isDirty('name') && empty($permission->slug)) {
                $permission->slug = Str::slug($permission->name);
            }
        });
    }

    // Relations
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    // Scopes
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }
}
