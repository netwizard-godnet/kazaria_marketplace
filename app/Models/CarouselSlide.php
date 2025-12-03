<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarouselSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'image_path', 'link_url', 'button_text', 'sort_order', 'is_active', 'starts_at', 'ends_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        
        // Si l'image est déjà dans le dossier public/images, retourner directement
        if (strpos($this->image_path, 'images/') === 0) {
            return asset($this->image_path);
        }
        
        // Sinon, c'est dans le storage (via lien symbolique)
        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
