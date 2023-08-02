<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{

    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user();
        
        if (!$user || !in_array($user->id_rol, $roles)) {
            return response()->json(['error' => 'Acceso no autorizado'], 403);
        }
        
        return $next($request);
    }
}
