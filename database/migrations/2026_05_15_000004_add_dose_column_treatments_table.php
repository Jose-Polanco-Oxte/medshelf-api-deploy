<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('treatments', function ($table) {
            $table->float('dose')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function ($table) {
            $table->dropColumn('dose');
        });
    }
};