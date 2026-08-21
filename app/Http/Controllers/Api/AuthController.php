<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'tenant'   => ['nullable', 'string'], // slug do ambiente: agendaqui.com.br/{tenant}
        ]);

        // O global scope de User esta inativo aqui (sem currentTenant no login),
        // entao escopamos manualmente por tenant para nao vazar login entre clientes.
        $query = User::where('username', $data['username']);

        if (! empty($data['tenant'])) {
            $tenant = Tenant::where('slug', $data['tenant'])->first();
            if (! $tenant) {
                throw ValidationException::withMessages(['tenant' => 'Ambiente nao encontrado.']);
            }
            if ($tenant->status === 'blocked') {
                throw ValidationException::withMessages(['tenant' => 'Assinatura suspensa. Regularize para acessar.']);
            }
            $query->where('tenant_id', $tenant->id);
        } else {
            // Sem slug: login do dono da plataforma (landlord / super-admin).
            $query->whereNull('tenant_id');
        }

        $user = $query->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['username' => 'Usuario ou senha invalidos.']);
        }

        $token = $user->createToken('app')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }

    public function me(Request $request)
    {
        return $request->user();
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sessao encerrada.']);
    }
}
