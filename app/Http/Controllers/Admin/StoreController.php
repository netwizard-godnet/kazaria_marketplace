<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Store::with(['user', 'approver', 'rejector']);
        
        // Filtre par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filtre par validation
        if ($request->filled('validation')) {
            if ($request->validation === 'approved') {
                $query->whereNotNull('approved_at');
            } elseif ($request->validation === 'rejected') {
                $query->whereNotNull('rejected_at');
            } elseif ($request->validation === 'pending') {
                $query->whereNull('approved_at')->whereNull('rejected_at');
            }
        }
        
        // Recherche par nom
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Filtre par documents
        if ($request->filled('documents')) {
            if ($request->documents === 'complete') {
                $query->whereNotNull('logo')
                      ->whereNotNull('banner')
                      ->whereNotNull('dfe_document')
                      ->whereNotNull('commerce_register');
            } elseif ($request->documents === 'incomplete') {
                $query->where(function($q) {
                    $q->whereNull('logo')
                      ->orWhereNull('banner')
                      ->orWhereNull('dfe_document')
                      ->orWhereNull('commerce_register');
                })->where(function($q) {
                    $q->whereNotNull('logo')
                      ->orWhereNotNull('banner')
                      ->orWhereNotNull('dfe_document')
                      ->orWhereNotNull('commerce_register');
                });
            } elseif ($request->documents === 'no_documents') {
                $query->whereNull('logo')
                      ->whereNull('banner')
                      ->whereNull('dfe_document')
                      ->whereNull('commerce_register');
            }
        }
        
        $stores = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());
        
        return view('admin.stores.index', compact('stores'));
    }


    public function show(Store $store)
    {
        $store->load(['user', 'products', 'approver', 'rejector']);
        return view('admin.stores.show', compact('store'));
    }

    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended,rejected',
        ]);

        $store->update($request->all());

        return redirect()->back()->with('success', 'Boutique mise à jour avec succès.');
    }

    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->back()->with('success', 'Boutique supprimée avec succès.');
    }

    public function approve(Store $store)
    {
        // Vérifier que la boutique n'est pas déjà approuvée ou rejetée
        if ($store->approved_at) {
            return redirect()->back()->with('error', 'Cette boutique a déjà été approuvée.');
        }
        
        if ($store->rejected_at) {
            return redirect()->back()->with('error', 'Cette boutique a été rejetée et ne peut pas être approuvée.');
        }
        
        $store->update([
            'status' => 'active',
            'is_verified' => true,
            'approved_at' => now(),
            'approved_by' => auth()->id()
        ]);
        
        // TODO: Envoyer un email de notification au vendeur
        
        return redirect()->back()->with('success', 'Boutique approuvée avec succès. Le vendeur a été notifié.');
    }

    public function reject(Request $request, Store $store)
    {
        // Vérifier que la boutique n'est pas déjà approuvée ou rejetée
        if ($store->approved_at) {
            return redirect()->back()->with('error', 'Cette boutique a déjà été approuvée et ne peut pas être rejetée.');
        }
        
        if ($store->rejected_at) {
            return redirect()->back()->with('error', 'Cette boutique a déjà été rejetée.');
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);
        
        $store->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => $request->rejection_reason
        ]);
        
        // TODO: Envoyer un email de notification au vendeur
        
        return redirect()->back()->with('success', 'Boutique rejetée avec succès. Le vendeur a été notifié.');
    }

    public function toggleStatus(Store $store)
    {
        // Logique pour basculer entre actif et suspendu
        if ($store->status === 'active') {
            $newStatus = 'suspended';
            $message = 'Boutique suspendue avec succès.';
        } elseif ($store->status === 'suspended') {
            $newStatus = 'active';
            $message = 'Boutique réactivée avec succès.';
        } else {
            // Pour les autres statuts (pending, rejected), on active la boutique
            $newStatus = 'active';
            $message = 'Boutique activée avec succès.';
        }
        
        $store->update(['status' => $newStatus]);
        return redirect()->back()->with('success', $message);
    }

    public function toggleOfficial(Store $store)
    {
        // Basculer le statut "Boutique officielle"
        $store->update(['is_official' => !$store->is_official]);
        
        $message = $store->is_official 
            ? 'Boutique officielle activée avec succès.' 
            : 'Boutique officielle désactivée avec succès.';
        
        return redirect()->back()->with('success', $message);
    }
}

