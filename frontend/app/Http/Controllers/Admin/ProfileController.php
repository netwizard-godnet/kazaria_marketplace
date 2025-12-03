<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Afficher le profil de l'admin
     */
    public function index()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Afficher le formulaire d'édition du profil
     */
    public function edit()
    {
        $user = Auth::user();
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Mettre à jour les informations du profil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'prenoms' => 'required|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['prenoms', 'nom', 'email', 'telephone', 'adresse']);

        // Gestion de la photo de profil
        if ($request->hasFile('profile_pic')) {
            // Supprimer l'ancienne photo si elle existe
            if ($user->profile_pic_url) {
                $oldPath = str_replace('storage/', '', $user->profile_pic_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Sauvegarder la nouvelle photo
            $path = $request->file('profile_pic')->store('images/profiles', 'public');
            $data['profile_pic_url'] = 'storage/' . $path;
        }

        $user->update($data);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Profil mis à jour avec succès.');
    }

    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function editPassword()
    {
        return view('admin.profile.password');
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.profile.index')
            ->with('success', 'Mot de passe mis à jour avec succès.');
    }

    /**
     * Supprimer la photo de profil
     */
    public function deleteProfilePic()
    {
        $user = Auth::user();

        if ($user->profile_pic_url) {
            $path = str_replace('storage/', '', $user->profile_pic_url);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            $user->update(['profile_pic_url' => null]);
        }

        return redirect()->route('admin.profile.index')
            ->with('success', 'Photo de profil supprimée avec succès.');
    }
}