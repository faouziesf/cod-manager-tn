<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ManagerAuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login', ['userType' => 'manager']);
    }

    /**
     * Authentifier l'utilisateur
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

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies ne correspondent pas à nos enregistrements.'],
            ]);
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur est actif
        if (!$user->active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Ce compte est désactivé.'],
            ]);
        }

        // Vérifier si l'utilisateur a un rôle valide (manager ou employee)
        if (!in_array($user->role, ['manager', 'employee'])) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Vous n\'avez pas les autorisations nécessaires pour accéder à cette interface.'],
            ]);
        }

        $request->session()->regenerate();

        // Rediriger vers le tableau de bord approprié selon le rôle
        if ($user->role === 'manager') {
            return redirect()->intended(route('manager.dashboard'));
        } else {
            return redirect()->intended(route('employee.dashboard'));
        }
    }

    /**
     * Déconnecter l'utilisateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('manager.login');
    }
}