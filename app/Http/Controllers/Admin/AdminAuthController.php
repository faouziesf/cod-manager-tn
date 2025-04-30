<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AdminAuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login', ['userType' => 'admin']);
    }

    /**
     * Authentifier l'administrateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies ne correspondent pas à nos enregistrements.'],
            ]);
        }

        $admin = Auth::guard('admin')->user();

        // Vérifier si l'admin est actif
        if (!$admin->active) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Ce compte administrateur est désactivé.'],
            ]);
        }

        // Vérifier si l'admin a une date d'expiration et si elle est dépassée
        if ($admin->expiry_date && Carbon::parse($admin->expiry_date)->isPast()) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Ce compte administrateur a expiré.'],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Déconnecter l'administrateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}