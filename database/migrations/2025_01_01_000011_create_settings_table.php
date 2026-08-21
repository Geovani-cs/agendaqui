<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Meu Lava-Rápido');
            $table->string('company_pix')->nullable();
            $table->text('message_scheduling')->nullable();
            $table->text('message_completion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('settings'); }
};
