<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('manager.login');
        }
        
        $user = Auth::user();
        
        // Vérifier si l'utilisateur est actif
        if (!$user->active) {
            Auth::logout();
            return redirect()->route('manager.login')
                ->withErrors(['email' => 'Ce compte est désactivé.']);
        }
        
        // Vérifier si l'utilisateur a un rôle valide (manager ou employee)
        if (!in_array($user->role, ['manager', 'employee'])) {
            Auth::logout();
            return redirect()->route('manager.login')
                ->withErrors(['email' => 'Vous n\'avez pas les autorisations nécessaires.']);
        }
        
        return $next($request);
    }
}