<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Alinhamento com o mockup: o app ja usa o status 'cancelado', mas o enum
// original nao o tinha. Sem isto, cancelar um agendamento via API quebra.
return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE appointments MODIFY status
            ENUM('aguardando','andamento','concluido','cancelado')
            NOT NULL DEFAULT 'aguardando'");
    }

    public function down(): void
    {
        // Reverte para o enum original (cuidado: linhas 'cancelado' viram invalidas).
        DB::statement("ALTER TABLE appointments MODIFY status
            ENUM('aguardando','andamento','concluido')
            NOT NULL DEFAULT 'aguardando'");
    }
};
