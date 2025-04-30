<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminMiddleware
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
        // Vérifier si l'utilisateur est connecté en tant qu'admin
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('admin.login');
        }
        
        // Vérifier si l'utilisateur est un super admin
        if (!Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'Accès refusé. Seuls les super administrateurs peuvent accéder à cette ressource.');
        }
        
        return $next($request);
    }
}