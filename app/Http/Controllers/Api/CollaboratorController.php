<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collaborator;
use Illuminate\Http\Request;

class CollaboratorController extends Controller
{
    public function index()
    {
        return Collaborator::with('services')->orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $collaborator = Collaborator::create($data);
        $this->syncCommissions($collaborator, $request->input('commissions', []));
        return response()->json($collaborator->load('services'), 201);
    }

    public function update(Request $request, Collaborator $collaborator)
    {
        $collaborator->update($this->validated($request));
        $this->syncCommissions($collaborator, $request->input('commissions', []));
        return $collaborator->load('services');
    }

    public function destroy(Collaborator $collaborator)
    {
        // Protege o histórico financeiro: não exclui quem já tem pagamentos.
        if ($collaborator->payments()->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir: há pagamentos vinculados a este colaborador. Remova os pagamentos antes.',
            ], 422);
        }

        $collaborator->delete();
        return response()->noContent();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string'],
            'cpf' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'pix' => ['nullable', 'string'],
        ]);
    }

    // commissions = { service_id: valor }  (ganho do colaborador por serviço)
    private function syncCommissions(Collaborator $collaborator, array $commissions): void
    {
        $sync = [];
        foreach ($commissions as $serviceId => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $sync[(int) $serviceId] = ['commission' => (float) $value];
        }
        $collaborator->services()->sync($sync);
    }
}
