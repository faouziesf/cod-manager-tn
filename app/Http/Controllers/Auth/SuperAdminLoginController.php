<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SuperAdminLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.superadmin-login');
    }

    public function login(Request $request)
    {
        // Utilisez la méthode validate() de la requête au lieu de this->validate()
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        $credentials = $request->only('email', 'password');
        
        if (Auth::guard('admin')->attempt($credentials)) {
            $admin = Auth::guard('admin')->user();
            
            if (!$admin->is_super_admin) {
                Auth::guard('admin')->logout();
                return back()->withErrors(['email' => 'Vous n\'avez pas les droits super administrateur.']);
            }
            
            return redirect()->intended(route('superadmin.dashboard'));
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
        ]);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect('/superadmin/login');
    }
}