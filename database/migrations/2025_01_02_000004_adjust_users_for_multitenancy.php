<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Roda DEPOIS de add_tenant_id_to_tables (users ja tem tenant_id aqui).
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // username deixa de ser unico global e passa a ser unico POR tenant.
            $table->dropUnique(['username']);
            $table->unique(['tenant_id', 'username']);
        });

        // Adiciona o papel super_admin (dono da plataforma / landlord).
        DB::statement("ALTER TABLE users MODIFY role
            ENUM('admin','colaborador','super_admin') NOT NULL DEFAULT 'colaborador'");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'username']);
            $table->unique('username');
        });

        DB::statement("ALTER TABLE users MODIFY role
            ENUM('admin','colaborador') NOT NULL DEFAULT 'colaborador'");
    }
};
