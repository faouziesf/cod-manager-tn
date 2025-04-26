<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        $credentials = $request->only('email', 'password');
        
        // Essayer de connecter en tant qu'admin
        if (Auth::guard('admin')->attempt($credentials)) {
            $admin = Auth::guard('admin')->user();
            
            // Rediriger selon le type d'admin
            if ($admin->is_super_admin) {
                return redirect()->route('superadmin.dashboard');
            } else {
                return redirect()->route('admin.dashboard');
            }
        }
        
        // Essayer de connecter en tant qu'utilisateur
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            if (!$user->active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Votre compte est désactivé.']);
            }
            
            if ($user->isManager()) {
                return redirect()->route('manager.dashboard');
            } else {
                return redirect()->route('employee.dashboard');
            }
        }

        // Échec de connexion
        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
        ]);
    }

    public function logout(Request $request)
    {
        // Déconnecter de toutes les gardes
        Auth::guard('admin')->logout();
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}