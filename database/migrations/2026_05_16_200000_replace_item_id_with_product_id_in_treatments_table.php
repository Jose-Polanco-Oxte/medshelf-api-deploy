<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
            $table->dropColumn('item_id');
            $table->foreignId('product_id')->after('profile_id')->constrained('products')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('treatments', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->foreignId('item_id')->after('profile_id')->constrained('items')->restrictOnDelete();
        });
    }
};
