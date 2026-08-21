<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Collaborator;
use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;

class CashRegisterService
{
    // Resumo do caixa de um dia: bruto, despesas, comissões por colaborador,
    // total de colaboradores, líquido e status de pagamento do dia.
    public function daySummary(string $date): array
    {
        $day = Carbon::parse($date)->toDateString();

        $appointments = Appointment::concluded()
            ->with(['services', 'collaborator'])
            ->whereDate('scheduled_at', $day)
            ->get();

        $gross = (float) $appointments->sum('value');

        $expenses = Expense::whereDate('spent_on', $day)->get();
        $expensesTotal = (float) $expenses->sum('value');

        // Agrupa comissões por colaborador.
        $groups = [];
        foreach ($appointments as $a) {
            $key = $a->collaborator_id ?: '_sem_';
            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'collaborator_id' => $a->collaborator_id,
                    'name' => $a->collaborator?->name ?? 'Sem colaborador',
                    'count' => 0,
                    'earning' => 0.0,
                ];
            }
            $groups[$key]['count']++;
            $groups[$key]['earning'] += (float) $a->commission_total;
        }

        // Marca quem já foi pago no dia (pagamento não-avulso).
        $paidToday = Payment::whereDate('paid_on', $day)
            ->where('manual', false)
            ->pluck('collaborator_id')->all();

        $collaborators = array_values(array_map(function ($g) use ($paidToday) {
            $g['paid'] = $g['collaborator_id'] && in_array($g['collaborator_id'], $paidToday);
            return $g;
        }, $groups));

        $collaboratorsTotal = array_sum(array_column($collaborators, 'earning'));
        $net = $gross - $expensesTotal - $collaboratorsTotal;

        return [
            'date' => $day,
            'gross' => round($gross, 2),
            'expenses_total' => round($expensesTotal, 2),
            'collaborators_total' => round($collaboratorsTotal, 2),
            'net' => round($net, 2),
            'services_count' => $appointments->count(),
            'expenses_count' => $expenses->count(),
            'collaborators' => $collaborators,
            'closed' => \App\Models\Closing::whereDate('closed_on', $day)->exists(),
        ];
    }

    // Extrato por colaborador: ganho total x pago x saldo a receber (histórico completo).
    public function ledger(): array
    {
        $rows = [];
        foreach (Collaborator::orderBy('name')->get() as $c) {
            $earned = (float) Appointment::concluded()
                ->with('services')
                ->where('collaborator_id', $c->id)
                ->get()->sum('commission_total');

            $paid = (float) Payment::where('collaborator_id', $c->id)->sum('amount');

            $rows[] = [
                'collaborator_id' => $c->id,
                'name' => $c->name,
                'pix' => $c->pix,
                'earned' => round($earned, 2),
                'paid' => round($paid, 2),
                'balance' => round($earned - $paid, 2),
            ];
        }
        return $rows;
    }
}
