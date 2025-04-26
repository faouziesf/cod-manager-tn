<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admins = Admin::where('is_super_admin', false)->get();
        return view('superadmin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.admins.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8|confirmed',
            'expiry_date' => 'nullable|date',
        ]);

        $admin = new Admin();
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->password = Hash::make($request->password);
        $admin->active = $request->has('active') ? true : false; // Gérer correctement la case à cocher
        $admin->expiry_date = $request->expiry_date;
        $admin->is_super_admin = false;
        $admin->save();

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Administrateur créé avec succès.');
            
    } catch (\Exception $e) {
        // Log l'erreur
        \Log::error('Erreur lors de la création d\'un admin: ' . $e->getMessage());
        
        // Rediriger avec l'erreur
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
    }
}

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin)
    {
        return view('superadmin.admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admin $admin)
    {
        if ($admin->is_super_admin) {
            return redirect()->route('superadmin.admins.index')
                ->with('error', 'Vous ne pouvez pas modifier un Super Admin.');
        }
        
        return view('superadmin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Admin $admin)
    {
        if ($admin->is_super_admin) {
            return redirect()->route('superadmin.admins.index')
                ->with('error', 'Vous ne pouvez pas modifier un Super Admin.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed',
            'active' => 'boolean',
            'expiry_date' => 'nullable|date',
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;
        
        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }
        
        $admin->active = $request->has('active');
        $admin->expiry_date = $request->expiry_date;
        $admin->save();

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Administrateur mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        if ($admin->is_super_admin) {
            return redirect()->route('superadmin.admins.index')
                ->with('error', 'Vous ne pouvez pas supprimer un Super Admin.');
        }

        $admin->delete();

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Administrateur supprimé avec succès.');
    }
}