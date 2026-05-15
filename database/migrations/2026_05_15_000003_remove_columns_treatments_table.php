<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('treatments', function ($table) {
            $table->dropColumn('frequency_value');
            $table->dropColumn('dose_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function ($table) {
            $table->integer('frequency_value')->after('status');
            $table->float('dose_quantity')->after('frequency_unit');
        });
    }
};