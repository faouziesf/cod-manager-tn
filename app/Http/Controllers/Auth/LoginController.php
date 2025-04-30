<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Afficher le formulaire de connexion générique
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        // Par défaut, on utilise 'user' comme type d'utilisateur
        $userType = 'user';
        
        // Déterminer le type d'utilisateur en fonction de l'URL
        $path = request()->path();
        if (strpos($path, 'admin') !== false) {
            $userType = 'admin';
        } elseif (strpos($path, 'manager') !== false) {
            $userType = 'manager';
        } elseif (strpos($path, 'employee') !== false) {
            $userType = 'employee';
        } elseif (strpos($path, 'superadmin') !== false) {
            $userType = 'superadmin';
        }
        
        return view('auth.login', compact('userType'));
    }

    /**
     * Traiter la demande de connexion
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

        // Déterminer le garde à utiliser en fonction de l'URL
        $path = request()->path();
        $guard = 'web'; // Par défaut
        $redirectRoute = 'login';
        
        if (strpos($path, 'admin') !== false) {
            $guard = 'admin';
            $redirectRoute = 'admin.dashboard';
        } elseif (strpos($path, 'superadmin') !== false) {
            $guard = 'admin'; // SuperAdmin utilise le même garde que Admin
            $redirectRoute = 'superadmin.dashboard';
        } elseif (strpos($path, 'manager') !== false || strpos($path, 'employee') !== false) {
            $guard = 'web';
            
            // Vérifier si l'utilisateur existe et si son rôle correspond à la route
            $user = \App\Models\User::where('email', $credentials['email'])->first();
            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['Les informations d\'identification fournies ne correspondent pas à nos enregistrements.'],
                ]);
            }
            
            // Vérifier le rôle
            if (strpos($path, 'manager') !== false && $user->role !== 'manager') {
                throw ValidationException::withMessages([
                    'email' => ['Vous n\'avez pas les autorisations nécessaires pour accéder en tant que manager.'],
                ]);
            } elseif (strpos($path, 'employee') !== false && $user->role !== 'employee') {
                throw ValidationException::withMessages([
                    'email' => ['Vous n\'avez pas les autorisations nécessaires pour accéder en tant qu\'employé.'],
                ]);
            }
            
            // Définir la redirection
            $redirectRoute = $user->role === 'manager' ? 'manager.dashboard' : 'employee.dashboard';
        }

        // Tentative de connexion
        if (!Auth::guard($guard)->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies ne correspondent pas à nos enregistrements.'],
            ]);
        }

        // Vérifier si l'utilisateur est actif
        $user = Auth::guard($guard)->user();
        if (!$user->active) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Ce compte est désactivé.'],
            ]);
        }

        // Si c'est un admin, vérifier aussi la date d'expiration
        if ($guard === 'admin' && $user->expiry_date && now()->greaterThan($user->expiry_date)) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Ce compte a expiré.'],
            ]);
        }

        // SuperAdmin spécifique
        if (strpos($path, 'superadmin') !== false && !$user->is_super_admin) {
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => ['Vous n\'avez pas les autorisations nécessaires pour accéder en tant que Super Admin.'],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($redirectRoute));
    }

    /**
     * Déconnecter l'utilisateur
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        // Déterminer le garde à utiliser
        $path = request()->path();
        $guard = 'web';
        $redirectRoute = 'login';
        
        if (strpos($path, 'admin') !== false) {
            $guard = 'admin';
            $redirectRoute = 'admin.login';
        } elseif (strpos($path, 'superadmin') !== false) {
            $guard = 'admin';
            $redirectRoute = 'superadmin.login';
        } elseif (strpos($path, 'manager') !== false) {
            $guard = 'web';
            $redirectRoute = 'manager.login';
        } elseif (strpos($path, 'employee') !== false) {
            $guard = 'web';
            $redirectRoute = 'employee.login';
        }

        Auth::guard($guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($redirectRoute);
    }
}