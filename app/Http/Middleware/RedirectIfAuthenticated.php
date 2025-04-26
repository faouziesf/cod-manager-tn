<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if ($guard === 'admin') {
                    $admin = Auth::guard('admin')->user();
                    if ($admin->is_super_admin) {
                        return redirect()->route('superadmin.dashboard');
                    }
                    return redirect()->route('admin.dashboard');
                }
                
                $user = Auth::user();
                if ($user->isManager()) {
                    return redirect()->route('manager.dashboard');
                }
                
                return redirect()->route('employee.dashboard');
            }
        }

        return $next($request);
    }
}