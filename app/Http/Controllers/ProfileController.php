<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Afficher le profil de l'utilisateur
     */
    public function index()
    {
        $user = Auth::user();
        return view('profil', compact('user'));
    }
}

