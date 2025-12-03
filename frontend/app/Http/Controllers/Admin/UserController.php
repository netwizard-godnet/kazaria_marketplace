<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['store', 'orders', 'role']);
        
        // Filtres
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenoms', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('role')) {
            switch ($request->role) {
                case 'admin':
                    $query->where('is_admin', true);
                    break;
                case 'seller':
                    $query->where('is_seller', true);
                    break;
                case 'customer':
                    $query->where('is_seller', false)->where('is_admin', false);
                    break;
            }
        }
        
        if ($request->filled('status')) {
            $query->where('is_verified', $request->status === 'active');
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);
        
        $users = $query->paginate(15)->withQueryString();
        
        // Statistiques
        $stats = [
            'total' => User::count(),
            'admins' => User::where('is_admin', true)->count(),
            'sellers' => User::where('is_seller', true)->count(),
            'customers' => User::where('is_seller', false)->where('is_admin', false)->count(),
            'verified' => User::where('is_verified', true)->count(),
            'unverified' => User::where('is_verified', false)->count(),
        ];
        
        return view('admin.users.index', compact('users', 'stats'));
    }

    public function sellers()
    {
        $users = User::where('is_seller', true)->with('store')->paginate(15);
        return view('admin.users.sellers', compact('users'));
    }

    public function customers()
    {
        $users = User::where('is_seller', false)->where('is_admin', false)->paginate(15);
        return view('admin.users.customers', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['store', 'orders', 'role']);
        
        // Si c'est une requête JSON/AJAX, retourner JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        }
        
        return view('admin.users.show', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id',
            'is_verified' => 'boolean',
            'is_seller' => 'boolean',
            'is_admin' => 'boolean',
        ]);

        $data = $request->only([
            'nom', 'prenoms', 'email', 'telephone', 'role_id'
        ]);

        // Mot de passe
        $data['password'] = bcrypt($request->password);

        // Gérer les rôles
        $data['is_admin'] = $request->boolean('is_admin');
        $data['is_seller'] = $request->boolean('is_seller');
        $data['is_verified'] = $request->boolean('is_verified');
        
        // Email vérifié automatiquement pour les nouveaux utilisateurs
        $data['email_verified_at'] = now();

        $user = User::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Utilisateur créé avec succès.',
                'user' => $user->fresh()
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'telephone' => 'nullable|string|max:20',
            'ville' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:10',
            'adresse' => 'nullable|string|max:500',
            'role_id' => 'nullable|exists:roles,id',
            'is_verified' => 'boolean',
            'is_seller' => 'boolean',
            'is_admin' => 'boolean',
            'email_verified_at' => 'nullable|date',
        ]);

        // Préparer les données à mettre à jour
        $data = $request->only([
            'nom', 'prenoms', 'email', 'telephone', 'ville', 'code_postal', 'adresse', 'role_id'
        ]);
        
        // Convertir role_id vide en null
        if (isset($data['role_id']) && $data['role_id'] === '') {
            $data['role_id'] = null;
        }
        
        // Gérer les rôles
        $data['is_admin'] = $request->boolean('is_admin');
        $data['is_seller'] = $request->boolean('is_seller');
        $data['is_verified'] = $request->boolean('is_verified');
        
        // Gérer l'email vérifié
        if ($request->has('email_verified_at')) {
            $data['email_verified_at'] = $request->email_verified_at;
        }

        $user->update($data);

        // Si c'est une requête AJAX, retourner JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Utilisateur mis à jour avec succès.',
                'user' => $user->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        if ($user->is_admin) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un administrateur.'
                ], 403);
            }
            return redirect()->back()->with('error', 'Impossible de supprimer un administrateur.');
        }

        $user->delete();
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès.'
            ]);
        }
        
        return redirect()->back()->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_verified' => !$user->is_verified]);
        
        $status = $user->is_verified ? 'activé' : 'désactivé';
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Utilisateur {$status} avec succès.",
                'user' => $user->fresh()
            ]);
        }
        
        return redirect()->back()->with('success', "Utilisateur {$status} avec succès.");
    }

    public function profile()
    {
        return view('admin.profile');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenoms' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'telephone' => 'nullable|string|max:20',
        ]);

        auth()->user()->update($request->all());

        return redirect()->back()->with('success', 'Profil mis à jour avec succès.');
    }
}

