<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Expense;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Relatório de serviços concluídos no período.
    public function services(Request $request)
    {
        [$from, $to] = $this->range($request);

        $appts = Appointment::concluded()
            ->with(['services', 'collaborator'])
            ->when($from, fn ($q) => $q->whereDate('scheduled_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('scheduled_at', '<=', $to))
            ->orderByDesc('scheduled_at')->get();

        return [
            'count' => $appts->count(),
            'revenue' => round((float) $appts->sum('value'), 2),
            'items' => $appts,
        ];
    }

    // Relatório financeiro: receita, despesas, comissões e lucro líquido.
    public function financial(Request $request)
    {
        [$from, $to] = $this->range($request);

        $appts = Appointment::concluded()->with('services')
            ->when($from, fn ($q) => $q->whereDate('scheduled_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('scheduled_at', '<=', $to))
            ->get();

        $revenue = (float) $appts->sum('value');
        $commissions = (float) $appts->sum('commission_total');

        $expenses = Expense::when($from, fn ($q) => $q->whereDate('spent_on', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('spent_on', '<=', $to))->get();
        $expensesTotal = (float) $expenses->sum('value');

        return [
            'revenue' => round($revenue, 2),
            'expenses' => round($expensesTotal, 2),
            'commissions' => round($commissions, 2),
            'profit' => round($revenue - $expensesTotal - $commissions, 2),
            'expenses_list' => $expenses,
        ];
    }

    private function range(Request $request): array
    {
        return [$request->query('from'), $request->query('to')];
    }
}
