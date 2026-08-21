<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('value', 10, 2)->default(0);
            $table->date('spent_on');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // quem lançou
            $table->timestamps();
            $table->index('spent_on');
        });
    }

    public function down(): void { Schema::dropIfExists('expenses'); }
};
