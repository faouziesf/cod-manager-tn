<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isManager()) {
            return redirect('/login')->with('error', 'Accès restreint aux managers.');
        }

        return $next($request);
    }
}