<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ganho (comissão) do colaborador em cada serviço.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('collaborator_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->decimal('commission', 10, 2)->default(0);
            $table->timestamps();
            $table->unique(['collaborator_id', 'service_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('collaborator_service'); }
};
