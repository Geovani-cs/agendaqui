<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Serviços de cada agendamento. Guarda o preço no momento do agendamento
// (snapshot) para que relatórios antigos não mudem se o preço do serviço mudar.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointment_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('execution_time')->default(0);
            $table->decimal('commission', 10, 2)->default(0); // comissão paga por este serviço (definida na conclusão)
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('appointment_service'); }
};
