<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\CashRegisterService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // Extrato por colaborador: ganho x pago x a receber.
    public function ledger(CashRegisterService $cash)
    {
        return $cash->ledger();
    }

    public function index(Request $request)
    {
        $q = Payment::with('collaborator')->orderByDesc('paid_on');
        if ($request->filled('collaborator_id')) {
            $q->where('collaborator_id', $request->collaborator_id);
        }
        return $q->get();
    }

    // Pagamento avulso (valor livre) a um colaborador.
    public function store(Request $request)
    {
        $data = $request->validate([
            'collaborator_id' => ['required', 'exists:collaborators,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_on' => ['required', 'date_format:Y-m-d'],
        ]);
        $data['manual'] = true;
        $data['user_id'] = $request->user()?->id;
        return response()->json(Payment::create($data), 201);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->noContent();
    }
}
