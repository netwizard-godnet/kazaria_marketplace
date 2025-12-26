<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Afficher la liste des rôles
     */
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Créer un nouveau rôle
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        // Attacher les permissions au rôle
        if ($request->filled('permissions') && is_array($request->permissions)) {
            $role->permissions()->attach($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle créé avec succès.');
    }

    /**
     * Afficher les détails d'un rôle
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get()->groupBy('module');
        $role->load('permissions');
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Mettre à jour un rôle
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        // Mettre à jour les permissions
        if ($request->filled('permissions') && is_array($request->permissions)) {
            $role->permissions()->sync($request->permissions);
        } else {
            // Si aucune permission n'est sélectionnée, détacher toutes les permissions
            $role->permissions()->sync([]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Supprimer un rôle
     */
    public function destroy(Role $role)
    {
        // Ne pas supprimer les rôles avec des utilisateurs associés
        if ($role->users()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer un rôle assigné à des utilisateurs.');
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle supprimé avec succès.');
    }
}
