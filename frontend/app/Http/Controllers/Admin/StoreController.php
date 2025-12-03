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
            $status = strtolower($request->status);
            $query->where(function ($q) use ($status) {
                $q->where('status', $status)
                  ->orWhereRaw('LOWER(crm_kyc_status) = ?', [$status]);
            });
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
        ]);

        $store->update($request->only(['name', 'description']));

        return redirect()->back()->with('success', 'Boutique mise à jour avec succès.');
    }

    public function destroy(Store $store)
    {
        $store->delete();
        return redirect()->back()->with('success', 'Boutique supprimée avec succès.');
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

