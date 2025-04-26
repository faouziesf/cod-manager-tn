<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $users = $admin->users()->orderBy('role')->get();
        
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:manager,employee',
        ]);

        $admin = Auth::guard('admin')->user();
        
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->role;
        $user->active = $request->has('active') ? true : false;
        $user->admin_id = $admin->id;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que l'utilisateur appartient à cet admin
        if ($user->admin_id !== $admin->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous n\'avez pas accès à cet utilisateur.');
        }
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que l'utilisateur appartient à cet admin
        if ($user->admin_id !== $admin->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous n\'avez pas accès à cet utilisateur.');
        }
        
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que l'utilisateur appartient à cet admin
        if ($user->admin_id !== $admin->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous n\'avez pas accès à cet utilisateur.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:manager,employee',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        
        $user->role = $request->role;
        $user->active = $request->has('active') ? true : false;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $admin = Auth::guard('admin')->user();
        
        // Vérifier que l'utilisateur appartient à cet admin
        if ($user->admin_id !== $admin->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous n\'avez pas accès à cet utilisateur.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
}