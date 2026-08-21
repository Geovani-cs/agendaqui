<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('closings', function (Blueprint $table) {
            $table->id();
            $table->date('closed_on')->unique();
            $table->decimal('gross', 10, 2)->default(0);        // saldo bruto
            $table->decimal('expenses_total', 10, 2)->default(0);
            $table->decimal('collaborators_total', 10, 2)->default(0);
            $table->decimal('net', 10, 2)->default(0);          // saldo líquido
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('closings'); }
};
