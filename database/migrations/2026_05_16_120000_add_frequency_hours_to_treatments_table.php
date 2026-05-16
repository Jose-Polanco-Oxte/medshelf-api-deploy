<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->unsignedInteger('frequency_hours')->default(8)->after('dose');
            $table->dropColumn('frequency_unit');
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->string('frequency_unit')->after('dose');
            $table->dropColumn('frequency_hours');
        });
    }
};


