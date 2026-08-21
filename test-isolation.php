<?php
// Teste de isolamento multi-tenant. Rode: php test-isolation.php
// Ele cria dados temporarios e desfaz tudo no final (DB::rollBack).

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Support\Facades\DB;

$fails = 0;
function check(string $label, bool $ok): void {
    global $fails;
    if (!$ok) { $fails++; }
    echo ($ok ? "  [PASS]    " : "  [FALHOU]  ") . $label . "\n";
}
function useTenant(?Tenant $t): void {
    if ($t) { app()->instance('currentTenant', $t); }
    else { app()->forgetInstance('currentTenant'); }
}

echo "== Estado inicial (do seed) ==\n";
echo "  Tenants no banco: " . Tenant::count() . "\n";
echo "  Super-admins (tenant_id null): " . User::whereNull('tenant_id')->count() . "\n\n";

echo "== Teste de isolamento ==\n";
DB::beginTransaction();
try {
    $demo = Tenant::where('slug', 'demo')->firstOrFail();
    $t2 = Tenant::create([
        'slug' => 'iso-test-2', 'name' => 'Isolation Test 2',
        'status' => 'active', 'monthly_fee' => 149,
    ]);

    // Cria sob o tenant demo
    useTenant($demo);
    $vDemo = VehicleType::create(['name' => 'ISO-DEMO']);
    check("cria sob demo preenche tenant_id sozinho", $vDemo->tenant_id === $demo->id);

    // Cria sob o tenant 2
    useTenant($t2);
    $vT2 = VehicleType::create(['name' => 'ISO-T2']);
    check("cria sob t2 preenche tenant_id sozinho", $vT2->tenant_id === $t2->id);

    // t2 nao enxerga nada do demo
    $namesT2 = VehicleType::pluck('name')->all();
    check("t2 ve o proprio dado (ISO-T2)", in_array('ISO-T2', $namesT2));
    check("t2 NAO ve o dado do demo (ISO-DEMO)", !in_array('ISO-DEMO', $namesT2));
    check("t2 NAO ve os dados semeados do demo (Carro)", !in_array('Carro', $namesT2));

    // demo nao enxerga nada do t2
    useTenant($demo);
    $namesDemo = VehicleType::pluck('name')->all();
    check("demo ve o proprio dado (ISO-DEMO)", in_array('ISO-DEMO', $namesDemo));
    check("demo NAO ve o dado do t2 (ISO-T2)", !in_array('ISO-T2', $namesDemo));

    // Super-admin (sem tenant vinculado) ve todos
    useTenant(null);
    $namesAll = VehicleType::pluck('name')->all();
    check("super-admin ve o dado do demo", in_array('ISO-DEMO', $namesAll));
    check("super-admin ve o dado do t2", in_array('ISO-T2', $namesAll));

    DB::rollBack();
    useTenant(null);
    echo "\n(dados de teste revertidos — nada ficou gravado no banco)\n";
} catch (Throwable $e) {
    DB::rollBack();
    echo "\nERRO durante o teste: " . $e->getMessage() . "\n";
    $fails++;
}

echo "\n========================================\n";
echo $fails === 0
    ? "RESULTADO: ISOLAMENTO OK - todos os testes passaram.\n"
    : "RESULTADO: $fails teste(s) falharam - revisar antes de subir.\n";
echo "========================================\n";
