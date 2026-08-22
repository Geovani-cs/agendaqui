<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Opcao B: guardar a imagem (data URL base64) direto no banco, em vez de arquivo no disco
// (o disco do Laravel Cloud e efemero por causa do scale-to-zero).
return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointment_photos', function (Blueprint $table) {
            $table->longText('data')->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_photos', function (Blueprint $table) {
            $table->dropColumn('data');
        });
    }
};
