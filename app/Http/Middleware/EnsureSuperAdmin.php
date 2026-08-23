<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $u = $request->user();
        // Dono da plataforma: sem tenant e com papel super_admin.
        if (! $u || $u->tenant_id !== null || $u->role !== 'super_admin') {
            abort(403, 'Acesso restrito ao dono da plataforma.');
        }
        return $next($request);
    }
}
