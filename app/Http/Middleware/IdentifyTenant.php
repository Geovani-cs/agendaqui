<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Sem usuario ou super-admin (tenant_id null) -> nao vincula tenant.
        // Nesse caso o global scope nao filtra (contexto landlord/painel do dono).
        if (! $user || ! $user->tenant_id) {
            return $next($request);
        }

        $tenant = $user->tenant; // relacao vinda da trait BelongsToTenant

        if (! $tenant) {
            abort(403, 'Tenant nao encontrado para este usuario.');
        }

        // Bloqueado: sem acesso.
        if ($tenant->status === 'blocked') {
            abort(403, 'Assinatura suspensa. Regularize para voltar a acessar.');
        }

        // Somente leitura: permite GET/HEAD, barra escrita.
        if ($tenant->status === 'read_only' && ! $request->isMethodSafe()) {
            abort(423, 'Conta em modo somente leitura. Regularize a mensalidade para voltar a editar.');
        }

        // A partir daqui, todo o global scope filtra por este tenant.
        app()->instance('currentTenant', $tenant);

        return $next($request);
    }
}
