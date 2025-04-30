<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if ($guard === 'admin') {
                    // Déterminer la redirection en fonction du chemin
                    $path = $request->path();
                    if (strpos($path, 'superadmin') !== false) {
                        // Vérifier si l'admin est un super admin
                        if (Auth::guard('admin')->user()->is_super_admin) {
                            return redirect()->route('superadmin.dashboard');
                        } else {
                            // Si ce n'est pas un super admin, déconnecter
                            Auth::guard('admin')->logout();
                            return redirect()->route('superadmin.login')
                                ->withErrors(['email' => 'Vous n\'avez pas les droits de super administrateur.']);
                        }
                    } else {
                        return redirect()->route('admin.dashboard');
                    }
                } else {
                    // Pour les utilisateurs standard (manager/employee)
                    $user = Auth::user();
                    $path = $request->path();
                    
                    if (strpos($path, 'manager') !== false) {
                        if ($user->role === 'manager') {
                            return redirect()->route('manager.dashboard');
                        } else {
                            Auth::logout();
                            return redirect()->route('manager.login')
                                ->withErrors(['email' => 'Vous n\'avez pas les droits de manager.']);
                        }
                    } elseif (strpos($path, 'employee') !== false) {
                        if ($user->role === 'employee') {
                            return redirect()->route('employee.dashboard');
                        } else {
                            Auth::logout();
                            return redirect()->route('employee.login')
                                ->withErrors(['email' => 'Vous n\'avez pas les droits d\'employé.']);
                        }
                    } else {
                        // Redirection par défaut basée sur le rôle
                        if ($user->role === 'manager') {
                            return redirect()->route('manager.dashboard');
                        } elseif ($user->role === 'employee') {
                            return redirect()->route('employee.dashboard');
                        } else {
                            return redirect(RouteServiceProvider::HOME);
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}