<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = \App\Models\Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = $request->input('settings', []);
        
        foreach ($settings as $key => $value) {
            // Si c'est un tableau (sélections multiples), convertir en string avec virgules
            if (is_array($value)) {
                $value = implode(',', array_filter($value)); // Filtrer les valeurs vides
            }
            
            \App\Models\Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value ?? '']
            );
        }
        
        // Gérer les uploads de fichiers
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoPath = $logo->store('logos', 'public');
            \App\Models\Setting::updateOrCreate(
                ['key' => 'site_logo'],
                ['value' => $logoPath]
            );
        }
        
        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon');
            $faviconPath = $favicon->store('favicons', 'public');
            \App\Models\Setting::updateOrCreate(
                ['key' => 'site_favicon'],
                ['value' => $faviconPath]
            );
        }
        
        return redirect()->back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    public function backup()
    {
        try {
            Artisan::call('backup:run');
            return redirect()->back()->with('success', 'Sauvegarde créée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la sauvegarde: ' . $e->getMessage());
        }
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            return redirect()->back()->with('success', 'Cache vidé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors du vidage du cache: ' . $e->getMessage());
        }
    }
}

