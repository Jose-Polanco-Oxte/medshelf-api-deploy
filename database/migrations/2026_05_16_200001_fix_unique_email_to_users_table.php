<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Agregar columna para guardar el email de usuarios eliminados
        Schema::table('users', function (Blueprint $table) {
            $table->string('deleted_email')->nullable()->after('email');
        });

        // 2. Quitar NOT NULL de email para poder setearlo en null
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // 3. Mover email → deleted_email en usuarios ya eliminados
        DB::table('users')
            ->whereNotNull('deleted_at')
            ->whereNotNull('email')
            ->update([
                'deleted_email' => DB::raw('email'),
                'email' => null,
            ]);

        // 4. Restaurar el unique en email (NULL no cuenta como duplicado)
        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        DB::table('users')
            ->whereNotNull('deleted_at')
            ->whereNotNull('deleted_email')
            ->update([
                'email' => DB::raw('deleted_email'),
                'deleted_email' => null,
            ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->dropColumn('deleted_email');
        });
    }
};