<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $q = Expense::with('user')->orderByDesc('spent_on');
        if ($request->filled('from')) {
            $q->whereDate('spent_on', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('spent_on', '<=', $request->to);
        }
        return $q->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description' => ['required', 'string'],
            'value' => ['required', 'numeric', 'min:0'],
            'spent_on' => ['required', 'date_format:Y-m-d'],
        ]);
        $data['user_id'] = $request->user()?->id;
        return response()->json(Expense::create($data), 201);
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->noContent();
    }
}
