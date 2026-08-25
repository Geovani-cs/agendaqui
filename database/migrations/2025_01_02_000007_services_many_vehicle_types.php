<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_vehicle_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_type_id')->constrained()->cascadeOnDelete();
            $table->unique(['service_id', 'vehicle_type_id']);
        });

        // Migra os vinculos existentes (1 tipo por servico) para a nova tabela pivo.
        DB::table('services')->whereNotNull('vehicle_type_id')->select('id', 'vehicle_type_id')
            ->get()->each(function ($s) {
                DB::table('service_vehicle_type')->insert([
                    'service_id' => $s->id,
                    'vehicle_type_id' => $s->vehicle_type_id,
                ]);
            });

        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['vehicle_type_id']);
            $table->dropColumn('vehicle_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('vehicle_type_id')->nullable()->after('cod')->constrained()->nullOnDelete();
        });
        Schema::dropIfExists('service_vehicle_type');
    }
};
