<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pagamentos aos colaboradores. manual=false quando quitado pelo fechamento de caixa do dia.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->default(0);
            $table->date('paid_on');
            $table->boolean('manual')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->index(['collaborator_id', 'paid_on']);
        });
    }

    public function down(): void { Schema::dropIfExists('payments'); }
};
