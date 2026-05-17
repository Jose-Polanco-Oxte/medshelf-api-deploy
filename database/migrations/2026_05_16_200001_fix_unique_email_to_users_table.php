<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('deleted_email')->nullable()->after('email');
        });

        // Un solo UPDATE en lugar de iterar fila por fila
        DB::table('users')
            ->whereNotNull('deleted_at')
            ->whereNotNull('email')
            ->update([
                'deleted_email' => DB::raw('email'),
                'email' => null,
            ]);

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
            $table->dropColumn('deleted_email');
        });
    }
};