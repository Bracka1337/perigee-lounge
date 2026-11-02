<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_served')->default(false)->after('total_price');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->boolean('is_available')->default(true)->after('price');
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->boolean('is_available')->default(true)->after('is_special');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('is_served');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('is_available');
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropColumn('is_available');
        });
    }
};
