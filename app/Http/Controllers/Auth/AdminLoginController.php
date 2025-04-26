<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.admin-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        $credentials = $request->only('email', 'password');
        
        if (Auth::guard('admin')->attempt($credentials)) {
            $admin = Auth::guard('admin')->user();
            
            // Si c'est un super admin, redirigez-le vers le tableau de bord du super admin
            if ($admin->is_super_admin) {
                return redirect()->route('superadmin.dashboard');
            }
            
            // Vérifiez si le compte est actif
            if (!$admin->active) {
                Auth::guard('admin')->logout();
                return back()->withErrors(['email' => 'Votre compte est désactivé.']);
            }
            
            // Vérifiez si le compte est expiré
            if ($admin->expiry_date && now()->gt($admin->expiry_date)) {
                Auth::guard('admin')->logout();
                return back()->withErrors(['email' => 'Votre compte a expiré.']);
            }
            
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
        ]);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect('/admin/login');
    }
}