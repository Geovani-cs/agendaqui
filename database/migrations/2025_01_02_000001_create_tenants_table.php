<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();   // vai na URL: agendaqui.com.br/jv
            $table->string('name');             // "JV Estetica Automotiva"

            // Maquina de estados da conta: active -> warning -> read_only -> blocked
            $table->string('status')->default('active');

            // Cobranca (mensalidade fixa)
            $table->decimal('monthly_fee', 10, 2)->default(149);
            $table->date('due_date')->nullable();
            $table->timestamp('status_changed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
