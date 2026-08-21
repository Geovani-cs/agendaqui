<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Dono da plataforma (landlord) — nao pertence a nenhum tenant.
        User::firstOrCreate(
            ['username' => 'dono', 'tenant_id' => null],
            ['name' => 'Dono da Plataforma', 'password' => Hash::make('dono'), 'role' => 'super_admin']
        );

        // 2) Tenant de demonstracao.
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Ambiente de Demonstracao',
                'status' => 'active',
                'monthly_fee' => 149,
                'due_date' => now()->addMonth()->toDateString(),
            ]
        );

        // 3) Ativa o contexto do tenant: tudo criado abaixo recebe tenant_id sozinho.
        app()->instance('currentTenant', $tenant);

        // Admin padrao do tenant demo
        User::firstOrCreate(
            ['username' => 'admin'],
            ['name' => 'Administrador', 'password' => Hash::make('admin'), 'role' => 'admin']
        );

        // Tipos de automovel
        $types = collect(['Carro', 'Moto', 'Caminhonete'])->mapWithKeys(
            fn ($t) => [$t => VehicleType::firstOrCreate(['name' => $t])->id]
        );

        // Servicos de exemplo
        $samples = [
            ['001', 'Carro', 'Lavagem Simples', 40, 40],
            ['002', 'Carro', 'Lavagem Completa', 80, 90],
            ['003', 'Moto', 'Lavagem Moto', 25, 30],
        ];
        foreach ($samples as [$cod, $type, $name, $value, $time]) {
            Service::firstOrCreate(
                ['cod' => $cod],
                [
                    'vehicle_type_id' => $types[$type],
                    'name' => $name,
                    'value' => $value,
                    'execution_time' => $time,
                ]
            );
        }

        // Configuracao inicial (nome, PIX, mensagens) — agora por tenant
        Setting::current();

        // Limpa o contexto ao final do seed.
        app()->forgetInstance('currentTenant');
    }
}
