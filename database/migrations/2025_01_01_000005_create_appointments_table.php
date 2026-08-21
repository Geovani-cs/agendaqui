<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('owner_name');
            $table->string('phone');
            $table->string('plate')->nullable();
            $table->foreignId('vehicle_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('model')->nullable();
            $table->dateTime('scheduled_at');
            $table->enum('status', ['aguardando', 'andamento', 'concluido'])->default('aguardando');
            $table->foreignId('collaborator_id')->nullable()->constrained()->nullOnDelete(); // quem executou
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['scheduled_at', 'status']);
            $table->index('plate');
        });
    }

    public function down(): void { Schema::dropIfExists('appointments'); }
};
