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
        $data = $request->validate([
            'name' => ['required', 'string'],
            'username' => ['required', 'string', 'unique:users,username'],
            'password' => ['required', 'string', 'min:4'],
            'role' => ['required', Rule::in(['admin', 'colaborador'])],
        ]);
        return response()->json(User::create($data), 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'username' => ['required', 'string', 'unique:users,username,' . $user->id],
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
        if (User::count() <= 1) {
            abort(422, 'Não é possível excluir o único usuário.');
        }
        $user->delete();
        return response()->noContent();
    }
}
