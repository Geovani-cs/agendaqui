<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    // Tabelas "de cliente". Pivots (collaborator_service, appointment_service)
    // ficam de fora: ja sao escopados pelos pais.
    private array $tables = [
        'users', 'vehicle_types', 'services', 'collaborators',
        'appointments', 'appointment_photos', 'expenses',
        'payments', 'closings', 'settings',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // nullable no inicio: nao quebra linhas existentes; super-admin fica null.
                $table->foreignId('tenant_id')
                      ->nullable()
                      ->after('id')
                      ->constrained()
                      ->cascadeOnDelete();
                $table->index('tenant_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }
    }
};
