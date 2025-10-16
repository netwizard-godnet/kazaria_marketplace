<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Intervention\Image\Facades\Image;

class AvatarController extends Controller
{
    /**
     * Générer un avatar pour les emails
     */
    public function generateEmailAvatar(Request $request)
    {
        $text = $request->get('text', 'K');
        $size = $request->get('size', 64);
        $bgColor = $request->get('bg', '#ff6b35');
        $textColor = $request->get('text_color', '#ffffff');
        
        // Créer l'image
        $img = Image::canvas($size, $size, $bgColor);
        
        // Ajouter le texte
        $img->text($text, $size/2, $size/2, function($font) use ($size, $textColor) {
            $font->file(public_path('fonts/arial.ttf')); // Assurez-vous d'avoir une police
            $font->size($size * 0.6);
            $font->color($textColor);
            $font->align('center');
            $font->valign('middle');
        });
        
        return $img->response('png');
    }
    
    /**
     * Avatar avec logo KAZARIA
     */
    public function kazariaAvatar()
    {
        $logoPath = public_path('images/logo.png');
        
        if (file_exists($logoPath)) {
            $img = Image::make($logoPath);
            $img->resize(64, 64);
            
            return $img->response('png');
        }
        
        // Fallback : avatar avec lettre K
        return $this->generateEmailAvatar(request()->merge(['text' => 'K', 'bg' => '#ff6b35']));
    }
}
