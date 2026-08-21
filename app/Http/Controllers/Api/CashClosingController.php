<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Closing;
use App\Models\Payment;
use App\Services\CashRegisterService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashClosingController extends Controller
{
    public function __construct(private CashRegisterService $cash) {}

    // Resumo do dia (fechar caixa).
    public function show(string $date)
    {
        return $this->cash->daySummary($date);
    }

    // Persiste o fechamento do dia (snapshot dos totais).
    public function close(Request $request, string $date)
    {
        $summary = $this->cash->daySummary($date);

        $closing = Closing::updateOrCreate(
            ['closed_on' => Carbon::parse($date)->toDateString()],
            [
                'gross' => $summary['gross'],
                'expenses_total' => $summary['expenses_total'],
                'collaborators_total' => $summary['collaborators_total'],
                'net' => $summary['net'],
                'closed_by' => $request->user()?->id,
            ]
        );

        return response()->json(['closing' => $closing, 'summary' => $this->cash->daySummary($date)]);
    }

    // Marca/desmarca um colaborador como pago no dia (comissão do próprio dia).
    public function togglePaid(Request $request, string $date)
    {
        $data = $request->validate(['collaborator_id' => ['required', 'exists:collaborators,id']]);
        $day = Carbon::parse($date)->toDateString();

        $existing = Payment::whereDate('paid_on', $day)
            ->where('collaborator_id', $data['collaborator_id'])
            ->where('manual', false)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['paid' => false, 'summary' => $this->cash->daySummary($date)]);
        }

        // Comissão do colaborador nesse dia.
        $amount = (float) Appointment::concluded()->with('services')
            ->whereDate('scheduled_at', $day)
            ->where('collaborator_id', $data['collaborator_id'])
            ->get()->sum('commission_total');

        Payment::create([
            'collaborator_id' => $data['collaborator_id'],
            'amount' => $amount,
            'paid_on' => $day,
            'manual' => false,
            'user_id' => $request->user()?->id,
        ]);

        return response()->json(['paid' => true, 'summary' => $this->cash->daySummary($date)]);
    }
}
