<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->guard('admin')->check() || !auth()->guard('admin')->user()->isSuperAdmin()) {
            return redirect('/superadmin/login')->with('error', 'Accès restreint au Super Admin.');
        }

        return $next($request);
    }
}