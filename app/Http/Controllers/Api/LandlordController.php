<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Painel do dono (landlord). Roda SEM identify.tenant, entao o global scope
// nao filtra e o dono enxerga todos os ambientes.
class LandlordController extends Controller
{
    public function tenants()
    {
        $startMonth = Carbon::now()->startOfMonth()->toDateString();

        $tenants = Tenant::orderBy('name')->get()->map(function ($t) use ($startMonth) {
            $revenue = (float) Appointment::where('tenant_id', $t->id)
                ->where('status', 'concluido')
                ->whereDate('scheduled_at', '>=', $startMonth)
                ->with('services')->get()->sum('value');

            return [
                'id' => $t->id,
                'slug' => $t->slug,
                'name' => $t->name,
                'status' => $t->status,
                'monthly_fee' => (float) $t->monthly_fee,
                'due_date' => optional($t->due_date)->toDateString(),
                'revenue_month' => round($revenue, 2),
            ];
        });

        return [
            'tenants' => $tenants,
            'summary' => [
                'active' => $tenants->where('status', '!=', 'blocked')->count(),
                'blocked' => $tenants->where('status', 'blocked')->count(),
                'my_revenue' => round($tenants->where('status', '!=', 'blocked')->sum('monthly_fee'), 2),
                'clients_revenue' => round($tenants->sum('revenue_month'), 2),
            ],
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'slug' => ['required', 'string', 'alpha_dash', 'unique:tenants,slug'],
            'monthly_fee' => ['nullable', 'numeric', 'min:0'],
            'admin_name' => ['required', 'string'],
            'admin_username' => ['required', 'string'],
            'admin_password' => ['required', 'string', 'min:4'],
        ]);

        $tenant = DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'slug' => $data['slug'],
                'name' => $data['name'],
                'status' => 'active',
                'monthly_fee' => $data['monthly_fee'] ?? 149,
                'due_date' => Carbon::now()->addMonth()->toDateString(),
            ]);

            User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['admin_name'],
                'username' => $data['admin_username'],
                'password' => $data['admin_password'], // cast 'hashed' faz o hash
                'role' => 'admin',
            ]);

            return $tenant;
        });

        return response()->json($tenant, 201);
    }

    public function updateStatus(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,warning,read_only,blocked'],
        ]);
        $tenant->update(['status' => $data['status'], 'status_changed_at' => now()]);
        return $tenant;
    }
}
