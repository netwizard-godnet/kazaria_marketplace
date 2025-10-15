<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    /**
     * Servir une image depuis le storage
     */
    public function serve($path)
    {
        // Nettoyer le chemin
        $path = str_replace('..', '', $path);
        $path = ltrim($path, '/');
        
        // Chemin complet vers le fichier
        $fullPath = storage_path('app/public/' . $path);
        
        // Vérifier que le fichier existe
        if (!File::exists($fullPath)) {
            abort(404, 'Image non trouvée');
        }
        
        // Obtenir le type MIME
        $mimeType = File::mimeType($fullPath);
        
        // Lire le contenu du fichier
        $fileContents = File::get($fullPath);
        
        // Retourner la réponse avec les headers appropriés
        return response($fileContents, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=31536000')
            ->header('Content-Length', strlen($fileContents));
    }
    
    /**
     * Servir le logo d'une boutique
     */
    public function storeLogo($storeId, $filename)
    {
        $path = "stores/logos/{$filename}";
        return $this->serve($path);
    }
    
    /**
     * Servir la bannière d'une boutique
     */
    public function storeBanner($storeId, $filename)
    {
        $path = "stores/banners/{$filename}";
        return $this->serve($path);
    }
    
    /**
     * Servir une image de produit
     */
    public function productImage($productId, $filename)
    {
        $path = "products/{$filename}";
        return $this->serve($path);
    }
}
