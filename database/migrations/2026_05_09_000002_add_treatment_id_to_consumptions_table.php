<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumptions', function (Blueprint $table) {
            $table->foreignId('treatment_id')
                ->nullable()
                ->after('item_id')
                ->constrained('treatments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consumptions', function (Blueprint $table) {
            $table->dropForeign(['treatment_id']);
            $table->dropColumn('treatment_id');
        });
    }
};
