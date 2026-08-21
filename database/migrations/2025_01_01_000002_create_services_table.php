<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('cod')->nullable();
            $table->foreignId('vehicle_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->decimal('value', 10, 2)->default(0);
            $table->unsignedInteger('execution_time')->default(0); // minutos
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('services'); }
};
