<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->unsignedInteger('days')->after('frequency_hours')->nullable();
            $table->dropColumn('end_date');
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->date('end_date')->after('frequency_hours')->nullable();
            $table->dropColumn('days');
        });
    }
};


