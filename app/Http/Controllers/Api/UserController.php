<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return User::orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $data = $request->validate([
            'name' => ['required', 'string'],
            'username' => ['required', 'string', Rule::unique('users', 'username')->where(fn ($q) => $q->where('tenant_id', $tenantId))],
            'password' => ['required', 'string', 'min:4'],
            'role' => ['required', Rule::in(['admin', 'colaborador'])],
        ]);
        return response()->json(User::create($data), 201);
    }

    public function update(Request $request, User $user)
    {
        $tenantId = $request->user()->tenant_id;
        $data = $request->validate([
            'name' => ['required', 'string'],
            'username' => ['required', 'string', Rule::unique('users', 'username')->where(fn ($q) => $q->where('tenant_id', $tenantId))->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:4'],
            'role' => ['required', Rule::in(['admin', 'colaborador'])],
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);
        return $user;
    }

    public function destroy(User $user)
    {
        // Não deixa remover o último administrador (senão ninguém gerencia o ambiente).
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            abort(422, 'Não é possível excluir o único administrador.');
        }
        $user->delete();
        return response()->noContent();
    }
}
